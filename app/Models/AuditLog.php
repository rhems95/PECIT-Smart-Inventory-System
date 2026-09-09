<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'model_type',
        'model_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function auditable(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'model_type', 'model_id');
    }

    public function actionLabel(): string
    {
        return match ($this->action) {
            'auth.login' => 'Signed in',
            'auth.login_failed' => 'Sign-in failed',
            'auth.logout' => 'Signed out',
            'inventory.created' => 'Created inventory item',
            'inventory.updated' => 'Updated inventory item',
            'inventory.stock_in' => 'Stocked in inventory',
            'inventory.adjusted' => 'Adjusted inventory quantity',
            'user.created' => 'Created user',
            'user.updated' => 'Updated user',
            'student.created' => 'Created student account',
            'student.updated' => 'Updated student account',
            'category.created' => 'Created category',
            'category.updated' => 'Updated category',
            'department.created' => 'Created department',
            'department.updated' => 'Updated department',
            'department.deleted' => 'Deleted department',
            'announcement.created' => 'Published announcement',
            'profile.updated' => 'Updated profile',
            'supply_request.created' => 'Submitted supply request',
            'supply_request.cancelled' => 'Cancelled supply request',
            'supply_request.accounting_review' => 'Sent request to admin review',
            'supply_request.approved' => 'Approved supply request',
            'supply_request.rejected' => 'Rejected supply request',
            'supply_request.released' => 'Released supply request',
            'purchase.created' => 'Placed student purchase',
            'purchase.cancelled' => 'Cancelled student purchase',
            'purchase.payment_verified' => 'Verified student payment',
            'purchase.released' => 'Released student purchase',
            default => str_replace(['.', '_'], [' — ', ' '], $this->action),
        };
    }

    public function recordLabel(): string
    {
        $record = $this->auditable;

        if ($record instanceof Inventory) {
            return trim(($record->item_code ? $record->item_code.' — ' : '').($record->item_name ?? ''));
        }

        if ($record instanceof User) {
            return $record->name.($record->employee_id ? ' ('.$record->employee_id.')' : '');
        }

        if ($record instanceof SupplyRequest) {
            return $record->request_number ?? 'Request #'.$record->id;
        }

        if ($record instanceof PurchaseRequest) {
            return $record->purchase_number ?? 'Purchase #'.$record->id;
        }

        if ($record instanceof Category) {
            return $record->name;
        }

        if ($record instanceof Department) {
            return $record->name.($record->code ? ' ('.$record->code.')' : '');
        }

        if ($record instanceof Announcement) {
            return $record->title;
        }

        if ($this->model_id) {
            return class_basename((string) $this->model_type).' #'.$this->model_id;
        }

        return '—';
    }

    public function detailsSummary(): string
    {
        $values = $this->new_values ?: $this->old_values;

        if (! is_array($values) || $values === []) {
            return '—';
        }

        $skip = ['password', 'remember_token', 'email_verified_at'];
        $parts = [];

        foreach ($values as $key => $value) {
            if (in_array($key, $skip, true) || is_array($value)) {
                continue;
            }

            if ($value === null || $value === '') {
                continue;
            }

            $parts[] = str_replace('_', ' ', (string) $key).': '.$value;

            if (count($parts) >= 4) {
                break;
            }
        }

        return $parts ? implode(' · ', $parts) : '—';
    }
}
