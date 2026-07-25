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
use Illuminate\View\View;

class SupplyOperationsController extends Controller
{
    public function stockIndex(): View
    {
        return view('supply.stock', [
            'items' => Inventory::orderBy('item_name')->get(),
        ]);
    }

    public function stockIn(Request $request, InventoryService $inventoryService): RedirectResponse
    {
        $data = $request->validate([
            'inventory_id' => ['required', 'exists:inventory,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $inventory = Inventory::findOrFail($data['inventory_id']);
        $inventoryService->stockIn($inventory, $data['quantity'], auth()->user(), $data['notes'] ?? null);

        return back()->with('success', 'Stock-in recorded.');
    }

    public function adjust(Request $request, InventoryService $inventoryService): RedirectResponse
    {
        $data = $request->validate([
            'inventory_id' => ['required', 'exists:inventory,id'],
            'new_quantity' => ['required', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $inventory = Inventory::findOrFail($data['inventory_id']);
        $inventoryService->adjust($inventory, $data['new_quantity'], auth()->user(), $data['notes'] ?? null);

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
        $purchases = PurchaseRequest::with('user')
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
