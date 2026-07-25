<?php

namespace App\Policies;

use App\Models\PurchaseRequest;
use App\Models\User;

class PurchaseRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('Student');
    }

    public function view(User $user, PurchaseRequest $purchaseRequest): bool
    {
        return $purchaseRequest->user_id === $user->id
            || $user->hasAnyRole(['Administrator', 'Accounting', 'Supply Personnel']);
    }

    public function checkout(User $user): bool
    {
        return $user->hasRole('Student');
    }

    public function uploadReceipt(User $user, PurchaseRequest $purchaseRequest): bool
    {
        return $purchaseRequest->user_id === $user->id
            && in_array($purchaseRequest->status, ['payment_submitted', 'pending'], true);
    }

    public function downloadPaymentSlip(User $user, PurchaseRequest $purchaseRequest): bool
    {
        return $purchaseRequest->user_id === $user->id;
    }
}
