<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\RequestItem;
use App\Models\SupplyRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class SupplyRequestService
{
    public function __construct(
        protected InventoryService $inventoryService,
        protected NotificationService $notifications,
        protected AuditLogService $auditLog,
    ) {}

    public function create(User $user, array $items, ?string $purpose, ?int $departmentId = null): SupplyRequest
    {
        return DB::transaction(function () use ($user, $items, $purpose, $departmentId) {
            $request = SupplyRequest::create([
                'request_number' => 'REQ-'.strtoupper(Str::random(8)),
                'user_id' => $user->id,
                'department_id' => $departmentId ?? $user->department_id,
                'type' => 'faculty',
                'status' => 'pending',
                'purpose' => $purpose,
            ]);

            foreach ($items as $row) {
                $inventory = Inventory::findOrFail($row['inventory_id']);
                RequestItem::create([
                    'request_id' => $request->id,
                    'inventory_id' => $inventory->id,
                    'quantity_requested' => (int) $row['quantity'],
                    'unit_price' => $inventory->unit_price,
                    'subtotal' => $inventory->unit_price * (int) $row['quantity'],
                ]);
            }

            $request->load('items');
            $request->update(['total_amount' => $request->items->sum('subtotal')]);

            $this->notifications->notifyRole(
                'Accounting',
                'new_request',
                'New supply request',
                "{$user->name} submitted request {$request->request_number}.",
                route('accounting.requests.show', $request),
            );

            $this->auditLog->log($user, 'supply_request.created', $request, null, $request->toArray());

            return $request->fresh(['items.inventory']);
        });
    }

    public function cancel(SupplyRequest $request, User $user): SupplyRequest
    {
        if (! $request->isCancellable()) {
            throw new RuntimeException('This request can no longer be cancelled.');
        }

        if ($request->user_id !== $user->id && ! $user->hasRole('Administrator')) {
            throw new RuntimeException('Unauthorized.');
        }

        return DB::transaction(function () use ($request, $user) {
            $wasApproved = $request->status === 'approved';

            if ($wasApproved) {
                $request->loadMissing('items.inventory');

                foreach ($request->items as $item) {
                    $qty = (int) ($item->quantity_approved ?: $item->quantity_requested);
                    if ($qty <= 0) {
                        continue;
                    }

                    $inventory = Inventory::query()->findOrFail($item->inventory_id);

                    $this->inventoryService->restore(
                        $inventory,
                        $qty,
                        $user,
                        "Restored from cancelled {$request->request_number}",
                        SupplyRequest::class,
                        $request->id,
                    );
                }
            }

            $request->update(['status' => 'cancelled']);

            $this->auditLog->log($user, 'supply_request.cancelled', $request);

            if ($wasApproved) {
                $this->notifications->notifyRole(
                    'Supply Personnel',
                    'request_cancelled',
                    'Approved request cancelled',
                    "Request {$request->request_number} was cancelled. Reserved stock was restored.",
                    route('supply.releases'),
                );
            }

            return $request->fresh(['items.inventory']);
        });
    }

    public function accountingReview(SupplyRequest $request, User $reviewer, array $pricedItems): SupplyRequest
    {
        if ($request->status !== 'pending' && $request->status !== 'accounting_review') {
            throw new RuntimeException('Request is not awaiting accounting review.');
        }

        return DB::transaction(function () use ($request, $reviewer, $pricedItems) {
            $total = 0;

            foreach ($pricedItems as $row) {
                $item = RequestItem::where('request_id', $request->id)->findOrFail($row['id']);
                $qtyApproved = (int) ($row['quantity_approved'] ?? $item->quantity_requested);
                $unitPrice = (float) ($row['unit_price'] ?? $item->unit_price);
                $subtotal = $qtyApproved * $unitPrice;

                $item->update([
                    'quantity_approved' => $qtyApproved,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ]);

                $total += $subtotal;
            }

            $request->update([
                'status' => 'admin_review',
                'total_amount' => $total,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
            ]);

            $this->notifications->notifyRole(
                'Administrator',
                'request_reviewed',
                'Request ready for approval',
                "Request {$request->request_number} was reviewed by accounting.",
                route('admin.requests.show', $request),
            );
            $this->notifications->notifyRole(
                'Admission',
                'request_reviewed',
                'Request ready for approval',
                "Request {$request->request_number} was reviewed by accounting.",
                route('admin.requests.show', $request),
            );

            $this->auditLog->log($reviewer, 'supply_request.accounting_review', $request);

            return $request->fresh(['items.inventory', 'user']);
        });
    }

    public function approve(SupplyRequest $request, User $approver): SupplyRequest
    {
        if ($request->status !== 'admin_review') {
            throw new RuntimeException('Request is not awaiting admin approval.');
        }

        return DB::transaction(function () use ($request, $approver) {
            $request->loadMissing(['items.inventory', 'user']);

            foreach ($request->items as $item) {
                $qty = (int) ($item->quantity_approved ?: $item->quantity_requested);
                if ($qty <= 0) {
                    continue;
                }

                $inventory = Inventory::query()->findOrFail($item->inventory_id);

                $this->inventoryService->reserve(
                    $inventory,
                    $qty,
                    $approver,
                    "Reserved for {$request->request_number}",
                    SupplyRequest::class,
                    $request->id,
                );
            }

            $request->update([
                'status' => 'approved',
                'approved_by' => $approver->id,
                'approved_at' => now(),
            ]);

            $request->user && $this->notifications->notify(
                $request->user,
                'request_approved',
                'Request approved',
                "Your request {$request->request_number} has been approved.",
                route('requests.show', $request),
            );

            $this->notifications->notifyRole(
                'Supply Personnel',
                'request_approved',
                'Approved request pending release',
                "Request {$request->request_number} is ready for release.",
                route('supply.releases.show', $request),
            );

            $this->auditLog->log($approver, 'supply_request.approved', $request);

            return $request->fresh(['items.inventory', 'user']);
        });
    }

    public function reject(SupplyRequest $request, User $approver, string $reason): SupplyRequest
    {
        if (! in_array($request->status, ['admin_review', 'accounting_review', 'pending'], true)) {
            throw new RuntimeException('Request cannot be rejected in its current state.');
        }

        $request->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
            'approved_by' => $approver->id,
            'approved_at' => now(),
        ]);

        $request->user && $this->notifications->notify(
            $request->user,
            'request_rejected',
            'Request rejected',
            "Your request {$request->request_number} was rejected: {$reason}",
            route('requests.show', $request),
        );

        $this->auditLog->log($approver, 'supply_request.rejected', $request);

        return $request;
    }

    public function release(SupplyRequest $request, User $releaser): SupplyRequest
    {
        if ($request->status !== 'approved') {
            throw new RuntimeException('Only approved requests can be released.');
        }

        return DB::transaction(function () use ($request, $releaser) {
            $request->loadMissing(['items.inventory', 'user']);

            foreach ($request->items as $item) {
                $qty = (int) ($item->quantity_approved ?: $item->quantity_requested);
                if ($qty <= 0) {
                    continue;
                }

                $inventory = Inventory::query()->findOrFail($item->inventory_id);

                $this->inventoryService->release(
                    $inventory,
                    $qty,
                    $releaser,
                    "Released for {$request->request_number}",
                    SupplyRequest::class,
                    $request->id,
                    $request->user?->name,
                );

                $item->update(['quantity_released' => $qty]);
            }

            $request->update([
                'status' => 'released',
                'released_by' => $releaser->id,
                'released_at' => now(),
            ]);

            $request->user && $this->notifications->notify(
                $request->user,
                'item_released',
                'Items released',
                "Items for request {$request->request_number} have been released.",
                route('requests.show', $request),
            );

            $this->auditLog->log($releaser, 'supply_request.released', $request);

            return $request->fresh(['items.inventory', 'user']);
        });
    }
}
