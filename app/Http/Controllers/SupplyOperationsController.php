<?php

namespace App\Http\Controllers;

use App\Enums\StockSourceType;
use App\Models\Inventory;
use App\Models\PurchaseRequest;
use App\Models\Supplier;
use App\Models\SupplyRequest;
use App\Services\InventoryService;
use App\Services\PurchaseRequestService;
use App\Services\SupplyRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SupplyOperationsController extends Controller
{
    public function stockIndex(): View
    {
        return view('supply.stock', [
            'items' => Inventory::with('sizeStocks')->orderBy('item_name')->get(),
            'suppliers' => Supplier::query()->where('is_active', true)->orderBy('name')->get(),
            'sources' => StockSourceType::stockInSources(),
        ]);
    }

    public function stockIn(Request $request, InventoryService $inventoryService): RedirectResponse
    {
        $sizes = config('psis.uniform_sizes', ['XS', 'S', 'M', 'L', 'XL', '2XL', '3XL']);
        $sourceValues = array_map(fn (StockSourceType $s) => $s->value, StockSourceType::stockInSources());

        $data = $request->validate([
            'inventory_id' => ['required', 'exists:inventory,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:500'],
            'size' => ['nullable', 'string', Rule::in($sizes)],
            'source_type' => ['required', Rule::in($sourceValues)],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'delivery_receipt_number' => ['nullable', 'string', 'max:100'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
        ]);

        $source = StockSourceType::from($data['source_type']);
        if ($source->requiresSupplier() && empty($data['supplier_id'])) {
            return back()->withInput()->withErrors(['supplier_id' => 'Supplier is required for this source.']);
        }
        if ($source->requiresReference() && empty($data['reference_number'])) {
            return back()->withInput()->withErrors(['reference_number' => 'Reference / PO number is required for this source.']);
        }

        $inventory = Inventory::findOrFail($data['inventory_id']);

        try {
            $inventoryService->stockIn(
                $inventory,
                $data['quantity'],
                auth()->user(),
                $data['notes'] ?? null,
                null,
                null,
                $data['size'] ?? null,
                [
                    'source_type' => $source->value,
                    'supplier_id' => $data['supplier_id'] ?? null,
                    'reference_number' => $data['reference_number'] ?? null,
                    'delivery_receipt_number' => $data['delivery_receipt_number'] ?? null,
                    'unit_cost' => $data['unit_cost'] ?? null,
                ],
            );
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Stock-in recorded.');
    }

    public function stockOut(Request $request, InventoryService $inventoryService): RedirectResponse
    {
        return $this->runDeduct($request, $inventoryService, 'stockOut', 'Stock-out recorded.');
    }

    public function damage(Request $request, InventoryService $inventoryService): RedirectResponse
    {
        $data = $this->validatedDeduct($request, supplierRequired: false);
        $inventory = Inventory::findOrFail($data['inventory_id']);

        try {
            $inventoryService->recordDamage(
                $inventory,
                $data['quantity'],
                auth()->user(),
                $data['notes'],
                $data['size'] ?? null,
                [
                    'reference_number' => $data['reference_number'] ?? null,
                    'supplier_id' => $data['supplier_id'] ?? null,
                ],
            );
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Damage recorded. On-hand stock was deducted.');
    }

    public function badOrder(Request $request, InventoryService $inventoryService): RedirectResponse
    {
        $data = $this->validatedDeduct($request, supplierRequired: false);
        $inventory = Inventory::findOrFail($data['inventory_id']);

        try {
            $inventoryService->recordBadOrder(
                $inventory,
                $data['quantity'],
                auth()->user(),
                $data['notes'],
                $data['size'] ?? null,
                [
                    'reference_number' => $data['reference_number'] ?? null,
                    'delivery_receipt_number' => $data['delivery_receipt_number'] ?? null,
                    'supplier_id' => $data['supplier_id'] ?? null,
                ],
            );
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Bad order recorded. On-hand stock was deducted.');
    }

    public function returnToSupplier(Request $request, InventoryService $inventoryService): RedirectResponse
    {
        $data = $this->validatedDeduct($request, supplierRequired: true);
        $inventory = Inventory::findOrFail($data['inventory_id']);

        try {
            $inventoryService->returnToSupplier(
                $inventory,
                $data['quantity'],
                auth()->user(),
                $data['notes'],
                (int) $data['supplier_id'],
                $data['size'] ?? null,
                [
                    'reference_number' => $data['reference_number'] ?? null,
                    'delivery_receipt_number' => $data['delivery_receipt_number'] ?? null,
                ],
            );
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Return to supplier recorded. Returned quantity was deducted.');
    }

    public function adjust(Request $request, InventoryService $inventoryService): RedirectResponse
    {
        $sizes = config('psis.uniform_sizes', ['XS', 'S', 'M', 'L', 'XL', '2XL', '3XL']);

        $data = $request->validate([
            'inventory_id' => ['required', 'exists:inventory,id'],
            'new_quantity' => ['required', 'integer', 'min:0'],
            'notes' => ['required', 'string', 'max:500'],
            'size' => ['nullable', 'string', Rule::in($sizes)],
        ]);

        $inventory = Inventory::findOrFail($data['inventory_id']);

        try {
            $inventoryService->adjust(
                $inventory,
                $data['new_quantity'],
                auth()->user(),
                $data['notes'],
                $data['size'] ?? null,
            );
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Inventory adjusted.');
    }

    public function releases(): View
    {
        $requests = SupplyRequest::with(['user.department', 'department'])
            ->where('status', 'approved')
            ->latest()
            ->paginate(15);

        return view('supply.releases-index', compact('requests'));
    }

    public function showRelease(SupplyRequest $request): View
    {
        $request->load(['items.inventory', 'user.department', 'department']);

        return view('supply.releases-show', ['supplyRequest' => $request]);
    }

    public function releaseRequest(SupplyRequest $request, SupplyRequestService $service): RedirectResponse
    {
        try {
            $request->load(['items.inventory', 'user']);
            $service->release($request, auth()->user());
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('supply.releases')->with('success', 'Items released. On-hand inventory has been deducted.');
    }

    public function purchases(): View
    {
        $purchases = PurchaseRequest::with(['user.department'])
            ->where('status', 'payment_verified')
            ->latest()
            ->paginate(15);

        return view('supply.purchases-index', compact('purchases'));
    }

    public function showPurchase(PurchaseRequest $purchase): View
    {
        $purchase->load(['items.inventory', 'user', 'payments']);

        return view('supply.purchases-show', compact('purchase'));
    }

    public function releasePurchase(PurchaseRequest $purchase, PurchaseRequestService $service): RedirectResponse
    {
        try {
            $purchase->load(['items.inventory', 'user', 'payments']);
            $service->release($purchase, auth()->user());
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('supply.purchases')->with('success', 'Purchase released. On-hand inventory has been deducted.');
    }

    protected function runDeduct(Request $request, InventoryService $inventoryService, string $method, string $success): RedirectResponse
    {
        $data = $this->validatedDeduct($request, supplierRequired: false);
        $inventory = Inventory::findOrFail($data['inventory_id']);

        try {
            $inventoryService->{$method}(
                $inventory,
                $data['quantity'],
                auth()->user(),
                $data['notes'],
                null,
                null,
                null,
                $data['size'] ?? null,
                [
                    'source_type' => StockSourceType::Other->value,
                    'reference_number' => $data['reference_number'] ?? null,
                    'supplier_id' => $data['supplier_id'] ?? null,
                ],
            );
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', $success);
    }

    /**
     * @return array<string, mixed>
     */
    protected function validatedDeduct(Request $request, bool $supplierRequired): array
    {
        $sizes = config('psis.uniform_sizes', ['XS', 'S', 'M', 'L', 'XL', '2XL', '3XL']);

        return $request->validate([
            'inventory_id' => ['required', 'exists:inventory,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'notes' => ['required', 'string', 'max:500'],
            'size' => ['nullable', 'string', Rule::in($sizes)],
            'supplier_id' => [$supplierRequired ? 'required' : 'nullable', 'exists:suppliers,id'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'delivery_receipt_number' => ['nullable', 'string', 'max:100'],
        ]);
    }
}
