<?php

namespace App\Policies;

use App\Models\Inventory;
use App\Models\User;

class InventoryPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Inventory $inventory): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['Administrator', 'Supply Personnel']);
    }

    public function update(User $user, Inventory $inventory): bool
    {
        return $user->hasAnyRole(['Administrator', 'Supply Personnel']);
    }
}
