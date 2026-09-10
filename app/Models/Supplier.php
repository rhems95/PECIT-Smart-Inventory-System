<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    protected $fillable = [
        'supplier_code',
        'name',
        'contact_person',
        'phone',
        'email',
        'address',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public static function nextCode(): string
    {
        $last = static::query()->orderByDesc('id')->value('supplier_code');
        $n = 1;
        if (is_string($last) && preg_match('/(\d+)$/', $last, $m)) {
            $n = (int) $m[1] + 1;
        }

        return 'SUP-'.str_pad((string) $n, 4, '0', STR_PAD_LEFT);
    }

    protected static function booted(): void
    {
        static::creating(function (Supplier $supplier) {
            if (! $supplier->supplier_code) {
                $supplier->supplier_code = static::nextCode();
            }
            if ($supplier->supplier_code) {
                $supplier->supplier_code = strtoupper($supplier->supplier_code);
            }
        });
    }
}
