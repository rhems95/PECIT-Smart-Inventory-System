<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Department;
use App\Models\Inventory;
use App\Models\InventoryPriceAdjustment;
use App\Models\Supplier;
use App\Models\UnitOfMeasurement;
use App\Models\User;
use App\Services\InventoryService;
use App\Services\AuditLogService;
use App\Services\StockCardService;
use App\Support\Qty;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;

class InventoryController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Inventory::class);

        $query = Inventory::with(['category', 'department', 'sizeStocks']);

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('item_name', 'like', "%{$search}%")
                    ->orWhere('item_code', 'like', "%{$search}%");
            });
        }

        if ($categoryId = $request->integer('category_id')) {
            $query->where('category_id', $categoryId);
        }

        if ($status = $request->string('status')->trim()->toString()) {
            $query->where('status', $status);
        }

        $items = $query->latest()->paginate(15)->withQueryString();

        return view('inventory.index', [
            'items' => $items,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Inventory::class);

        return view('inventory.create', [
            'inventory' => null,
            'categories' => Category::orderBy('name')->get(),
            'departments' => Department::active()->orderBy('name')->get(),
            'units' => UnitOfMeasurement::orderBy('name')->get(),
            'sizes' => config('psis.uniform_sizes', ['XS', 'S', 'M', 'L', 'XL', '2XL', '3XL']),
        ]);
    }

    public function store(Request $request, AuditLogService $auditLog, InventoryService $inventoryService): RedirectResponse
    {
        $this->authorize('create', Inventory::class);

        $data = $this->validatedItem($request);
        $size = $data['size'] ?? null;
        unset($data['size']);

        $probe = new Inventory($data + ['reserved_quantity' => 0]);
        if ($probe->requiresSize() && ($size === null || $size === '')) {
            return back()->withInput()->withErrors([
                'size' => 'Select a uniform size for this shop item.',
            ]);
        }

        $item = DB::transaction(function () use ($data, $size, $request, $auditLog, $inventoryService) {
            $item = Inventory::create($data + ['reserved_quantity' => 0]);

            if ($item->requiresSize()) {
                $item->sizeStocks()->create([
                    'size' => $size,
                    'quantity' => Qty::of($data['quantity'] ?? 0),
                    'reserved_quantity' => 0,
                ]);
                $item->syncAggregatesFromSizeStocks();
            }

            $item->updateStatus();

            $inventoryService->recordOpeningBalance(
                $item->fresh(['sizeStocks']),
                Qty::of($data['quantity'] ?? 0),
                $request->user(),
                $item->requiresSize() ? $size : null,
            );

            $auditLog->log($request->user(), 'inventory.created', $item, null, [
                'item_code' => $item->item_code,
                'item_name' => $item->item_name,
                'quantity' => $item->quantity,
                'size' => $size,
            ]);

            return $item;
        });

        return redirect()->route('inventory.index')->with('success', 'Inventory item created.');
    }

    public function show(Inventory $inventory): View
    {
        $this->authorize('view', $inventory);

        $inventory->load(['category', 'department', 'sizeStocks', 'unitOfMeasurement']);

        return view('inventory.show', compact('inventory'));
    }

    public function stockCard(Request $request, Inventory $inventory, StockCardService $stockCard): View
    {
        $this->authorize('viewStockCard', $inventory);

        $inventory->load(['category', 'unitOfMeasurement']);

        return view('inventory.stock-card', [
            'inventory' => $inventory,
            'entries' => $stockCard->paginate($inventory, $request),
            'types' => \App\Enums\InventoryTransactionType::stockCardFilters(),
            'suppliers' => Supplier::orderBy('name')->get(),
        ]);
    }

    public function edit(Inventory $inventory): View
    {
        $this->authorize('update', $inventory);

        return view('inventory.edit', [
            'inventory' => $inventory->load('sizeStocks'),
            'categories' => Category::orderBy('name')->get(),
            'departments' => Department::active()->orderBy('name')->get(),
            'units' => UnitOfMeasurement::orderBy('name')->get(),
            'sizes' => config('psis.uniform_sizes', ['XS', 'S', 'M', 'L', 'XL', '2XL', '3XL']),
        ]);
    }

    public function update(Request $request, Inventory $inventory, InventoryService $inventoryService, AuditLogService $auditLog): RedirectResponse
    {
        $this->authorize('update', $inventory);

        $data = $this->validatedItem($request, $inventory);
        $sizeQuantities = $data['size_quantities'] ?? [];
        unset($data['size_quantities']);

        $before = $inventory->only(['item_code', 'item_name', 'unit_price', 'minimum_stock', 'student_shop', 'department_id', 'status']);
        $oldPrice = (float) $inventory->unit_price;
        $inventory->update($data);
        $inventory->refresh();

        if (isset($data['unit_price']) && (float) $data['unit_price'] !== $oldPrice) {
            InventoryPriceAdjustment::create([
                'inventory_id' => $inventory->id,
                'old_unit_price' => $oldPrice,
                'new_unit_price' => $data['unit_price'],
                'reason' => $request->input('price_adjustment_reason') ?: 'Unit price updated on item edit.',
                'adjusted_by' => $request->user()->id,
                'adjusted_at' => now(),
            ]);
        }

        if ($inventory->requiresSize()) {
            try {
                $this->syncSizeQuantities($inventory, $sizeQuantities, $request->user(), $inventoryService);
            } catch (RuntimeException $e) {
                return back()->withInput()->with('error', $e->getMessage());
            }
        }

        if (! isset($data['status']) || $data['status'] !== 'discontinued') {
            $inventory->updateStatus();
        }

        $auditLog->log($request->user(), 'inventory.updated', $inventory, $before, [
            'item_code' => $inventory->item_code,
            'item_name' => $inventory->item_name,
        ]);

        return redirect()->route('inventory.show', $inventory)->with('success', 'Inventory item updated.');
    }

    public function destroy(Request $request, Inventory $inventory, AuditLogService $auditLog): RedirectResponse
    {
        $this->authorize('delete', $inventory);

        $reason = $inventory->deletionBlockReason();
        if ($reason !== null) {
            return back()->with('error', $reason);
        }

        $auditLog->log($request->user(), 'inventory.deleted', $inventory, [
            'item_code' => $inventory->item_code,
            'item_name' => $inventory->item_name,
            'quantity' => $inventory->quantity,
        ]);

        $inventory->delete();

        return redirect()->route('inventory.index')->with('success', 'Inventory item deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function validatedItem(Request $request, ?Inventory $inventory = null): array
    {
        $sizes = config('psis.uniform_sizes', ['XS', 'S', 'M', 'L', 'XL', '2XL', '3XL']);
        $needsSize = $request->boolean('student_shop')
            && ! str_contains(strtolower(($request->input('item_name') ?? '').' '.($request->input('item_code') ?? '')), 'lanyard');

        $rules = [
            'item_code' => ['required', 'string', 'max:50', 'unique:inventory,item_code'.($inventory ? ','.$inventory->id : '')],
            'item_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category_id' => ['required', 'exists:categories,id'],
            'unit_of_measurement_id' => ['required', 'exists:units_of_measurement,id'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'minimum_stock' => ['required', 'numeric', 'min:0'],
            'location' => ['nullable', 'string', 'max:255'],
            'student_shop' => ['sometimes', 'boolean'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
        ];

        if (! $inventory) {
            $rules['quantity'] = ['required', 'numeric', 'min:0'];
            $rules['size'] = [$needsSize ? 'required' : 'nullable', 'string', Rule::in($sizes)];
        } else {
            $rules['status'] = ['nullable', 'in:available,low_stock,out_of_stock,discontinued'];
            $rules['size_quantities'] = ['nullable', 'array'];
            $rules['size_quantities.*'] = ['integer', 'min:0'];
        }

        $data = $request->validate($rules);
        $data['student_shop'] = $request->boolean('student_shop');
        $uom = UnitOfMeasurement::find($data['unit_of_measurement_id']);
        if ($uom) {
            $data['unit'] = $uom->symbol === 'pcs' ? 'piece' : $uom->symbol;
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $sizeQuantities
     */
    protected function syncSizeQuantities(Inventory $inventory, array $sizeQuantities, User $user, InventoryService $inventoryService): void
    {
        $allowed = config('psis.uniform_sizes', ['XS', 'S', 'M', 'L', 'XL', '2XL', '3XL']);

        foreach ($allowed as $size) {
            if (! array_key_exists($size, $sizeQuantities)) {
                continue;
            }

            $qty = (int) $sizeQuantities[$size];
            $current = $inventory->sizeStockFor($size);

            if ($current && (int) $current->quantity === $qty) {
                continue;
            }

            if (! $current && $qty === 0) {
                continue;
            }

            $inventoryService->adjust(
                $inventory,
                $qty,
                $user,
                'Updated on-hand by size from inventory edit.',
                $size,
            );
            $inventory->unsetRelation('sizeStocks');
        }
    }
}
