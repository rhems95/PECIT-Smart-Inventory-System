<?php

namespace App\Enums;

enum StockSourceType: string
{
    case PurchaseOrder = 'purchase_order';
    case ManualExternal = 'manual_external';
    case EmergencyPurchase = 'emergency_purchase';
    case Donation = 'donation';
    case OpeningBalance = 'opening_balance';
    case Adjustment = 'adjustment';
    case Return = 'return';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::PurchaseOrder => 'Purchase order',
            self::ManualExternal => 'Manual / external',
            self::EmergencyPurchase => 'Emergency purchase',
            self::Donation => 'Donation',
            self::OpeningBalance => 'Opening balance',
            self::Adjustment => 'Adjustment',
            self::Return => 'Return',
            self::Other => 'Other',
        };
    }

    public function requiresSupplier(): bool
    {
        return $this === self::PurchaseOrder;
    }

    public function requiresReference(): bool
    {
        return in_array($this, [self::PurchaseOrder, self::EmergencyPurchase], true);
    }

    /**
     * @return array<int, self>
     */
    public static function stockInSources(): array
    {
        return [
            self::ManualExternal,
            self::PurchaseOrder,
            self::EmergencyPurchase,
            self::Donation,
            self::OpeningBalance,
            self::Other,
        ];
    }
}
