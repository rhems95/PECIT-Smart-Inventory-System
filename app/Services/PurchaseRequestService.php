<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\Payment;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class PurchaseRequestService
{
    public function __construct(
        protected InventoryService $inventoryService,
        protected NotificationService $notifications,
        protected AuditLogService $auditLog,
    ) {}

    public function checkout(User $user, array $cart): PurchaseRequest
    {
        if (empty($cart)) {
            throw new RuntimeException('Your cart is empty.');
        }

        return DB::transaction(function () use ($user, $cart) {
            $purchase = PurchaseRequest::create([
                'purchase_number' => 'PUR-'.strtoupper(Str::random(8)),
                'user_id' => $user->id,
                'status' => 'pending',
            ]);

            $total = 0;

            foreach ($cart as $line) {
                if (! is_array($line) || ! isset($line['inventory_id'], $line['quantity'])) {
                    throw new RuntimeException('Your cart is outdated. Please re-add items and choose a size.');
                }

                $inventoryId = (int) $line['inventory_id'];
                $qty = (int) $line['quantity'];
                $size = $line['size'] ?? null;

                if ($inventoryId <= 0 || $qty <= 0) {
                    throw new RuntimeException('Your cart contains an invalid item.');
                }

                $inventory = Inventory::findOrFail($inventoryId);

                if (! $inventory->isAvailableInStudentShop($user)) {
                    $label = $inventory->department
                        ? "exclusive to {$inventory->department->name}"
                        : 'not available in the Uniform Shop';
                    throw new RuntimeException("{$inventory->item_name} is {$label}.");
                }

                if ($inventory->requiresSize()) {
                    $allowed = config('psis.uniform_sizes', []);
                    if (! is_string($size) || $size === '' || ! in_array($size, $allowed, true)) {
                        throw new RuntimeException("Please choose a size for {$inventory->item_name} before checkout.");
                    }
                } else {
                    $size = null;
                }

                if ($inventory->availableQuantity($size) < $qty) {
                    $label = $size ? "{$inventory->item_name} (size {$size})" : $inventory->item_name;
                    throw new RuntimeException("Insufficient stock for {$label}.");
                }

                $subtotal = $inventory->unit_price * $qty;
                PurchaseRequestItem::create([
                    'purchase_request_id' => $purchase->id,
                    'inventory_id' => $inventory->id,
                    'size' => $size,
                    'quantity' => $qty,
                    'unit_price' => $inventory->unit_price,
                    'subtotal' => $subtotal,
                ]);

                $total += $subtotal;
            }

            $purchase->update(['total_amount' => $total]);

            Payment::create([
                'reference_number' => 'PAY-'.strtoupper(Str::random(10)),
                'purchase_request_id' => $purchase->id,
                'user_id' => $user->id,
                'amount' => $total,
                'status' => 'pending',
                'payment_method' => 'over_the_counter',
            ]);

            $purchase->update(['status' => 'payment_submitted']);

            $this->notifications->notifyRole(
                'Accounting',
                'payment_submitted',
                'New student purchase',
                "{$user->name} submitted purchase {$purchase->purchase_number}.",
                route('accounting.payments.show', $purchase),
            );

            $this->auditLog->log($user, 'purchase.created', $purchase);

            return $purchase->fresh(['items.inventory', 'payments']);
        });
    }

    public function verifyPayment(PurchaseRequest $purchase, User $verifier): PurchaseRequest
    {
        if ($purchase->status !== 'payment_submitted') {
            throw new RuntimeException('Purchase is not awaiting payment verification.');
        }

        return DB::transaction(function () use ($purchase, $verifier) {
            $purchase->loadMissing(['items.inventory', 'user', 'payments']);

            foreach ($purchase->items as $item) {
                $inventory = Inventory::query()->findOrFail($item->inventory_id);

                $this->inventoryService->reserve(
                    $inventory,
                    (int) $item->quantity,
                    $verifier,
                    "Reserved for {$purchase->purchase_number}",
                    PurchaseRequest::class,
                    $purchase->id,
                    $item->size,
                );
            }

            $payment = $purchase->payments()->latest()->first();
            $payment?->update([
                'status' => 'verified',
                'verified_by' => $verifier->id,
                'verified_at' => now(),
            ]);

            $purchase->update([
                'status' => 'payment_verified',
                'verified_by' => $verifier->id,
                'verified_at' => now(),
            ]);

            $purchase->user && $this->notifications->notify(
                $purchase->user,
                'payment_verified',
                'Payment verified',
                "Payment for {$purchase->purchase_number} has been verified.",
                route('purchases.show', $purchase),
            );

            $this->notifications->notifyRole(
                'Supply Personnel',
                'purchase_verified',
                'Purchase ready for release',
                "Purchase {$purchase->purchase_number} is ready for release.",
                route('supply.purchases.show', $purchase),
            );

            $this->auditLog->log($verifier, 'purchase.payment_verified', $purchase);

            return $purchase->fresh(['items.inventory', 'user']);
        });
    }

    public function cancel(PurchaseRequest $purchase, User $user): PurchaseRequest
    {
        if (! $purchase->isCancellable()) {
            throw new RuntimeException('This purchase can no longer be cancelled.');
        }

        if ($purchase->user_id !== $user->id && ! $user->hasAnyRole(['Administrator', 'Accounting'])) {
            throw new RuntimeException('Unauthorized.');
        }

        return DB::transaction(function () use ($purchase, $user) {
            $wasVerified = $purchase->status === 'payment_verified';

            if ($wasVerified) {
                $purchase->loadMissing('items.inventory');

                foreach ($purchase->items as $item) {
                    $qty = (int) $item->quantity;
                    if ($qty <= 0) {
                        continue;
                    }

                    $inventory = Inventory::query()->findOrFail($item->inventory_id);

                    $this->inventoryService->restore(
                        $inventory,
                        $qty,
                        $user,
                        "Restored from cancelled {$purchase->purchase_number}",
                        PurchaseRequest::class,
                        $purchase->id,
                        $item->size,
                    );
                }
            }

            $purchase->update(['status' => 'cancelled']);

            $this->auditLog->log($user, 'purchase.cancelled', $purchase);

            if ($wasVerified) {
                $this->notifications->notifyRole(
                    'Supply Personnel',
                    'purchase_cancelled',
                    'Verified purchase cancelled',
                    "Purchase {$purchase->purchase_number} was cancelled. Reserved stock was restored.",
                    route('supply.purchases'),
                );
            }

            return $purchase->fresh(['items.inventory']);
        });
    }

    public function release(PurchaseRequest $purchase, User $releaser): PurchaseRequest
    {
        if ($purchase->status !== 'payment_verified') {
            throw new RuntimeException('Purchase must be payment verified before release.');
        }

        return DB::transaction(function () use ($purchase, $releaser) {
            $purchase->loadMissing(['items.inventory', 'user']);

            $deducted = [];

            foreach ($purchase->items as $item) {
                $inventory = Inventory::query()->findOrFail($item->inventory_id);

                $this->inventoryService->release(
                    $inventory,
                    (int) $item->quantity,
                    $releaser,
                    "Student purchase {$purchase->purchase_number}",
                    PurchaseRequest::class,
                    $purchase->id,
                    $purchase->user?->name,
                    $item->size,
                );

                $deducted[] = $item->size
                    ? "{$inventory->item_name} ({$item->size}) x{$item->quantity}"
                    : "{$inventory->item_name} x{$item->quantity}";
            }

            $purchase->update([
                'status' => 'released',
                'released_by' => $releaser->id,
                'released_at' => now(),
            ]);

            $purchase->user && $this->notifications->notify(
                $purchase->user,
                'item_released',
                'Purchase released',
                "Your purchase {$purchase->purchase_number} has been released.",
                route('purchases.show', $purchase),
            );

            $this->auditLog->log($releaser, 'purchase.released', $purchase, null, [
                'deducted' => $deducted,
            ]);

            return $purchase->fresh(['items.inventory', 'user']);
        });
    }
}
