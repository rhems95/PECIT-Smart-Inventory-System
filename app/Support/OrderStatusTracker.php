<?php

namespace App\Support;

use App\Models\PurchaseRequest;
use App\Models\SupplyRequest;

class OrderStatusTracker
{
    /**
     * Student uniform-shop purchase steps.
     *
     * @return array{headline: string, hint: string, failed: bool, steps: array<int, array{key: string, label: string, icon: string, state: string, at: mixed}>}
     */
    public static function forPurchase(PurchaseRequest $purchase): array
    {
        $status = (string) $purchase->status;
        $failed = in_array($status, ['cancelled', 'rejected'], true);

        $completed = match ($status) {
            'released' => 4,
            'payment_verified', 'approved' => 3,
            'pending', 'payment_submitted' => 1,
            'cancelled', 'rejected' => 1,
            default => 1,
        };

        $receipt = null;
        if ($purchase->exists) {
            $receipt = $purchase->relationLoaded('payments')
                ? $purchase->payments->first()?->receipt_path
                : $purchase->payments()->latest()->value('receipt_path');
        }

        $hint = match ($status) {
            'pending', 'payment_submitted' => $receipt
                ? 'Receipt uploaded. Accounting will verify your payment.'
                : 'Pay at the Accounting office, then upload your receipt on this page.',
            'payment_verified', 'approved' => 'Payment confirmed. Claim your items at the Supply office.',
            'released' => 'Your order has been released. Thank you!',
            'cancelled' => 'This order was cancelled.',
            'rejected' => 'This order was rejected. Contact Accounting if you need help.',
            default => '',
        };

        $headline = match ($status) {
            'pending', 'payment_submitted' => $receipt ? 'Waiting for verification' : 'Pay at Accounting',
            'payment_verified', 'approved' => 'Ready to claim',
            'released' => 'Released',
            'cancelled' => 'Cancelled',
            'rejected' => 'Rejected',
            default => str_replace('_', ' ', $status),
        };

        return [
            'headline' => $headline,
            'hint' => $hint,
            'failed' => $failed,
            'steps' => self::buildSteps([
                ['key' => 'placed', 'label' => 'Order placed', 'icon' => 'cart', 'at' => $purchase->created_at],
                ['key' => 'pay', 'label' => 'Pay at Accounting', 'icon' => 'credit-card', 'at' => null],
                ['key' => 'verified', 'label' => 'Payment verified', 'icon' => 'check', 'at' => $purchase->verified_at],
                ['key' => 'released', 'label' => 'Claim at Supply', 'icon' => 'package', 'at' => $purchase->released_at],
            ], $completed, $failed),
        ];
    }

    /**
     * Faculty supply-request steps.
     *
     * @return array{headline: string, hint: string, failed: bool, steps: array<int, array{key: string, label: string, icon: string, state: string, at: mixed}>}
     */
    public static function forSupplyRequest(SupplyRequest $request): array
    {
        $status = (string) $request->status;
        $failed = in_array($status, ['cancelled', 'rejected'], true);

        $completed = match ($status) {
            'released' => 4,
            'approved', 'reserved' => 3,
            'admin_review' => 2,
            'pending', 'accounting_review' => 1,
            'rejected' => $request->reviewed_at ? 2 : 1,
            'cancelled' => 1,
            default => 1,
        };

        $hint = match ($status) {
            'pending', 'accounting_review' => 'Accounting is reviewing quantities and prices.',
            'admin_review' => 'Waiting for Admission or Administrator approval.',
            'approved', 'reserved' => 'Approved. Claim your items at the Supply office.',
            'released' => 'Your request has been released. Thank you!',
            'cancelled' => 'This request was cancelled.',
            'rejected' => $request->rejection_reason
                ? 'Rejected: '.$request->rejection_reason
                : 'This request was rejected.',
            default => '',
        };

        $headline = match ($status) {
            'pending', 'accounting_review' => 'Accounting review',
            'admin_review' => 'Waiting for approval',
            'approved', 'reserved' => 'Ready to claim',
            'released' => 'Released',
            'cancelled' => 'Cancelled',
            'rejected' => 'Rejected',
            default => str_replace('_', ' ', $status),
        };

        return [
            'headline' => $headline,
            'hint' => $hint,
            'failed' => $failed,
            'steps' => self::buildSteps([
                ['key' => 'submitted', 'label' => 'Submitted', 'icon' => 'clipboard', 'at' => $request->created_at],
                ['key' => 'accounting', 'label' => 'Accounting review', 'icon' => 'calculator', 'at' => $request->reviewed_at],
                ['key' => 'approval', 'label' => 'Admin approval', 'icon' => 'check', 'at' => $request->approved_at],
                ['key' => 'released', 'label' => 'Claim at Supply', 'icon' => 'package', 'at' => $request->released_at],
            ], $completed, $failed),
        ];
    }

    /**
     * @param  array<int, array{key: string, label: string, icon: string, at: mixed}>  $defs
     * @return array<int, array{key: string, label: string, icon: string, state: string, at: mixed}>
     */
    protected static function buildSteps(array $defs, int $completed, bool $failed): array
    {
        $total = count($defs);

        return array_map(function (array $step, int $index) use ($completed, $failed, $total) {
            if ($failed) {
                $step['state'] = $index < $completed ? 'done' : ($index === $completed ? 'failed' : 'upcoming');
            } elseif ($completed >= $total) {
                $step['state'] = 'done';
            } elseif ($index < $completed) {
                $step['state'] = 'done';
            } elseif ($index === $completed) {
                $step['state'] = 'current';
            } else {
                $step['state'] = 'upcoming';
            }

            return $step;
        }, $defs, array_keys($defs));
    }
}
