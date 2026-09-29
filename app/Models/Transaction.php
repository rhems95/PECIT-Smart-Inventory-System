<?php

namespace App\Models;

use App\Enums\InventoryTransactionType;
use App\Enums\ReceivingInspectionStatus;
use App\Enums\StockSourceType;
use App\Support\Qty;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Transaction extends Model
{
    protected $fillable = [
        'transaction_number',
        'inventory_id',
        'type',
        'source_type',
        'quantity',
        'quantity_in',
        'quantity_out',
        'quantity_before',
        'quantity_after',
        'balance_after',
        'unit_cost',
        'total_cost',
        'supplier_id',
        'reference_type',
        'reference_id',
        'reference_number',
        'delivery_receipt_number',
        'size',
        'notes',
        'performed_by',
        'purchased_by',
        'transaction_date',
        'inspection_status',
        'inspection_notes',
        'inspected_by',
        'inspected_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => InventoryTransactionType::class,
            'source_type' => StockSourceType::class,
            'quantity' => 'decimal:4',
            'quantity_in' => 'decimal:4',
            'quantity_out' => 'decimal:4',
            'quantity_before' => 'decimal:4',
            'quantity_after' => 'decimal:4',
            'balance_after' => 'decimal:4',
            'unit_cost' => 'decimal:2',
            'total_cost' => 'decimal:2',
            'transaction_date' => 'datetime',
            'inspection_status' => ReceivingInspectionStatus::class,
            'inspected_at' => 'datetime',
        ];
    }

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }

    public function performer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'purchased_by');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspected_by');
    }

    public function scopePurchaseHistory(Builder $query): Builder
    {
        return $query->whereIn('type', [
            InventoryTransactionType::StockIn->value,
            InventoryTransactionType::PurchaseDelivery->value,
        ]);
    }

    public function isPurchaseHistoryRow(): bool
    {
        $type = $this->type instanceof InventoryTransactionType ? $this->type->value : (string) $this->type;

        return in_array($type, [
            InventoryTransactionType::StockIn->value,
            InventoryTransactionType::PurchaseDelivery->value,
        ], true);
    }

    public function inspectionLabel(): string
    {
        $status = $this->inspection_status;

        return $status instanceof ReceivingInspectionStatus
            ? $status->label()
            : ReceivingInspectionStatus::Pending->label();
    }

    public function reference(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'reference_type', 'reference_id');
    }

    public function scopePhysical(Builder $query): Builder
    {
        return $query->whereIn('type', InventoryTransactionType::physicalValues());
    }

    public function typeLabel(): string
    {
        $type = $this->type;

        return $type instanceof InventoryTransactionType ? $type->label() : (string) $type;
    }

    public function runningBalance(): float
    {
        return Qty::of($this->balance_after ?? $this->quantity_after);
    }

    public function buyerName(): string
    {
        return $this->buyer?->name ?? $this->performer?->name ?? '—';
    }
}
