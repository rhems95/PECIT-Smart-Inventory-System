<?php

namespace App\Policies;

use App\Models\SupplyRequest;
use App\Models\User;

class SupplyRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('Faculty');
    }

    public function view(User $user, SupplyRequest $supplyRequest): bool
    {
        return $supplyRequest->user_id === $user->id
            || $user->hasAnyRole(['Administrator', 'Admission', 'Accounting', 'Supply Personnel']);
    }

    public function create(User $user): bool
    {
        return $user->hasRole('Faculty');
    }

    public function cancel(User $user, SupplyRequest $supplyRequest): bool
    {
        return $supplyRequest->user_id === $user->id
            && in_array($supplyRequest->status, ['pending', 'accounting_review'], true);
    }
}
