<?php

namespace App\Support;

use App\Models\User;

class PsisMenu
{
    /**
     * @return array<int, array{label: string, route: string|null, icon: string, color: string, roles: array<int, string>}>
     */
    public static function itemsFor(User $user): array
    {
        $items = [
            ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'home', 'color' => '#F4B400', 'roles' => ['Administrator', 'Admission', 'Accounting', 'Supply Personnel', 'Faculty', 'Student']],
            ['label' => 'Inventory', 'route' => 'inventory.index', 'icon' => 'box', 'color' => '#38BDF8', 'roles' => ['Administrator', 'Admission', 'Accounting', 'Supply Personnel', 'Faculty']],
            ['label' => 'My Requests', 'route' => 'requests.index', 'icon' => 'clipboard', 'color' => '#A78BFA', 'roles' => ['Faculty']],
            ['label' => 'New Request', 'route' => 'requests.create', 'icon' => 'plus', 'color' => '#34D399', 'roles' => ['Faculty']],
            ['label' => 'Uniform Shop', 'route' => 'shop.index', 'icon' => 'cart', 'color' => '#FB923C', 'roles' => ['Student']],
            ['label' => 'My Purchases', 'route' => 'purchases.index', 'icon' => 'receipt', 'color' => '#F472B6', 'roles' => ['Student']],
            ['label' => 'Review Requests', 'route' => 'accounting.requests', 'icon' => 'calculator', 'color' => '#22D3EE', 'roles' => ['Accounting']],
            ['label' => 'Verify Payments', 'route' => 'accounting.payments', 'icon' => 'credit-card', 'color' => '#4ADE80', 'roles' => ['Accounting']],
            ['label' => 'Approve Requests', 'route' => 'admin.requests', 'icon' => 'check', 'color' => '#FBBF24', 'roles' => ['Administrator', 'Admission']],
            ['label' => 'Users', 'route' => 'admin.users.index', 'icon' => 'users', 'color' => '#60A5FA', 'roles' => ['Administrator', 'Supply Personnel']],
            ['label' => 'Categories', 'route' => 'admin.categories.index', 'icon' => 'tag', 'color' => '#C084FC', 'roles' => ['Administrator', 'Supply Personnel']],
            ['label' => 'Departments', 'route' => 'admin.departments.index', 'icon' => 'building', 'color' => '#94A3B8', 'roles' => ['Administrator', 'Supply Personnel']],
            ['label' => 'Suppliers', 'route' => 'admin.suppliers.index', 'icon' => 'truck', 'color' => '#FB923C', 'roles' => ['Administrator', 'Supply Personnel']],
            ['label' => 'Units', 'route' => 'admin.units.index', 'icon' => 'tag', 'color' => '#2DD4BF', 'roles' => ['Administrator', 'Supply Personnel']],
            ['label' => 'Announcements', 'route' => 'admin.announcements.index', 'icon' => 'megaphone', 'color' => '#F87171', 'roles' => ['Administrator', 'Supply Personnel']],
            ['label' => 'Stock Operations', 'route' => 'supply.stock.index', 'icon' => 'warehouse', 'color' => '#FBBF24', 'roles' => ['Supply Personnel', 'Administrator']],
            ['label' => 'Purchase History', 'route' => 'supply.purchase-history', 'icon' => 'clipboard', 'color' => '#38BDF8', 'roles' => ['Supply Personnel', 'Administrator']],
            ['label' => 'Release Items', 'route' => 'supply.releases', 'icon' => 'package', 'color' => '#FB923C', 'roles' => ['Supply Personnel', 'Administrator']],
            ['label' => 'Student Purchases', 'route' => 'supply.purchases', 'icon' => 'receipt', 'color' => '#F472B6', 'roles' => ['Supply Personnel', 'Administrator']],
            ['label' => 'Students', 'route' => 'supply.students.index', 'icon' => 'academic-cap', 'color' => '#2DD4BF', 'roles' => ['Supply Personnel', 'Administrator']],
            ['label' => 'Restock Tips', 'route' => 'ai.restock', 'icon' => 'trending-up', 'color' => '#34D399', 'roles' => ['Supply Personnel', 'Administrator']],
            ['label' => 'Reports', 'route' => 'reports.index', 'icon' => 'chart', 'color' => '#38BDF8', 'roles' => ['Administrator', 'Accounting', 'Supply Personnel']],
            ['label' => 'AI Assistant', 'route' => 'ai.chat', 'icon' => 'sparkles', 'color' => '#F4B400', 'roles' => ['Administrator', 'Admission', 'Accounting', 'Supply Personnel', 'Faculty', 'Student']],
            ['label' => 'Audit Logs', 'route' => 'admin.audit-logs', 'icon' => 'shield', 'color' => '#818CF8', 'roles' => ['Administrator', 'Supply Personnel']],
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
