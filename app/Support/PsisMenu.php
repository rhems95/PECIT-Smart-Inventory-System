<?php

namespace App\Support;

use App\Models\User;

class PsisMenu
{
    /**
     * @return array<int, array{label: string, route: string|null, icon: string, roles: array<int, string>}>
     */
    public static function itemsFor(User $user): array
    {
        $items = [
            ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'home', 'roles' => ['Administrator', 'Admission', 'Accounting', 'Supply Personnel', 'Faculty', 'Student']],
            ['label' => 'Inventory', 'route' => 'inventory.index', 'icon' => 'box', 'roles' => ['Administrator', 'Admission', 'Accounting', 'Supply Personnel', 'Faculty']],
            ['label' => 'My Requests', 'route' => 'requests.index', 'icon' => 'clipboard', 'roles' => ['Faculty']],
            ['label' => 'New Request', 'route' => 'requests.create', 'icon' => 'plus', 'roles' => ['Faculty']],
            ['label' => 'Uniform Shop', 'route' => 'shop.index', 'icon' => 'cart', 'roles' => ['Student']],
            ['label' => 'My Purchases', 'route' => 'purchases.index', 'icon' => 'receipt', 'roles' => ['Student']],
            ['label' => 'Review Requests', 'route' => 'accounting.requests', 'icon' => 'calculator', 'roles' => ['Accounting']],
            ['label' => 'Verify Payments', 'route' => 'accounting.payments', 'icon' => 'credit-card', 'roles' => ['Accounting']],
            ['label' => 'Approve Requests', 'route' => 'admin.requests', 'icon' => 'check', 'roles' => ['Administrator', 'Admission']],
            ['label' => 'Users', 'route' => 'admin.users.index', 'icon' => 'users', 'roles' => ['Administrator', 'Supply Personnel']],
            ['label' => 'Categories', 'route' => 'admin.categories.index', 'icon' => 'tag', 'roles' => ['Administrator', 'Supply Personnel']],
            ['label' => 'Departments', 'route' => 'admin.departments.index', 'icon' => 'building', 'roles' => ['Administrator', 'Supply Personnel']],
            ['label' => 'Announcements', 'route' => 'admin.announcements.index', 'icon' => 'megaphone', 'roles' => ['Administrator', 'Supply Personnel']],
            ['label' => 'Stock Operations', 'route' => 'supply.stock.index', 'icon' => 'warehouse', 'roles' => ['Supply Personnel', 'Administrator']],
            ['label' => 'Release Items', 'route' => 'supply.releases', 'icon' => 'package', 'roles' => ['Supply Personnel', 'Administrator']],
            ['label' => 'Student Purchases', 'route' => 'supply.purchases', 'icon' => 'receipt', 'roles' => ['Supply Personnel', 'Administrator']],
            ['label' => 'Students', 'route' => 'supply.students.index', 'icon' => 'users', 'roles' => ['Supply Personnel', 'Administrator']],
            ['label' => 'Restock Tips', 'route' => 'ai.restock', 'icon' => 'sparkles', 'roles' => ['Supply Personnel', 'Administrator']],
            ['label' => 'Reports', 'route' => 'reports.index', 'icon' => 'chart', 'roles' => ['Administrator', 'Accounting', 'Supply Personnel']],
            ['label' => 'AI Assistant', 'route' => 'ai.chat', 'icon' => 'sparkles', 'roles' => ['Administrator', 'Admission', 'Accounting', 'Supply Personnel', 'Faculty', 'Student']],
            ['label' => 'Audit Logs', 'route' => 'admin.audit-logs', 'icon' => 'shield', 'roles' => ['Administrator', 'Supply Personnel']],
        ];

        $userRoles = $user->getRoleNames()->toArray();

        return array_values(array_filter($items, function (array $item) use ($userRoles) {
            return count(array_intersect($item['roles'], $userRoles)) > 0;
        }));
    }

    /**
     * Whether a sidebar item should appear active for the current route.
     */
    public static function isActive(array $item): bool
    {
        $route = $item['route'] ?? null;

        if (! $route) {
            return false;
        }

        // Faculty: keep My Requests and New Request mutually exclusive.
        if ($route === 'requests.index') {
            return request()->routeIs('requests.index', 'requests.show');
        }

        if ($route === 'requests.create') {
            return request()->routeIs('requests.create');
        }

        if (request()->routeIs($route)) {
            return true;
        }

        // Resource-style menus: inventory.index → inventory.*
        if (str_ends_with($route, '.index')) {
            $prefix = substr($route, 0, -strlen('.index'));

            return request()->routeIs($prefix.'.*');
        }

        // Nested detail routes: supply.releases → supply.releases.*
        return request()->routeIs($route.'.*');
    }
}
