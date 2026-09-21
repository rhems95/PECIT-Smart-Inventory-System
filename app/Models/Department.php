<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
        'faculty_budget_limit',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'faculty_budget_limit' => 'decimal:2',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function inventoryItems(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function supplyRequests(): HasMany
    {
        return $this->hasMany(SupplyRequest::class);
    }
}
