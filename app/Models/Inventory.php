<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inventory extends Model
{
    protected $table = 'inventory';

    protected $fillable = [
        'item_code',
        'item_name',
        'description',
        'category_id',
        'unit',
        'unit_price',
        'quantity',
        'reserved_quantity',
        'minimum_stock',
        'location',
        'status',
        'student_shop',
        'department_id',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'quantity' => 'integer',
            'reserved_quantity' => 'integer',
            'minimum_stock' => 'integer',
            'student_shop' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Student Uniform Shop rules:
     * - Shared items (department_id null): P.E., NSTP, ID lanyard — all students
     * - Exclusive items (department_id set): ONLY that department's students
     * - Students never see another department's exclusive uniform
     *
     * @param  Builder<Inventory>  $query
     * @return Builder<Inventory>
     */
    public function scopeForStudentShop(Builder $query, User $user): Builder
    {
        return $query
            ->where('student_shop', true)
            ->where('status', '!=', 'discontinued')
            ->where(function ($q) use ($user) {
                // Shared campus items
                $q->whereNull('department_id');

                // Own department exclusive uniforms only
                if ($user->department_id) {
                    $q->orWhere('department_id', $user->department_id);
                }
            });
    }

    /**
     * Whether this inventory row may be purchased by the student in Uniform Shop.
     */
    public function isAvailableInStudentShop(?User $user): bool
    {
        if (! $user || ! $this->student_shop || $this->status === 'discontinued') {
            return false;
        }

        // Shared: P.E. / NSTP / lanyard (no department lock)
        if ($this->department_id === null) {
            return true;
        }

        // Exclusive: buyer must belong to the same department
        return $user->department_id !== null
            && (int) $this->department_id === (int) $user->department_id;
    }

    public function isDepartmentExclusive(): bool
    {
        return $this->student_shop && $this->department_id !== null;
    }

    /**
     * Clothing uniforms in the student shop require a size before purchase.
     * Accessories such as ID lanyards do not.
     */
    public function requiresSize(): bool
    {
        if (! $this->student_shop) {
            return false;
        }

        $haystack = strtolower(($this->item_name ?? '').' '.($this->item_code ?? ''));

        return ! str_contains($haystack, 'lanyard');
    }

    public function requestItems(): HasMany
    {
        return $this->hasMany(RequestItem::class);
    }

    public function purchaseRequestItems(): HasMany
    {
        return $this->hasMany(PurchaseRequestItem::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function stockLogs(): HasMany
    {
        return $this->hasMany(StockLog::class);
    }

    public function sizeStocks(): HasMany
    {
        return $this->hasMany(InventorySizeStock::class);
    }

    public function deletionBlockReason(): ?string
    {
        if ((int) $this->reserved_quantity > 0) {
            return 'Cannot delete: stock is reserved. Release or cancel related requests first.';
        }

        if ($this->requestItems()->exists()) {
            return 'Cannot delete: this item is used on a faculty request.';
        }

        if ($this->purchaseRequestItems()->exists()) {
            return 'Cannot delete: this item is used on a student purchase.';
        }

        return null;
    }

    public function canBeDeleted(): bool
    {
        return $this->deletionBlockReason() === null;
    }

    /**
     * Available qty overall, or for a specific size when this item tracks sizes.
     */
    public function availableQuantity(?string $size = null): int
    {
        if ($this->requiresSize()) {
            if ($size !== null && $size !== '') {
                $stock = $this->sizeStockFor($size);

                return $stock ? $stock->availableQuantity() : 0;
            }

            return (int) $this->sizeStocks->sum(fn (InventorySizeStock $s) => $s->availableQuantity());
        }

        return max(0, $this->quantity - $this->reserved_quantity);
    }

    public function sizeStockFor(string $size): ?InventorySizeStock
    {
        $size = strtoupper(trim($size));

        if ($this->relationLoaded('sizeStocks')) {
            return $this->sizeStocks->firstWhere('size', $size);
        }

        return $this->sizeStocks()->where('size', $size)->first();
    }

    /**
     * @return array<string, int> size => available qty
     */
    public function availableBySize(): array
    {
        $map = [];
        foreach ($this->sizeStocks as $stock) {
            $map[$stock->size] = $stock->availableQuantity();
        }

        return $map;
    }

    /**
     * Keep parent quantity/reserved_quantity as sums of size rows (for reports & status).
     */
    public function syncAggregatesFromSizeStocks(): void
    {
        if (! $this->requiresSize()) {
            return;
        }

        $stocks = $this->sizeStocks()->get();
        $this->quantity = (int) $stocks->sum('quantity');
        $this->reserved_quantity = (int) $stocks->sum('reserved_quantity');
        $this->save();
    }

    public function isLowStock(): bool
    {
        $available = $this->availableQuantity();

        return $available > 0 && $available <= $this->minimum_stock;
    }

    public function isOutOfStock(): bool
    {
        return $this->availableQuantity() <= 0;
    }

    public function updateStatus(): void
    {
        if ($this->status === 'discontinued') {
            return;
        }

        if ($this->isOutOfStock()) {
            $this->status = 'out_of_stock';
        } elseif ($this->isLowStock()) {
            $this->status = 'low_stock';
        } else {
            $this->status = 'available';
        }

        $this->save();
    }
}
