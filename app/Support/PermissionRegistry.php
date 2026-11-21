<?php

namespace App\Support;

use App\Models\Shared\User;

/**
 * Authoritative RBAC & Granular Permission Registry preserving all 33 reference permission keys
 * and role defaults while enforcing them server-side (fixing the legacy client-only gap).
 */
final class PermissionRegistry
{
    public const PARENT_KEYS = [
        'dashboard',
        'pos',
        'pos_terminal',
        'invoice_list',
        'product',
        'purchase',
        'customer',
        'expense',
        'return',
        'report',
        'user',
    ];

    public const GROUPS = [
        'dashboard' => [
            'label' => 'Dashboard',
            'children' => [],
        ],
        'pos' => [
            'label' => 'POS & Invoices',
            'children' => [
                'pos_terminal' => 'POS Terminal',
                'invoice_list' => 'Invoice List',
            ],
        ],
        'product' => [
            'label' => 'Product Management',
            'children' => [
                'product_list' => 'Product List',
                'brand_list' => 'Brand List',
                'category_list' => 'Category List',
                'barcode_print' => 'Barcode Print',
                'stock_overview' => 'Stock Overview',
            ],
        ],
        'purchase' => [
            'label' => 'Purchase & Suppliers',
            'children' => [
                'purchase_entry' => 'Purchase Entry',
                'supplier_list' => 'Supplier List',
                'supplier_due' => 'Supplier Due',
                'due_payment' => 'Supplier Due Payment',
            ],
        ],
        'customer' => [
            'label' => 'Customer Management',
            'children' => [
                'customer_list' => 'Customer List',
                'customer_due' => 'Customer Due',
                'customer_due_collection' => 'Customer Due Collection',
            ],
        ],
        'expense' => [
            'label' => 'Expense & Opening Balance',
            'children' => [
                'expense_list' => 'Expense List',
                'expense_category' => 'Expense Category',
            ],
        ],
        'return' => [
            'label' => 'Return Management',
            'children' => [
                'return_list' => 'Return List',
            ],
        ],
        'report' => [
            'label' => 'Reports',
            'children' => [
                'daily_ledger' => 'Daily Ledger',
                'sales_report' => 'Sales Report',
                'income_expense_report' => 'Income & Expense Report',
                'daily_receipt' => 'Daily Receipt & Payment',
                'personal_trans' => 'Personal Transaction Report',
                'low_stock' => 'Low Stock Report',
            ],
        ],
        'user' => [
            'label' => 'Settings & Users',
            'children' => [
                'user_role' => 'User List & Roles',
            ],
        ],
    ];

    public static function isDeveloper(?User $user): bool
    {
        return $user?->isDeveloper() ?? false;
    }

    public static function isAdmin(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if (self::isDeveloper($user)) {
            return true;
        }

        $role = strtolower(trim((string) $user->role));

        return in_array($role, ['admin', 'super_admin', 'super admin'], true);
    }

    public static function isSuperAdmin(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if (self::isDeveloper($user)) {
            return true;
        }

        $role = strtolower(trim((string) $user->role));

        return in_array($role, ['super_admin', 'super admin'], true);
    }

    public static function canViewProfit(?User $user): bool
    {
        return self::isAdmin($user);
    }

    public static function canViewActivityLog(?User $user): bool
    {
        return self::isDeveloper($user);
    }

    public static function canManageUsers(?User $user): bool
    {
        return self::isDeveloper($user);
    }

    /**
     * Resolve effective permissions for a user, combining explicit JSON permissions with role presets.
     *
     * @return array<string, bool>
     */
    public static function effectiveFor(?User $user): array
    {
        if (! $user) {
            return array_fill_keys(self::allKeys(), false);
        }

        if (self::isAdmin($user)) {
            return array_fill_keys(self::allKeys(), true);
        }

        $role = strtolower(trim((string) $user->role));
        $stored = $user->permissions;
        if (is_string($stored)) {
            $stored = json_decode($stored, true);
        }

        $defaults = self::roleDefaults($role);

        if (is_array($stored) && count($stored) > 0) {
            $resolved = [];
            $posEnabled = array_key_exists('pos', $stored) ? (bool) $stored['pos'] : true;
            $resolved['dashboard'] = array_key_exists('dashboard', $stored) ? (bool) $stored['dashboard'] : true;
            $resolved['pos'] = $posEnabled;
            $resolved['pos_terminal'] = $posEnabled && (array_key_exists('pos_terminal', $stored) ? (bool) $stored['pos_terminal'] : true);
            $resolved['invoice_list'] = $posEnabled && (array_key_exists('invoice_list', $stored) ? (bool) $stored['invoice_list'] : true);
            $resolved['product'] = (bool) ($stored['product'] ?? false);
            $resolved['purchase'] = (bool) ($stored['purchase'] ?? false);
            $resolved['customer'] = (bool) ($stored['customer'] ?? false);
            $resolved['expense'] = (bool) ($stored['expense'] ?? false);
            $resolved['return'] = array_key_exists('return', $stored) ? (bool) $stored['return'] : $posEnabled;
            $resolved['report'] = (bool) ($stored['report'] ?? false);
            $resolved['user'] = (bool) ($stored['user'] ?? false);

            foreach (self::GROUPS as $parent => $meta) {
                foreach (array_keys($meta['children']) as $child) {
                    if ($child === 'pos_terminal' || $child === 'invoice_list') {
                        continue;
                    }
                    $resolved[$child] = $resolved[$parent] && (array_key_exists($child, $stored) ? (bool) $stored[$child] : true);
                }
            }

            return $resolved;
        }

        return $defaults;
    }

    public static function allows(?User $user, string $permission): bool
    {
        if (! $user) {
            return false;
        }

        if (self::isAdmin($user)) {
            return true;
        }

        $effective = self::effectiveFor($user);

        return (bool) ($effective[$permission] ?? false);
    }

    /**
     * @return array<string, bool>
     */
    public static function roleDefaults(string $role): array
    {
        $role = strtolower(trim($role));

        $parents = match ($role) {
            'admin', 'super_admin', 'super admin' => array_fill_keys(self::PARENT_KEYS, true),
            'manager' => [
                'dashboard' => true,
                'pos' => true,
                'pos_terminal' => true,
                'invoice_list' => true,
                'product' => true,
                'purchase' => true,
                'customer' => true,
                'expense' => true,
                'return' => true,
                'report' => true,
                'user' => false,
            ],
            'accountant' => [
                'dashboard' => true,
                'pos' => false,
                'pos_terminal' => false,
                'invoice_list' => false,
                'product' => false,
                'purchase' => false,
                'customer' => true,
                'expense' => true,
                'return' => false,
                'report' => true,
                'user' => false,
            ],
            default => [ // staff, cashier, users
                'dashboard' => true,
                'pos' => true,
                'pos_terminal' => true,
                'invoice_list' => true,
                'product' => false,
                'purchase' => false,
                'customer' => false,
                'expense' => false,
                'return' => false,
                'report' => false,
                'user' => false,
            ],
        };

        $full = $parents;
        foreach (self::GROUPS as $parent => $meta) {
            foreach (array_keys($meta['children']) as $child) {
                if (! isset($full[$child])) {
                    $full[$child] = (bool) ($parents[$parent] ?? false);
                }
            }
        }

        return $full;
    }

    /**
     * @return list<string>
     */
    public static function allKeys(): array
    {
        $keys = self::PARENT_KEYS;
        foreach (self::GROUPS as $meta) {
            foreach (array_keys($meta['children']) as $child) {
                $keys[] = $child;
            }
        }

        return array_values(array_unique($keys));
    }
}
