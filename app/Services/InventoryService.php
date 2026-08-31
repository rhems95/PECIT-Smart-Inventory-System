<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\InventorySizeStock;
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
        ?string $size = null,
    ): Transaction {
        if ($quantity <= 0) {
            throw new RuntimeException('Stock-in quantity must be greater than zero.');
        }

        $size = $this->normalizeRequiredSize($inventory, $size, mustHaveSize: true);

        return DB::transaction(function () use ($inventory, $quantity, $performedBy, $notes, $referenceType, $referenceId, $size) {
            $locked = Inventory::query()->whereKey($inventory->id)->lockForUpdate()->firstOrFail();

            if ($size !== null) {
                $stock = $this->lockOrCreateSizeStock($locked, $size);
                $before = $stock->quantity;
                $stock->quantity += $quantity;
                $stock->save();
                $locked->syncAggregatesFromSizeStocks();
                $locked->refresh();
                $locked->updateStatus();
                $notes = $this->appendSizeNote($notes, $size);
                $qtyAfterForTxn = $stock->quantity;
            } else {
                $before = $locked->quantity;
                $locked->quantity += $quantity;
                $locked->save();
                $locked->updateStatus();
                $qtyAfterForTxn = $locked->quantity;
            }

            $transaction = $this->logTransaction(
                $locked,
                'stock_in',
                $quantity,
                $before,
                $qtyAfterForTxn,
                $performedBy,
                $notes,
                $referenceType,
                $referenceId,
            );

            StockLog::create([
                'inventory_id' => $locked->id,
                'action' => 'stock_in',
                'quantity' => $quantity,
                'balance_after' => $locked->quantity,
                'notes' => $notes,
                'performed_by' => $performedBy->id,
            ]);

            $inventory->setRawAttributes($locked->getAttributes());
            $inventory->syncOriginal();

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
        ?string $size = null,
    ): Transaction {
        if ($quantity <= 0) {
            throw new RuntimeException('Stock-out quantity must be greater than zero.');
        }

        $size = $this->normalizeRequiredSize($inventory, $size, mustHaveSize: false);

        return DB::transaction(function () use ($inventory, $quantity, $performedBy, $notes, $referenceType, $referenceId, $deliveryRecipient, $size) {
            $locked = Inventory::query()->whereKey($inventory->id)->lockForUpdate()->firstOrFail();

            if ($locked->requiresSize() && $size === null) {
                $before = $locked->quantity;
                $this->deductAvailableAcrossSizes($locked, $quantity);
                $locked->refresh();
                $locked->updateStatus();
                $qtyAfterForTxn = $locked->quantity;
            } elseif ($size !== null) {
                $stock = $this->lockSizeStock($locked, $size);
                if ($stock->availableQuantity() < $quantity) {
                    throw new RuntimeException("Insufficient available stock for size {$size}.");
                }
                $before = $stock->quantity;
                $stock->quantity -= $quantity;
                $stock->save();
                $locked->syncAggregatesFromSizeStocks();
                $locked->refresh();
                $locked->updateStatus();
                $notes = $this->appendSizeNote($notes, $size);
                $qtyAfterForTxn = $stock->quantity;
            } else {
                if ($locked->availableQuantity() < $quantity) {
                    throw new RuntimeException('Insufficient available stock.');
                }
                $before = $locked->quantity;
                $locked->quantity -= $quantity;
                $locked->save();
                $locked->updateStatus();
                $qtyAfterForTxn = $locked->quantity;
            }

            $transaction = $this->logTransaction(
                $locked,
                'stock_out',
                $quantity,
                $before,
                $qtyAfterForTxn,
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

            $inventory->setRawAttributes($locked->getAttributes());
            $inventory->syncOriginal();

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
        ?string $size = null,
    ): Transaction {
        if ($quantity <= 0) {
            throw new RuntimeException('Reserve quantity must be greater than zero.');
        }

        $size = $this->normalizeRequiredSize($inventory, $size, mustHaveSize: false);

        return DB::transaction(function () use ($inventory, $quantity, $performedBy, $notes, $referenceType, $referenceId, $size) {
            $locked = Inventory::query()->whereKey($inventory->id)->lockForUpdate()->firstOrFail();

            if ($locked->requiresSize() && $size === null) {
                $before = $locked->quantity;
                $this->reserveAcrossSizes($locked, $quantity);
                $locked->refresh();
                $locked->updateStatus();
            } elseif ($size !== null) {
                $stock = $this->lockSizeStock($locked, $size);
                if ($stock->availableQuantity() < $quantity) {
                    throw new RuntimeException("Insufficient available stock to reserve for {$locked->item_name} size {$size}.");
                }
                $before = $stock->quantity;
                $stock->reserved_quantity += $quantity;
                $stock->save();
                $locked->syncAggregatesFromSizeStocks();
                $locked->refresh();
                $locked->updateStatus();
                $notes = $this->appendSizeNote($notes, $size);
            } else {
                if ($locked->availableQuantity() < $quantity) {
                    throw new RuntimeException("Insufficient available stock to reserve for {$locked->item_name}.");
                }
                $before = $locked->quantity;
                $locked->reserved_quantity += $quantity;
                $locked->save();
                $locked->updateStatus();
            }

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
        ?string $size = null,
    ): Transaction {
        if ($quantity <= 0) {
            throw new RuntimeException('Release quantity must be greater than zero.');
        }

        $size = $this->normalizeRequiredSize($inventory, $size, mustHaveSize: false);

        return DB::transaction(function () use ($inventory, $quantity, $performedBy, $notes, $referenceType, $referenceId, $deliveryRecipient, $size) {
            $locked = Inventory::query()->whereKey($inventory->id)->lockForUpdate()->firstOrFail();

            if ($locked->requiresSize() && $size === null) {
                $before = $locked->quantity;
                $this->releaseAcrossSizes($locked, $quantity);
                $locked->refresh();
                $locked->updateStatus();
                $qtyAfterForTxn = $locked->quantity;
            } elseif ($size !== null) {
                $stock = $this->lockSizeStock($locked, $size);
                if ($stock->quantity < $quantity) {
                    throw new RuntimeException("Insufficient on-hand stock for {$locked->item_name} size {$size}.");
                }
                $before = $stock->quantity;
                $stock->quantity -= $quantity;
                $stock->reserved_quantity = max(0, $stock->reserved_quantity - $quantity);
                $stock->save();
                $locked->syncAggregatesFromSizeStocks();
                $locked->refresh();
                $locked->updateStatus();
                $notes = $this->appendSizeNote($notes, $size);
                $qtyAfterForTxn = $stock->quantity;
            } else {
                if ($locked->quantity < $quantity) {
                    throw new RuntimeException("Insufficient on-hand stock for {$locked->item_name}.");
                }
                $before = $locked->quantity;
                $locked->quantity -= $quantity;
                $locked->reserved_quantity = max(0, $locked->reserved_quantity - $quantity);
                $locked->save();
                $locked->updateStatus();
                $qtyAfterForTxn = $locked->quantity;
            }

            $transaction = $this->logTransaction(
                $locked,
                'release',
                $quantity,
                $before,
                $qtyAfterForTxn,
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
        ?string $size = null,
    ): Transaction {
        if ($quantity <= 0) {
            throw new RuntimeException('Restore quantity must be greater than zero.');
        }

        $size = $this->normalizeRequiredSize($inventory, $size, mustHaveSize: false);

        return DB::transaction(function () use ($inventory, $quantity, $performedBy, $notes, $referenceType, $referenceId, $size) {
            $locked = Inventory::query()->whereKey($inventory->id)->lockForUpdate()->firstOrFail();

            if ($locked->requiresSize() && $size === null) {
                $before = $locked->quantity;
                $this->restoreAcrossSizes($locked, $quantity);
                $locked->refresh();
                $locked->updateStatus();
            } elseif ($size !== null) {
                $stock = $this->lockSizeStock($locked, $size);
                if ($stock->reserved_quantity < $quantity) {
                    throw new RuntimeException("Insufficient reserved stock to restore for size {$size}.");
                }
                $before = $stock->quantity;
                $stock->reserved_quantity -= $quantity;
                $stock->save();
                $locked->syncAggregatesFromSizeStocks();
                $locked->refresh();
                $locked->updateStatus();
                $notes = $this->appendSizeNote($notes, $size);
            } else {
                $locked->refresh();
                if ($locked->reserved_quantity < $quantity) {
                    throw new RuntimeException('Insufficient reserved stock to restore.');
                }
                $before = $locked->quantity;
                $locked->reserved_quantity -= $quantity;
                $locked->save();
                $locked->updateStatus();
            }

            $inventory->setRawAttributes($locked->getAttributes());
            $inventory->syncOriginal();

            return $this->logTransaction(
                $locked,
                'restore',
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

    public function adjust(
        Inventory $inventory,
        int $newQuantity,
        User $performedBy,
        ?string $notes = null,
        ?string $size = null,
    ): Transaction {
        if ($newQuantity < 0) {
            throw new RuntimeException('Adjusted quantity cannot be negative.');
        }

        $size = $this->normalizeRequiredSize($inventory, $size, mustHaveSize: true);

        return DB::transaction(function () use ($inventory, $newQuantity, $performedBy, $notes, $size) {
            $locked = Inventory::query()->whereKey($inventory->id)->lockForUpdate()->firstOrFail();

            if ($size !== null) {
                $stock = $this->lockOrCreateSizeStock($locked, $size);
                if ($newQuantity < $stock->reserved_quantity) {
                    throw new RuntimeException(
                        "Cannot set size {$size} on-hand ({$newQuantity}) below reserved ({$stock->reserved_quantity}). Available is on-hand minus reserved."
                    );
                }
                $before = $stock->quantity;
                $difference = abs($newQuantity - $before);
                $stock->quantity = $newQuantity;
                $stock->save();
                $locked->syncAggregatesFromSizeStocks();
                $locked->refresh();
                $locked->updateStatus();
                $notes = $this->appendSizeNote($notes, $size);
                $qtyAfterForTxn = $stock->quantity;
            } else {
                $before = $locked->quantity;
                $difference = abs($newQuantity - $before);
                $locked->quantity = $newQuantity;
                if ($locked->reserved_quantity > $locked->quantity) {
                    $locked->reserved_quantity = $locked->quantity;
                }
                $locked->save();
                $locked->updateStatus();
                $qtyAfterForTxn = $locked->quantity;
            }

            $transaction = $this->logTransaction(
                $locked,
                'adjustment',
                $difference,
                $before,
                $qtyAfterForTxn,
                $performedBy,
                $notes,
            );

            StockLog::create([
                'inventory_id' => $locked->id,
                'action' => 'adjustment',
                'quantity' => $difference,
                'balance_after' => $locked->quantity,
                'notes' => $notes,
                'performed_by' => $performedBy->id,
            ]);

            $inventory->setRawAttributes($locked->getAttributes());
            $inventory->syncOriginal();

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

    protected function normalizeRequiredSize(Inventory $inventory, ?string $size, bool $mustHaveSize = false): ?string
    {
        if (! $inventory->requiresSize()) {
            return null;
        }

        $size = is_string($size) ? strtoupper(trim($size)) : '';
        $allowed = config('psis.uniform_sizes', []);

        if ($size === '') {
            if ($mustHaveSize) {
                throw new RuntimeException("A valid size is required for {$inventory->item_name}.");
            }

            return null;
        }

        if (! in_array($size, $allowed, true)) {
            throw new RuntimeException("A valid size is required for {$inventory->item_name}.");
        }

        return $size;
    }

    protected function reserveAcrossSizes(Inventory $inventory, int $quantity): void
    {
        $remaining = $quantity;
        $stocks = InventorySizeStock::query()
            ->where('inventory_id', $inventory->id)
            ->orderBy('size')
            ->lockForUpdate()
            ->get();

        foreach ($stocks as $stock) {
            $take = min($remaining, $stock->availableQuantity());
            if ($take <= 0) {
                continue;
            }
            $stock->reserved_quantity += $take;
            $stock->save();
            $remaining -= $take;
            if ($remaining === 0) {
                break;
            }
        }

        if ($remaining > 0) {
            throw new RuntimeException("Insufficient available stock to reserve for {$inventory->item_name}.");
        }

        $inventory->syncAggregatesFromSizeStocks();
    }

    protected function releaseAcrossSizes(Inventory $inventory, int $quantity): void
    {
        $remaining = $quantity;
        $stocks = InventorySizeStock::query()
            ->where('inventory_id', $inventory->id)
            ->orderBy('size')
            ->lockForUpdate()
            ->get();

        foreach ($stocks as $stock) {
            $take = min($remaining, $stock->quantity);
            if ($take <= 0) {
                continue;
            }
            $stock->quantity -= $take;
            $stock->reserved_quantity = max(0, $stock->reserved_quantity - $take);
            $stock->save();
            $remaining -= $take;
            if ($remaining === 0) {
                break;
            }
        }

        if ($remaining > 0) {
            throw new RuntimeException("Insufficient on-hand stock for {$inventory->item_name}.");
        }

        $inventory->syncAggregatesFromSizeStocks();
    }

    protected function restoreAcrossSizes(Inventory $inventory, int $quantity): void
    {
        $remaining = $quantity;
        $stocks = InventorySizeStock::query()
            ->where('inventory_id', $inventory->id)
            ->orderBy('size')
            ->lockForUpdate()
            ->get();

        foreach ($stocks as $stock) {
            $take = min($remaining, $stock->reserved_quantity);
            if ($take <= 0) {
                continue;
            }
            $stock->reserved_quantity -= $take;
            $stock->save();
            $remaining -= $take;
            if ($remaining === 0) {
                break;
            }
        }

        if ($remaining > 0) {
            throw new RuntimeException('Insufficient reserved stock to restore.');
        }

        $inventory->syncAggregatesFromSizeStocks();
    }

    protected function deductAvailableAcrossSizes(Inventory $inventory, int $quantity): void
    {
        $remaining = $quantity;
        $stocks = InventorySizeStock::query()
            ->where('inventory_id', $inventory->id)
            ->orderBy('size')
            ->lockForUpdate()
            ->get();

        foreach ($stocks as $stock) {
            $take = min($remaining, $stock->availableQuantity());
            if ($take <= 0) {
                continue;
            }
            $stock->quantity -= $take;
            $stock->save();
            $remaining -= $take;
            if ($remaining === 0) {
                break;
            }
        }

        if ($remaining > 0) {
            throw new RuntimeException('Insufficient available stock.');
        }

        $inventory->syncAggregatesFromSizeStocks();
    }

    protected function lockOrCreateSizeStock(Inventory $inventory, string $size): InventorySizeStock
    {
        $stock = InventorySizeStock::query()
            ->where('inventory_id', $inventory->id)
            ->where('size', $size)
            ->lockForUpdate()
            ->first();

        if ($stock) {
            return $stock;
        }

        return InventorySizeStock::create([
            'inventory_id' => $inventory->id,
            'size' => $size,
            'quantity' => 0,
            'reserved_quantity' => 0,
        ]);
    }

    protected function lockSizeStock(Inventory $inventory, string $size): InventorySizeStock
    {
        $stock = InventorySizeStock::query()
            ->where('inventory_id', $inventory->id)
            ->where('size', $size)
            ->lockForUpdate()
            ->first();

        if (! $stock) {
            throw new RuntimeException("No stock record for {$inventory->item_name} size {$size}.");
        }

        return $stock;
    }

    protected function appendSizeNote(?string $notes, string $size): string
    {
        $tag = "Size: {$size}";

        return $notes ? "{$notes} ({$tag})" : $tag;
    }
}
