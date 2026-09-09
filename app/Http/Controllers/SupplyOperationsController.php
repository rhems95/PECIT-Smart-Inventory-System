<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\PurchaseRequest;
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
        ]);
    }

    public function stockIn(Request $request, InventoryService $inventoryService): RedirectResponse
    {
        $sizes = config('psis.uniform_sizes', ['XS', 'S', 'M', 'L', 'XL', '2XL', '3XL']);

        $data = $request->validate([
            'inventory_id' => ['required', 'exists:inventory,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:500'],
            'size' => ['nullable', 'string', Rule::in($sizes)],
        ]);

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
            );
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Stock-in recorded.');
    }

    public function adjust(Request $request, InventoryService $inventoryService): RedirectResponse
    {
        $sizes = config('psis.uniform_sizes', ['XS', 'S', 'M', 'L', 'XL', '2XL', '3XL']);

        $data = $request->validate([
            'inventory_id' => ['required', 'exists:inventory,id'],
            'new_quantity' => ['required', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
            'size' => ['nullable', 'string', Rule::in($sizes)],
        ]);

        $inventory = Inventory::findOrFail($data['inventory_id']);

        try {
            $inventoryService->adjust(
                $inventory,
                $data['new_quantity'],
                auth()->user(),
                $data['notes'] ?? null,
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
}
