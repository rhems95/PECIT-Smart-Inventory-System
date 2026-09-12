<?php

namespace App\Models;

use App\Enums\InventoryTransactionType;
use App\Enums\StockSourceType;
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
        'transaction_date',
    ];

    protected function casts(): array
    {
        return [
            'type' => InventoryTransactionType::class,
            'source_type' => StockSourceType::class,
            'quantity' => 'integer',
            'quantity_in' => 'integer',
            'quantity_out' => 'integer',
            'quantity_before' => 'integer',
            'quantity_after' => 'integer',
            'balance_after' => 'integer',
            'unit_cost' => 'decimal:2',
            'total_cost' => 'decimal:2',
            'transaction_date' => 'datetime',
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

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
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

    public function runningBalance(): int
    {
        return (int) ($this->balance_after ?? $this->quantity_after);
    }
}
