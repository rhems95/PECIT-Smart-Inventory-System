<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UnitOfMeasurement extends Model
{
    protected $table = 'units_of_measurement';

    protected $fillable = [
        'name',
        'symbol',
        'description',
    ];

    public function inventoryItems(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }

    public function label(): string
    {
        return "{$this->name} ({$this->symbol})";
    }
}
