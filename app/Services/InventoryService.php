<?php

namespace App\Services;

use App\Enums\InventoryTransactionType;
use App\Enums\ReceivingInspectionStatus;
use App\Enums\StockSourceType;
use App\Models\Inventory;
use App\Models\InventorySizeStock;
use App\Models\StockLog;
use App\Models\Transaction;
use App\Models\User;
use App\Support\Qty;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class InventoryService
{
    public function __construct(
        protected AuditLogService $auditLog,
    ) {}
    public function stockIn(
        Inventory $inventory,
        float $quantity,
        User $performedBy,
        ?string $notes = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $size = null,
        array $extra = [],
    ): Transaction {
        $quantity = Qty::of($quantity);
        if ($quantity <= 0) {
            throw new RuntimeException('Stock-in quantity must be greater than zero.');
        }

        $size = $this->normalizeRequiredSize($inventory, $size, mustHaveSize: true);
        $extra = $this->normalizeStockInExtra($extra);

        return DB::transaction(function () use ($inventory, $quantity, $performedBy, $notes, $referenceType, $referenceId, $size, $extra) {
            $locked = Inventory::query()->whereKey($inventory->id)->lockForUpdate()->firstOrFail();

            if ($size !== null) {
                $stock = $this->lockOrCreateSizeStock($locked, $size);
                $before = $stock->quantity;
                $stock->quantity = Qty::add($stock->quantity, $quantity);
                $stock->save();
                $locked->syncAggregatesFromSizeStocks();
                $locked->refresh();
                $locked->updateStatus();
                $notes = $this->appendSizeNote($notes, $size);
                $qtyAfterForTxn = $stock->quantity;
            } else {
                $before = $locked->quantity;
                $locked->quantity = Qty::add($locked->quantity, $quantity);
                $locked->save();
                $locked->updateStatus();
                $qtyAfterForTxn = $locked->quantity;
            }

            $transaction = $this->logTransaction(
                $locked,
                $extra['type'] ?? InventoryTransactionType::StockIn->value,
                $quantity,
                $before,
                $qtyAfterForTxn,
                $performedBy,
                $notes,
                $referenceType,
                $referenceId,
                $extra + ['size' => $size],
            );

            StockLog::create([
                'inventory_id' => $locked->id,
                'action' => 'stock_in',
                'quantity' => $quantity,
                'balance_after' => $locked->quantity,
                'notes' => $notes,
                'performed_by' => $performedBy->id,
            ]);

            $this->auditLog->log($performedBy, 'inventory.stock_in', $locked, null, [
                'item' => $locked->item_name,
                'quantity' => $quantity,
                'size' => $size,
                'notes' => $notes,
            ]);

            $inventory->setRawAttributes($locked->getAttributes());
            $inventory->syncOriginal();

            return $transaction;
        });
    }

    public function stockOut(
        Inventory $inventory,
        float $quantity,
        User $performedBy,
        ?string $notes = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $deliveryRecipient = null,
        ?string $size = null,
        array $extra = [],
    ): Transaction {
        $quantity = Qty::of($quantity);
        if ($quantity <= 0) {
            throw new RuntimeException('Stock-out quantity must be greater than zero.');
        }

        $type = (string) ($extra['type'] ?? InventoryTransactionType::StockOut->value);
        if (! filled($notes)) {
            throw new RuntimeException('A reason is required for this stock movement.');
        }
        if ($type === InventoryTransactionType::ReturnToSupplier->value && empty($extra['supplier_id'])) {
            throw new RuntimeException('Supplier is required when returning items.');
        }

        $size = $this->normalizeRequiredSize($inventory, $size, mustHaveSize: false);

        return DB::transaction(function () use ($inventory, $quantity, $performedBy, $notes, $referenceType, $referenceId, $deliveryRecipient, $size, $extra, $type) {
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
                $stock->quantity = Qty::sub($stock->quantity, $quantity);
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
                $locked->quantity = Qty::sub($locked->quantity, $quantity);
                $locked->save();
                $locked->updateStatus();
                $qtyAfterForTxn = $locked->quantity;
            }

            $transaction = $this->logTransaction(
                $locked,
                $type,
                $quantity,
                $before,
                $qtyAfterForTxn,
                $performedBy,
                $notes,
                $referenceType,
                $referenceId,
                $extra + ['size' => $size],
            );

            StockLog::create([
                'inventory_id' => $locked->id,
                'action' => $type === InventoryTransactionType::StockOut->value ? 'delivery' : 'stock_out',
                'quantity' => $quantity,
                'balance_after' => $locked->quantity,
                'delivery_recipient' => $deliveryRecipient,
                'notes' => $notes,
                'performed_by' => $performedBy->id,
            ]);

            $this->auditLog->log($performedBy, 'inventory.'.$type, $locked, null, [
                'item' => $locked->item_name,
                'quantity' => $quantity,
                'size' => $size,
                'notes' => $notes,
            ]);

            $inventory->setRawAttributes($locked->getAttributes());
            $inventory->syncOriginal();

            return $transaction;
        });
    }

    public function recordDamage(
        Inventory $inventory,
        float $quantity,
        User $performedBy,
        string $reason,
        ?string $size = null,
        array $extra = [],
    ): Transaction {
        return $this->stockOut(
            $inventory,
            $quantity,
            $performedBy,
            $reason,
            $extra['reference_type'] ?? null,
            $extra['reference_id'] ?? null,
            null,
            $size,
            $extra + [
                'type' => InventoryTransactionType::Damage->value,
                'source_type' => $extra['source_type'] ?? StockSourceType::Other->value,
            ],
        );
    }

    public function recordBadOrder(
        Inventory $inventory,
        float $quantity,
        User $performedBy,
        string $reason,
        ?string $size = null,
        array $extra = [],
    ): Transaction {
        return $this->stockOut(
            $inventory,
            $quantity,
            $performedBy,
            $reason,
            $extra['reference_type'] ?? null,
            $extra['reference_id'] ?? null,
            null,
            $size,
            $extra + [
                'type' => InventoryTransactionType::BadOrder->value,
                'source_type' => $extra['source_type'] ?? StockSourceType::Other->value,
            ],
        );
    }

    public function returnToSupplier(
        Inventory $inventory,
        float $quantity,
        User $performedBy,
        string $reason,
        int $supplierId,
        ?string $size = null,
        array $extra = [],
    ): Transaction {
        return $this->stockOut(
            $inventory,
            $quantity,
            $performedBy,
            $reason,
            $extra['reference_type'] ?? null,
            $extra['reference_id'] ?? null,
            null,
            $size,
            $extra + [
                'type' => InventoryTransactionType::ReturnToSupplier->value,
                'source_type' => StockSourceType::Return->value,
                'supplier_id' => $supplierId,
            ],
        );
    }

    public function reserve(
        Inventory $inventory,
        float $quantity,
        User $performedBy,
        ?string $notes = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $size = null,
    ): Transaction {
        $quantity = Qty::of($quantity);
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
                $stock->reserved_quantity = Qty::add($stock->reserved_quantity, $quantity);
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
                $locked->reserved_quantity = Qty::add($locked->reserved_quantity, $quantity);
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
                ['size' => $size],
            );
        });
    }

    public function release(
        Inventory $inventory,
        float $quantity,
        User $performedBy,
        ?string $notes = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $deliveryRecipient = null,
        ?string $size = null,
    ): Transaction {
        $quantity = Qty::of($quantity);
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
                $stock->quantity = Qty::sub($stock->quantity, $quantity);
                $stock->reserved_quantity = max(0, Qty::sub($stock->reserved_quantity, $quantity));
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
                $locked->quantity = Qty::sub($locked->quantity, $quantity);
                $locked->reserved_quantity = max(0, Qty::sub($locked->reserved_quantity, $quantity));
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
                ['size' => $size],
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
        float $quantity,
        User $performedBy,
        ?string $notes = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $size = null,
    ): Transaction {
        $quantity = Qty::of($quantity);
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
                $stock->reserved_quantity = Qty::sub($stock->reserved_quantity, $quantity);
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
                $locked->reserved_quantity = Qty::sub($locked->reserved_quantity, $quantity);
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
                ['size' => $size],
            );
        });
    }

    public function adjust(
        Inventory $inventory,
        float $newQuantity,
        User $performedBy,
        ?string $notes = null,
        ?string $size = null,
    ): Transaction {
        $newQuantity = Qty::of($newQuantity);
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
                $difference = Qty::of(abs($newQuantity - $before));
                $stock->quantity = $newQuantity;
                $stock->save();
                $locked->syncAggregatesFromSizeStocks();
                $locked->refresh();
                $locked->updateStatus();
                $notes = $this->appendSizeNote($notes, $size);
                $qtyAfterForTxn = $stock->quantity;
            } else {
                $before = $locked->quantity;
                $difference = Qty::of(abs($newQuantity - $before));
                $locked->quantity = $newQuantity;
                if ($locked->reserved_quantity > $locked->quantity) {
                    $locked->reserved_quantity = $locked->quantity;
                }
                $locked->save();
                $locked->updateStatus();
                $qtyAfterForTxn = $locked->quantity;
            }

            $adjustType = $qtyAfterForTxn > $before
                ? InventoryTransactionType::AdjustmentIn->value
                : ($qtyAfterForTxn < $before
                    ? InventoryTransactionType::AdjustmentOut->value
                    : InventoryTransactionType::Adjustment->value);

            $transaction = $this->logTransaction(
                $locked,
                $adjustType,
                $difference,
                $before,
                $qtyAfterForTxn,
                $performedBy,
                $notes,
                null,
                null,
                [
                    'source_type' => StockSourceType::Adjustment->value,
                    'size' => $size,
                ],
            );

            StockLog::create([
                'inventory_id' => $locked->id,
                'action' => 'adjustment',
                'quantity' => $difference,
                'balance_after' => $locked->quantity,
                'notes' => $notes,
                'performed_by' => $performedBy->id,
            ]);

            $this->auditLog->log($performedBy, 'inventory.adjusted', $locked, [
                'quantity' => $before,
            ], [
                'item' => $locked->item_name,
                'quantity' => $newQuantity,
                'size' => $size,
                'notes' => $notes,
            ]);

            $inventory->setRawAttributes($locked->getAttributes());
            $inventory->syncOriginal();

            return $transaction;
        });
    }

    public function logTransaction(
        Inventory $inventory,
        string $type,
        float $quantity,
        float $quantityBefore,
        float $quantityAfter,
        User $performedBy,
        ?string $notes = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
        array $extra = [],
    ): Transaction {
        $quantity = Qty::of($quantity);
        $quantityBefore = Qty::of($quantityBefore);
        $quantityAfter = Qty::of($quantityAfter);
        $type = (string) ($extra['type'] ?? $type);
        [$quantityIn, $quantityOut] = $this->splitInOut($type, $quantity, $quantityBefore, $quantityAfter);
        $unitCost = isset($extra['unit_cost']) && $extra['unit_cost'] !== '' && $extra['unit_cost'] !== null
            ? round((float) $extra['unit_cost'], 2)
            : null;
        $moved = max($quantityIn, $quantityOut, $quantity);

        $inbound = in_array($type, [
            InventoryTransactionType::StockIn->value,
            InventoryTransactionType::PurchaseDelivery->value,
        ], true);

        return Transaction::create([
            'transaction_number' => 'TXN-'.strtoupper(Str::random(10)),
            'inventory_id' => $inventory->id,
            'type' => $type,
            'source_type' => $extra['source_type'] ?? null,
            'quantity' => $quantity,
            'quantity_in' => $quantityIn,
            'quantity_out' => $quantityOut,
            'quantity_before' => $quantityBefore,
            'quantity_after' => $quantityAfter,
            'balance_after' => $quantityAfter,
            'unit_cost' => $unitCost,
            'total_cost' => $unitCost !== null ? round($unitCost * $moved, 2) : null,
            'supplier_id' => $extra['supplier_id'] ?? null,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'reference_number' => $extra['reference_number'] ?? null,
            'delivery_receipt_number' => $extra['delivery_receipt_number'] ?? null,
            'size' => $extra['size'] ?? null,
            'notes' => $notes,
            'performed_by' => $performedBy->id,
            'purchased_by' => $extra['purchased_by'] ?? ($inbound ? $performedBy->id : null),
            'transaction_date' => $extra['transaction_date'] ?? now(),
            'inspection_status' => $extra['inspection_status'] ?? ($inbound ? ReceivingInspectionStatus::Pending->value : null),
        ]);
    }

    public function recordOpeningBalance(
        Inventory $inventory,
        float $quantity,
        User $performedBy,
        ?string $size = null,
    ): ?Transaction {
        $quantity = Qty::of($quantity);
        if ($quantity <= 0) {
            return null;
        }

        $after = $size
            ? Qty::of($inventory->sizeStockFor($size)?->quantity ?? $quantity)
            : Qty::of($inventory->quantity);

        return $this->logTransaction(
            $inventory,
            InventoryTransactionType::OpeningBalance->value,
            $quantity,
            0,
            $after,
            $performedBy,
            'Opening balance',
            null,
            null,
            [
                'source_type' => StockSourceType::OpeningBalance->value,
                'size' => $size,
                'unit_cost' => $inventory->unit_price,
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    protected function normalizeStockInExtra(array $extra): array
    {
        $source = $extra['source_type'] ?? StockSourceType::ManualExternal->value;
        $sourceValue = $source instanceof StockSourceType ? $source->value : (string) $source;

        if (! isset($extra['type'])) {
            $extra['type'] = match ($sourceValue) {
                StockSourceType::OpeningBalance->value => InventoryTransactionType::OpeningBalance->value,
                StockSourceType::PurchaseOrder->value => InventoryTransactionType::PurchaseDelivery->value,
                default => InventoryTransactionType::StockIn->value,
            };
        }

        $extra['source_type'] = $sourceValue;

        return $extra;
    }

    /**
     * @return array{0: float, 1: float}
     */
    protected function splitInOut(string $type, float $quantity, float $before, float $after): array
    {
        $enum = InventoryTransactionType::tryFrom($type);

        if (in_array($type, [
            InventoryTransactionType::Reserve->value,
            InventoryTransactionType::Restore->value,
        ], true)) {
            return [0, 0];
        }

        if ($type === InventoryTransactionType::Adjustment->value) {
            if ($after > $before) {
                return [$quantity, 0];
            }
            if ($after < $before) {
                return [0, $quantity];
            }

            return [0, 0];
        }

        if ($enum?->isInbound()) {
            return [$quantity, 0];
        }

        return [0, $quantity];
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

    protected function reserveAcrossSizes(Inventory $inventory, float $quantity): void
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
            $stock->reserved_quantity = Qty::add($stock->reserved_quantity, $take);
            $stock->save();
            $remaining = Qty::sub($remaining, $take);
            if ($remaining <= 0) {
                break;
            }
        }

        if ($remaining > 0) {
            throw new RuntimeException("Insufficient available stock to reserve for {$inventory->item_name}.");
        }

        $inventory->syncAggregatesFromSizeStocks();
    }

    protected function releaseAcrossSizes(Inventory $inventory, float $quantity): void
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
            $stock->quantity = Qty::sub($stock->quantity, $take);
            $stock->reserved_quantity = max(0, Qty::sub($stock->reserved_quantity, $take));
            $stock->save();
            $remaining = Qty::sub($remaining, $take);
            if ($remaining <= 0) {
                break;
            }
        }

        if ($remaining > 0) {
            throw new RuntimeException("Insufficient on-hand stock for {$inventory->item_name}.");
        }

        $inventory->syncAggregatesFromSizeStocks();
    }

    protected function restoreAcrossSizes(Inventory $inventory, float $quantity): void
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
            $stock->reserved_quantity = Qty::sub($stock->reserved_quantity, $take);
            $stock->save();
            $remaining = Qty::sub($remaining, $take);
            if ($remaining <= 0) {
                break;
            }
        }

        if ($remaining > 0) {
            throw new RuntimeException('Insufficient reserved stock to restore.');
        }

        $inventory->syncAggregatesFromSizeStocks();
    }

    protected function deductAvailableAcrossSizes(Inventory $inventory, float $quantity): void
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
            $stock->quantity = Qty::sub($stock->quantity, $take);
            $stock->save();
            $remaining = Qty::sub($remaining, $take);
            if ($remaining <= 0) {
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
