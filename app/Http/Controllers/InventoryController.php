<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Department;
use App\Models\Inventory;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

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
            'categories' => Category::orderBy('name')->get(),
            'departments' => Department::orderBy('name')->get(),
            'sizes' => config('psis.uniform_sizes', ['XS', 'S', 'M', 'L', 'XL', '2XL', '3XL']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Inventory::class);

        $data = $this->validatedItem($request);
        $size = $data['size'] ?? null;
        unset($data['size']);

        $item = Inventory::create($data + ['reserved_quantity' => 0]);

        if ($item->requiresSize()) {
            $qty = (int) ($data['quantity'] ?? 0);
            $item->sizeStocks()->create([
                'size' => $size,
                'quantity' => $qty,
                'reserved_quantity' => 0,
            ]);
            $item->syncAggregatesFromSizeStocks();
        }

        $item->updateStatus();

        return redirect()->route('inventory.index')->with('success', 'Inventory item created.');
    }

    public function show(Inventory $inventory): View
    {
        $this->authorize('view', $inventory);

        $inventory->load(['category', 'department', 'sizeStocks', 'transactions' => fn ($q) => $q->latest()->limit(10)]);

        $qrSvg = QrCode::size(120)->generate($inventory->item_code);

        return view('inventory.show', compact('inventory', 'qrSvg'));
    }

    public function edit(Inventory $inventory): View
    {
        $this->authorize('update', $inventory);

        return view('inventory.edit', [
            'inventory' => $inventory->load('sizeStocks'),
            'categories' => Category::orderBy('name')->get(),
            'departments' => Department::orderBy('name')->get(),
            'sizes' => config('psis.uniform_sizes', ['XS', 'S', 'M', 'L', 'XL', '2XL', '3XL']),
        ]);
    }

    public function update(Request $request, Inventory $inventory, InventoryService $inventoryService): RedirectResponse
    {
        $this->authorize('update', $inventory);

        $data = $this->validatedItem($request, $inventory);
        $sizeQuantities = $data['size_quantities'] ?? [];
        unset($data['size_quantities']);

        $inventory->update($data);
        $inventory->refresh();

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

        return redirect()->route('inventory.show', $inventory)->with('success', 'Inventory item updated.');
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
            'unit' => ['required', 'string', 'max:50'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'minimum_stock' => ['required', 'integer', 'min:0'],
            'location' => ['nullable', 'string', 'max:255'],
            'student_shop' => ['sometimes', 'boolean'],
            'department_id' => ['nullable', 'exists:departments,id'],
        ];

        if (! $inventory) {
            $rules['quantity'] = ['required', 'integer', 'min:0'];
            $rules['size'] = [$needsSize ? 'required' : 'nullable', 'string', Rule::in($sizes)];
        } else {
            $rules['status'] = ['nullable', 'in:available,low_stock,out_of_stock,discontinued'];
            $rules['size_quantities'] = ['nullable', 'array'];
            $rules['size_quantities.*'] = ['integer', 'min:0'];
        }

        $data = $request->validate($rules);
        $data['student_shop'] = $request->boolean('student_shop');

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
