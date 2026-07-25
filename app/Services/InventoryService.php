<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\StockLog;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class InventoryService
{
    public function stockIn(
        Inventory $inventory,
        int $quantity,
        User $performedBy,
        ?string $notes = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
    ): Transaction {
        if ($quantity <= 0) {
            throw new RuntimeException('Stock-in quantity must be greater than zero.');
        }

        return DB::transaction(function () use ($inventory, $quantity, $performedBy, $notes, $referenceType, $referenceId) {
            $inventory->refresh();
            $before = $inventory->quantity;
            $inventory->quantity += $quantity;
            $inventory->save();
            $inventory->updateStatus();

            $transaction = $this->logTransaction(
                $inventory,
                'stock_in',
                $quantity,
                $before,
                $inventory->quantity,
                $performedBy,
                $notes,
                $referenceType,
                $referenceId,
            );

            StockLog::create([
                'inventory_id' => $inventory->id,
                'action' => 'stock_in',
                'quantity' => $quantity,
                'balance_after' => $inventory->quantity,
                'notes' => $notes,
                'performed_by' => $performedBy->id,
            ]);

            return $transaction;
        });
    }

    public function stockOut(
        Inventory $inventory,
        int $quantity,
        User $performedBy,
        ?string $notes = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $deliveryRecipient = null,
    ): Transaction {
        if ($quantity <= 0) {
            throw new RuntimeException('Stock-out quantity must be greater than zero.');
        }

        return DB::transaction(function () use ($inventory, $quantity, $performedBy, $notes, $referenceType, $referenceId, $deliveryRecipient) {
            $inventory->refresh();

            if ($inventory->availableQuantity() < $quantity) {
                throw new RuntimeException('Insufficient available stock.');
            }

            $before = $inventory->quantity;
            $inventory->quantity -= $quantity;
            $inventory->save();
            $inventory->updateStatus();

            $transaction = $this->logTransaction(
                $inventory,
                'stock_out',
                $quantity,
                $before,
                $inventory->quantity,
                $performedBy,
                $notes,
                $referenceType,
                $referenceId,
            );

            StockLog::create([
                'inventory_id' => $inventory->id,
                'action' => 'delivery',
                'quantity' => $quantity,
                'balance_after' => $inventory->quantity,
                'delivery_recipient' => $deliveryRecipient,
                'notes' => $notes,
                'performed_by' => $performedBy->id,
            ]);

            return $transaction;
        });
    }

    public function reserve(
        Inventory $inventory,
        int $quantity,
        User $performedBy,
        ?string $notes = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
    ): Transaction {
        if ($quantity <= 0) {
            throw new RuntimeException('Reserve quantity must be greater than zero.');
        }

        return DB::transaction(function () use ($inventory, $quantity, $performedBy, $notes, $referenceType, $referenceId) {
            $locked = Inventory::query()->whereKey($inventory->id)->lockForUpdate()->firstOrFail();

            if ($locked->availableQuantity() < $quantity) {
                throw new RuntimeException("Insufficient available stock to reserve for {$locked->item_name}.");
            }

            $before = $locked->quantity;
            $locked->reserved_quantity += $quantity;
            $locked->save();
            $locked->updateStatus();

            $inventory->setRawAttributes($locked->getAttributes());
            $inventory->syncOriginal();

            return $this->logTransaction(
                $locked,
                'reserve',
                $quantity,
                $before,
                $locked->quantity,
                $performedBy,
                $notes,
                $referenceType,
                $referenceId,
            );
        });
    }

    public function release(
        Inventory $inventory,
        int $quantity,
        User $performedBy,
        ?string $notes = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $deliveryRecipient = null,
    ): Transaction {
        if ($quantity <= 0) {
            throw new RuntimeException('Release quantity must be greater than zero.');
        }

        return DB::transaction(function () use ($inventory, $quantity, $performedBy, $notes, $referenceType, $referenceId, $deliveryRecipient) {
            $locked = Inventory::query()->whereKey($inventory->id)->lockForUpdate()->firstOrFail();

            if ($locked->quantity < $quantity) {
                throw new RuntimeException("Insufficient on-hand stock for {$locked->item_name}.");
            }

            $before = $locked->quantity;

            // Deduct on-hand stock. Clear reserved qty for this release (or all reserved if under-reserved).
            $locked->quantity -= $quantity;
            $locked->reserved_quantity = max(0, $locked->reserved_quantity - $quantity);
            $locked->save();
            $locked->updateStatus();

            $transaction = $this->logTransaction(
                $locked,
                'release',
                $quantity,
                $before,
                $locked->quantity,
                $performedBy,
                $notes,
                $referenceType,
                $referenceId,
            );

            StockLog::create([
                'inventory_id' => $locked->id,
                'action' => 'delivery',
                'quantity' => $quantity,
                'balance_after' => $locked->quantity,
                'delivery_recipient' => $deliveryRecipient,
                'notes' => $notes,
                'performed_by' => $performedBy->id,
            ]);

            // Keep caller's model in sync for subsequent operations.
            $inventory->setRawAttributes($locked->getAttributes());
            $inventory->syncOriginal();

            return $transaction;
        });
    }

    public function restore(
        Inventory $inventory,
        int $quantity,
        User $performedBy,
        ?string $notes = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
    ): Transaction {
        if ($quantity <= 0) {
            throw new RuntimeException('Restore quantity must be greater than zero.');
        }

        return DB::transaction(function () use ($inventory, $quantity, $performedBy, $notes, $referenceType, $referenceId) {
            $inventory->refresh();

            if ($inventory->reserved_quantity < $quantity) {
                throw new RuntimeException('Insufficient reserved stock to restore.');
            }

            $before = $inventory->quantity;
            $inventory->reserved_quantity -= $quantity;
            $inventory->save();
            $inventory->updateStatus();

            return $this->logTransaction(
                $inventory,
                'restore',
                $quantity,
                $before,
                $inventory->quantity,
                $performedBy,
                $notes,
                $referenceType,
                $referenceId,
            );
        });
    }

    public function adjust(
        Inventory $inventory,
        int $newQuantity,
        User $performedBy,
        ?string $notes = null,
    ): Transaction {
        if ($newQuantity < 0) {
            throw new RuntimeException('Adjusted quantity cannot be negative.');
        }

        return DB::transaction(function () use ($inventory, $newQuantity, $performedBy, $notes) {
            $inventory->refresh();
            $before = $inventory->quantity;
            $difference = abs($newQuantity - $before);

            $inventory->quantity = $newQuantity;

            if ($inventory->reserved_quantity > $inventory->quantity) {
                $inventory->reserved_quantity = $inventory->quantity;
            }

            $inventory->save();
            $inventory->updateStatus();

            $transaction = $this->logTransaction(
                $inventory,
                'adjustment',
                $difference,
                $before,
                $inventory->quantity,
                $performedBy,
                $notes,
            );

            StockLog::create([
                'inventory_id' => $inventory->id,
                'action' => 'adjustment',
                'quantity' => $difference,
                'balance_after' => $inventory->quantity,
                'notes' => $notes,
                'performed_by' => $performedBy->id,
            ]);

            return $transaction;
        });
    }

    public function logTransaction(
        Inventory $inventory,
        string $type,
        int $quantity,
        int $quantityBefore,
        int $quantityAfter,
        User $performedBy,
        ?string $notes = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
    ): Transaction {
        return Transaction::create([
            'transaction_number' => 'TXN-'.strtoupper(Str::random(10)),
            'inventory_id' => $inventory->id,
            'type' => $type,
            'quantity' => $quantity,
            'quantity_before' => $quantityBefore,
            'quantity_after' => $quantityAfter,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'notes' => $notes,
            'performed_by' => $performedBy->id,
        ]);
    }
}
