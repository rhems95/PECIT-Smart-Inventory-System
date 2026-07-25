<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'inventory.view',
            'inventory.manage',
            'requests.submit',
            'requests.review',
            'requests.approve',
            'requests.release',
            'purchases.checkout',
            'purchases.verify',
            'users.manage',
            'reports.view',
            'audit.view',
            'announcements.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $map = [
            'Administrator' => $permissions,
            'Accounting' => ['inventory.view', 'requests.review', 'purchases.verify', 'reports.view'],
            'Supply Personnel' => ['inventory.view', 'inventory.manage', 'requests.release', 'reports.view'],
            'Faculty' => ['inventory.view', 'requests.submit'],
            'Student' => ['inventory.view', 'purchases.checkout'],
        ];

        foreach ($map as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $role->syncPermissions($rolePermissions);
        }
    }
}
