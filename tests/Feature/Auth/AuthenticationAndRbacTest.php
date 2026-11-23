<?php

namespace Tests\Feature\Auth;

use App\Livewire\Shared\Auth\LoginPage;
use App\Models\Shared\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class AuthenticationAndRbacTest extends TestCase
{
    use DatabaseTransactions;

    public function test_guest_is_redirected_to_login_page(): void
    {
        $this->get('/admin-dashboard')->assertRedirect('/admin-login-page');
        $this->get('/battery/dashboard')->assertRedirect('/admin-login-page');
        $this->get('/admin-login-page')->assertOk()->assertSee('MARSS');
    }

    public function test_pending_user_cannot_authenticate(): void
    {
        $pending = User::create([
            'name' => 'Pending Staff',
            'email' => 'pending.test@marss.local',
            'password' => Hash::make('secret123'),
            'role' => 'staff',
            'status' => 'pending',
        ]);

        Livewire::test(LoginPage::class)
            ->set('email', $pending->email)
            ->set('password', 'secret123')
            ->call('authenticate')
            ->assertSet('errorMessage', 'Your account is currently pending approval or inactive. Contact an administrator.');

        $this->assertGuest();
    }

    public function test_cashier_login_redirects_to_pos_and_enforces_server_side_rbac(): void
    {
        $cashier = User::create([
            'name' => 'Counter Cashier',
            'email' => 'cashier.test@marss.local',
            'password' => Hash::make('secret123'),
            'role' => 'cashier',
            'status' => 'approved',
        ]);

        Livewire::test(LoginPage::class)
            ->set('email', $cashier->email)
            ->set('password', 'secret123')
            ->call('authenticate')
            ->assertRedirect(route('retail.pos'));

        // Allowed for cashier
        $this->actingAs($cashier)->get(route('retail.pos'))->assertOk();
        $this->actingAs($cashier)->get(route('retail.invoices'))->assertOk();
        $this->actingAs($cashier)->get(route('settings.profile'))->assertOk();

        // Denied server-side for cashier (fixes legacy client-only RBAC vulnerability)
        $this->actingAs($cashier)->get(route('settings.users'))->assertForbidden();
        $this->actingAs($cashier)->get(route('settings.activity-log'))->assertForbidden();
        $this->actingAs($cashier)->get(route('retail.purchases'))->assertForbidden();
        $this->actingAs($cashier)->get(route('battery.purchases'))->assertForbidden();
        $this->actingAs($cashier)->get(route('retail.reports.sales'))->assertForbidden();
        $this->actingAs($cashier)->get(route('battery.reports.sales'))->assertForbidden();
    }

    public function test_manager_cannot_access_admin_activity_log_or_profit_metrics(): void
    {
        $manager = User::create([
            'name' => 'Branch Manager',
            'email' => 'manager.test@marss.local',
            'password' => Hash::make('secret123'),
            'role' => 'manager',
            'status' => 'approved',
        ]);

        $this->assertFalse($manager->canViewProfit());
        $this->actingAs($manager)->get(route('retail.dashboard'))->assertOk()->assertDontSee('Net Profit (Admin)');
        $this->actingAs($manager)->get(route('retail.purchases'))->assertOk();
        $this->actingAs($manager)->get(route('battery.dashboard'))->assertOk();
        $this->actingAs($manager)->get(route('settings.activity-log'))->assertForbidden();
        $this->actingAs($manager)->get(route('settings.users'))->assertForbidden();
    }

    public function test_admin_can_access_profit_metrics_and_logs_but_not_developer_only_user_management(): void
    {
        $admin = User::where('email', 'admin@gmail.com')->firstOrFail();

        $this->assertTrue($admin->canViewProfit());
        $this->actingAs($admin)->get(route('retail.dashboard'))->assertOk()->assertSee('Net Profit (Admin)');
        $this->actingAs($admin)->get(route('settings.profile'))->assertOk();
        $this->actingAs($admin)->get(route('settings.activity-log'))->assertOk();
        $this->actingAs($admin)->get(route('settings.users'))->assertForbidden();
    }

    public function test_hidden_developer_account_has_full_access_is_hidden_and_cannot_be_deleted(): void
    {
        $dev = User::where('email', User::DEVELOPER_EMAIL)->firstOrFail();

        $this->assertTrue($dev->isDeveloper());
        $this->assertTrue($dev->isAdmin());
        $this->assertTrue($dev->isSuperAdmin());
        $this->assertTrue($dev->canViewProfit());
        $this->assertFalse(User::visible()->where('id', $dev->id)->exists());

        $this->actingAs($dev)->get(route('settings.users'))->assertOk();
        Livewire::actingAs($dev)
            ->test(\App\Livewire\Shared\Settings\UserRoleManagementPage::class)
            ->assertSee(User::DEVELOPER_EMAIL)
            ->assertSee('SUPER ADMIN')
            ->assertSee('Admin')
            ->assertSee('Md Skil Ahmed')
            ->call('deleteUser', $dev->id);
        $this->assertTrue(User::where('id', $dev->id)->exists());
        $this->actingAs($dev)->get(route('settings.activity-log'))->assertOk();
        $this->actingAs($dev)->get(route('retail.dashboard'))->assertOk();
        $this->actingAs($dev)->get(route('battery.dashboard'))->assertOk();

        // Non-dev user can edit profile name/email, does NOT see Change Password, and gets 403 on updatePassword
        $admin = User::where('email', 'admin@gmail.com')->firstOrFail();
        Livewire::actingAs($admin)
            ->test(\App\Livewire\Shared\Settings\UserProfilePage::class)
            ->assertDontSee('Change Password')
            ->call('updatePassword')
            ->assertForbidden();

        $this->expectException(\RuntimeException::class);
        $dev->delete();
    }

    public function test_logout_invalidates_session(): void
    {
        $admin = User::where('email', 'admin@gmail.com')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
