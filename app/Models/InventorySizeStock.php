<?php

namespace App\Models;

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
            'quantity' => 'integer',
            'reserved_quantity' => 'integer',
        ];
    }

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }

    public function availableQuantity(): int
    {
        return max(0, $this->quantity - $this->reserved_quantity);
    }
}
