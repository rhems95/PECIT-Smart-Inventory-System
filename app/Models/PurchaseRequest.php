<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseRequest extends Model
{
    protected $fillable = [
        'purchase_number',
        'user_id',
        'status',
        'total_amount',
        'remarks',
        'verified_by',
        'released_by',
        'verified_at',
        'released_at',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'verified_at' => 'datetime',
            'released_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function releaser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'released_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseRequestItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function isCancellable(): bool
    {
        return in_array($this->status, ['pending', 'payment_submitted', 'payment_verified'], true);
    }

    /**
     * @param  Builder<PurchaseRequest>  $query
     * @return Builder<PurchaseRequest>
     */
    public function scopeRecentlyVerified(Builder $query, int $limit = 10): Builder
    {
        return $query
            ->with(['user.department'])
            ->whereNotNull('verified_at')
            ->latest('verified_at')
            ->limit($limit);
    }
}
