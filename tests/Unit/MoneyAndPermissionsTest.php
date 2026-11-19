<?php

namespace Tests\Unit;

use App\Models\Shared\User;
use App\Support\Money;
use App\Support\PermissionRegistry;
use PHPUnit\Framework\TestCase;

class MoneyAndPermissionsTest extends TestCase
{
    public function test_bcmath_decimal_arithmetic_avoids_float_drift(): void
    {
        $this->assertSame('0.30', Money::add('0.1', '0.2'));
        $this->assertSame('19.99', Money::sub('20.00', '0.01'));
        $this->assertSame('1560.00', Money::mul('78.00', 20));
        $this->assertSame('12.50', Money::percent('250.00', '5'));
        $this->assertSame('33.33', Money::div('100', '3'));
        $this->assertSame('0.00', Money::maxZero('-50.25'));
    }

    public function test_bd_currency_formatting_and_taka_words(): void
    {
        $this->assertSame('৳ 24,800.00', Money::formatBd('24800'));
        $this->assertSame('৳ 1,24,800.50', Money::formatBd('124800.5'));
        $this->assertSame('৳ 31,85,231.76', Money::formatBd('3185231.76'));
        $this->assertSame('One Lakh Twenty Four Thousand Eight Hundred Taka Only', Money::toWords('124800'));
        $this->assertSame('One Hundred Taka and Fifty Poisha Only', Money::toWords('100.50'));
    }

    public function test_role_permissions_and_admin_profit_isolation(): void
    {
        $admin = new User(['role' => 'admin', 'status' => 'approved']);
        $manager = new User(['role' => 'manager', 'status' => 'approved']);
        $cashier = new User(['role' => 'cashier', 'status' => 'approved']);
        $accountant = new User(['role' => 'accountant', 'status' => 'approved']);

        $this->assertTrue(PermissionRegistry::isAdmin($admin));
        $this->assertTrue(PermissionRegistry::canViewProfit($admin));
        $this->assertTrue(PermissionRegistry::allows($admin, 'user'));

        $this->assertFalse(PermissionRegistry::canViewProfit($manager));
        $this->assertTrue(PermissionRegistry::allows($manager, 'product'));
        $this->assertFalse(PermissionRegistry::allows($manager, 'user'));

        $this->assertTrue(PermissionRegistry::allows($cashier, 'pos_terminal'));
        $this->assertTrue(PermissionRegistry::allows($cashier, 'invoice_list'));
        $this->assertFalse(PermissionRegistry::allows($cashier, 'purchase'));
        $this->assertFalse(PermissionRegistry::allows($cashier, 'report'));

        $this->assertTrue(PermissionRegistry::allows($accountant, 'report'));
        $this->assertTrue(PermissionRegistry::allows($accountant, 'expense'));
        $this->assertFalse(PermissionRegistry::allows($accountant, 'pos_terminal'));
    }
}
