<?php

namespace App\Http\Controllers;

use App\Enums\ReceivingInspectionStatus;
use App\Enums\StockSourceType;
use App\Models\Inventory;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\RequestItem;
use App\Models\Supplier;
use App\Models\SupplyRequest;
use App\Models\Transaction;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\InventoryService;
use App\Services\PurchaseRequestService;
use App\Services\SupplyRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SupplyOperationsController extends Controller
{
    public function stockIndex(): View
    {
        return view('supply.stock', [
            'items' => Inventory::with('sizeStocks')->orderBy('item_name')->get(),
            'suppliers' => Supplier::query()->where('is_active', true)->orderBy('name')->get(),
            'buyers' => User::query()
                ->with('department')
                ->where('is_active', true)
                ->role(['Administrator', 'Admission', 'Accounting', 'Supply Personnel', 'Faculty'])
                ->orderBy('name')
                ->get(),
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
            'purchased_by' => ['nullable', 'exists:users,id'],
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
                    'purchased_by' => $data['purchased_by'] ?? auth()->id(),
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

    public function purchaseHistory(Request $request): View
    {
        $kind = $request->query('kind', 'all');
        if (! in_array($kind, ['all', 'shop', 'department', 'supplier'], true)) {
            $kind = 'all';
        }

        $search = trim((string) $request->query('q', ''));
        $inspection = $request->query('inspection');

        $shopRows = null;
        $departmentRows = null;
        $supplierRows = null;

        if ($kind === 'all' || $kind === 'shop') {
            $shopQuery = PurchaseRequestItem::query()
                ->with(['inventory', 'inspector', 'purchaseRequest.user.department'])
                ->whereHas('purchaseRequest', function ($query) {
                    $query->whereNotIn('status', ['cancelled', 'rejected']);
                })
                ->latest('id');

            if ($search !== '') {
                $shopQuery->where(function ($inner) use ($search) {
                    $inner->whereHas('inventory', fn ($inv) => $inv->where('item_name', 'like', "%{$search}%")->orWhere('item_code', 'like', "%{$search}%"))
                        ->orWhereHas('purchaseRequest', function ($purchase) use ($search) {
                            $purchase->where('purchase_number', 'like', "%{$search}%")
                                ->orWhereHas('user', fn ($user) => $user->where('name', 'like', "%{$search}%")->orWhere('employee_id', 'like', "%{$search}%"));
                        });
                });
            }

            $this->applyInspectionFilter($shopQuery, $inspection);

            $shopRows = $shopQuery->paginate(20, ['*'], 'shop_page')->withQueryString();
        }

        if ($kind === 'all' || $kind === 'department') {
            $deptQuery = RequestItem::query()
                ->with(['inventory', 'inspector', 'supplyRequest.user', 'supplyRequest.department', 'supplyRequest.releaser'])
                ->where('quantity_released', '>', 0)
                ->whereHas('supplyRequest', fn ($query) => $query->where('status', 'released'))
                ->latest('id');

            if ($search !== '') {
                $deptQuery->where(function ($inner) use ($search) {
                    $inner->whereHas('inventory', fn ($inv) => $inv->where('item_name', 'like', "%{$search}%")->orWhere('item_code', 'like', "%{$search}%"))
                        ->orWhereHas('supplyRequest', function ($req) use ($search) {
                            $req->where('request_number', 'like', "%{$search}%")
                                ->orWhereHas('user', fn ($user) => $user->where('name', 'like', "%{$search}%"))
                                ->orWhereHas('department', fn ($dept) => $dept->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"));
                        });
                });
            }

            $this->applyInspectionFilter($deptQuery, $inspection);

            $departmentRows = $deptQuery->paginate(20, ['*'], 'department_page')->withQueryString();
        }

        if ($kind === 'all' || $kind === 'supplier') {
            $query = Transaction::query()
                ->purchaseHistory()
                ->with(['inventory', 'supplier', 'performer.department', 'inspector', 'buyer.department'])
                ->latest('transaction_date')
                ->latest('id');

            if ($search !== '') {
                $query->where(function ($inner) use ($search) {
                    $inner->where('transaction_number', 'like', "%{$search}%")
                        ->orWhere('reference_number', 'like', "%{$search}%")
                        ->orWhere('delivery_receipt_number', 'like', "%{$search}%")
                        ->orWhereHas('inventory', fn ($inv) => $inv->where('item_name', 'like', "%{$search}%")->orWhere('item_code', 'like', "%{$search}%"))
                        ->orWhereHas('buyer', fn ($buyer) => $buyer->where('name', 'like', "%{$search}%"));
                });
            }

            $this->applyInspectionFilter($query, $inspection);

            $supplierRows = $query->paginate(20, ['*'], 'supplier_page')->withQueryString();
        }

        return view('supply.purchase-history', [
            'kind' => $kind,
            'shopRows' => $shopRows,
            'departmentRows' => $departmentRows,
            'supplierRows' => $supplierRows,
        ]);
    }

    public function inspectDepartmentItem(Request $request, RequestItem $item, AuditLogService $auditLog): RedirectResponse
    {
        if ((int) $item->quantity_released <= 0) {
            abort(404);
        }

        $status = $this->validatedInspection($request);

        $before = [
            'inspection_status' => $item->inspection_status instanceof ReceivingInspectionStatus
                ? $item->inspection_status->value
                : $item->inspection_status,
            'inspection_notes' => $item->inspection_notes,
        ];
        $item->update([
            'inspection_status' => $status->value,
            'inspection_notes' => $request->input('inspection_notes'),
            'inspected_by' => auth()->id(),
            'inspected_at' => now(),
        ]);

        $item->loadMissing(['inventory', 'supplyRequest']);

        $auditLog->log(auth()->user(), 'supply_request.receiving_inspected', $item->supplyRequest, $before, [
            'request' => $item->supplyRequest?->request_number,
            'item' => $item->inventory?->item_name,
            'status' => $status->value,
            'notes' => $request->input('inspection_notes'),
        ]);

        return back()->with('success', 'Item check saved for '.$item->supplyRequest?->request_number.'.');
    }

    public function inspectShopItem(Request $request, PurchaseRequestItem $item, AuditLogService $auditLog): RedirectResponse
    {
        $status = $this->validatedInspection($request);

        $before = [
            'inspection_status' => $item->inspection_status instanceof ReceivingInspectionStatus
                ? $item->inspection_status->value
                : $item->inspection_status,
            'inspection_notes' => $item->inspection_notes,
        ];
        $item->update([
            'inspection_status' => $status->value,
            'inspection_notes' => $request->input('inspection_notes'),
            'inspected_by' => auth()->id(),
            'inspected_at' => now(),
        ]);

        $item->loadMissing(['inventory', 'purchaseRequest']);

        $auditLog->log(auth()->user(), 'shop.receiving_inspected', $item->purchaseRequest, $before, [
            'purchase' => $item->purchaseRequest?->purchase_number,
            'item' => $item->inventory?->item_name,
            'status' => $status->value,
            'notes' => $request->input('inspection_notes'),
        ]);

        return back()->with('success', 'Item check saved for '.$item->purchaseRequest?->purchase_number.'.');
    }

    public function inspectPurchase(Request $request, Transaction $transaction, AuditLogService $auditLog): RedirectResponse
    {
        if (! $transaction->isPurchaseHistoryRow()) {
            abort(404);
        }

        $data = $this->validatedInspection($request);

        $transaction->loadMissing('inventory');

        $before = [
            'inspection_status' => $transaction->inspection_status instanceof ReceivingInspectionStatus
                ? $transaction->inspection_status->value
                : $transaction->inspection_status,
            'inspection_notes' => $transaction->inspection_notes,
        ];
        $transaction->update([
            'inspection_status' => $data->value,
            'inspection_notes' => $request->input('inspection_notes'),
            'inspected_by' => auth()->id(),
            'inspected_at' => now(),
        ]);

        $auditLog->log(auth()->user(), 'inventory.receiving_inspected', $transaction, $before, [
            'transaction' => $transaction->transaction_number,
            'item' => $transaction->inventory?->item_name,
            'status' => $data->value,
            'notes' => $request->input('inspection_notes'),
        ]);

        return back()->with('success', 'Receiving check saved for '.$transaction->transaction_number.'.');
    }

    protected function applyInspectionFilter(mixed $query, mixed $status): void
    {
        if ($status === 'pending') {
            $query->where(function ($inner) {
                $inner->where('inspection_status', ReceivingInspectionStatus::Pending->value)
                    ->orWhereNull('inspection_status');
            });
        } elseif (in_array($status, ['correct', 'incorrect'], true)) {
            $query->where('inspection_status', $status);
        }
    }

    protected function validatedInspection(Request $request): ReceivingInspectionStatus
    {
        $data = $request->validate([
            'inspection_status' => ['required', Rule::enum(ReceivingInspectionStatus::class)],
            'inspection_notes' => ['nullable', 'string', 'max:500'],
        ]);

        $status = ReceivingInspectionStatus::from($data['inspection_status']);
        if ($status === ReceivingInspectionStatus::Incorrect && blank($data['inspection_notes'] ?? null)) {
            throw ValidationException::withMessages([
                'inspection_notes' => 'Describe the wrong item that was given so Supply can follow up.',
            ]);
        }

        return $status;
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
