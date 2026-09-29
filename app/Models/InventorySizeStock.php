<?php

namespace App\Models;

use App\Support\Qty;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventorySizeStock extends Model
{
    protected $fillable = [
        'inventory_id',
        'size',
        'quantity',
        'reserved_quantity',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
            'reserved_quantity' => 'decimal:4',
        ];
    }

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }

    public function availableQuantity(): float
    {
        return max(0, Qty::sub($this->quantity, $this->reserved_quantity));
    }
}
