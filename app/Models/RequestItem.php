<?php

namespace App\Models;

use App\Enums\ReceivingInspectionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequestItem extends Model
{
    protected $fillable = [
        'request_id',
        'inventory_id',
        'quantity_requested',
        'quantity_approved',
        'quantity_released',
        'unit_price',
        'subtotal',
        'inspection_status',
        'inspection_notes',
        'inspected_by',
        'inspected_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity_requested' => 'decimal:4',
            'quantity_approved' => 'decimal:4',
            'quantity_released' => 'decimal:4',
            'unit_price' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'inspection_status' => ReceivingInspectionStatus::class,
            'inspected_at' => 'datetime',
        ];
    }

    public function supplyRequest(): BelongsTo
    {
        return $this->belongsTo(SupplyRequest::class, 'request_id');
    }

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspected_by');
    }

    public function inspectionLabel(): string
    {
        $status = $this->inspection_status;

        return $status instanceof ReceivingInspectionStatus
            ? $status->label()
            : ReceivingInspectionStatus::Pending->label();
    }
}
