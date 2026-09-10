<?php

namespace App\Enums;

enum InventoryTransactionType: string
{
    case OpeningBalance = 'opening_balance';
    case StockIn = 'stock_in';
    case StockOut = 'stock_out';
    case Release = 'release';
    case Adjustment = 'adjustment';
    case AdjustmentIn = 'adjustment_in';
    case AdjustmentOut = 'adjustment_out';
    case Damage = 'damage';
    case BadOrder = 'bad_order';
    case ReturnToSupplier = 'return_to_supplier';
    case PurchaseDelivery = 'purchase_delivery';
    case Reserve = 'reserve';
    case Restore = 'restore';

    public function label(): string
    {
        return match ($this) {
            self::OpeningBalance => 'Opening balance',
            self::StockIn => 'Stock in',
            self::StockOut => 'Stock out',
            self::Release => 'Release',
            self::Adjustment => 'Adjustment',
            self::AdjustmentIn => 'Adjustment in',
            self::AdjustmentOut => 'Adjustment out',
            self::Damage => 'Damage',
            self::BadOrder => 'Bad order',
            self::ReturnToSupplier => 'Return to supplier',
            self::PurchaseDelivery => 'Purchase delivery',
            self::Reserve => 'Reserve',
            self::Restore => 'Restore',
        };
    }

    public function isPhysical(): bool
    {
        return ! in_array($this, [self::Reserve, self::Restore], true);
    }

    public function isInbound(): bool
    {
        return in_array($this, [
            self::OpeningBalance,
            self::StockIn,
            self::AdjustmentIn,
            self::PurchaseDelivery,
        ], true);
    }

    /**
     * @return array<int, string>
     */
    public static function physicalValues(): array
    {
        return array_values(array_map(
            fn (self $type) => $type->value,
            array_filter(self::cases(), fn (self $type) => $type->isPhysical()),
        ));
    }

    /**
     * Types selectable on Stock Card / stock-operation forms (not reserve/restore).
     *
     * @return array<int, self>
     */
    public static function stockCardFilters(): array
    {
        return array_values(array_filter(
            self::cases(),
            fn (self $type) => $type->isPhysical(),
        ));
    }
}
