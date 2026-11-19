<?php

use App\Http\Controllers\AuthController;
use App\Livewire\Shared\Auth\LoginPage;
use App\Livewire\Shared\Location\LocationManagerPage;
use App\Livewire\Shared\Settings\UserActivityLogPage;
use App\Livewire\Shared\Settings\UserProfilePage;
use App\Livewire\Shared\Settings\UserRoleManagementPage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (Auth::check()) {
        $role = strtolower(trim((string) Auth::user()->role));

        return redirect()->route($role === 'cashier' ? 'retail.pos' : 'retail.dashboard');
    }

    return redirect()->route('login');
})->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/admin-login-page', LoginPage::class)->name('login');
    Route::redirect('/nexus-login-page', '/admin-login-page');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/naxus-pos-logout', [AuthController::class, 'logout'])->name('logout.legacy');

    // User Profile (accessible to all authenticated users)
    Route::get('/admin-dashboard-user-profile', UserProfilePage::class)->name('settings.profile');

    // User & Role Management (permission-gated)
    Route::get('/admin-dashboard-user-role', UserRoleManagementPage::class)
        ->middleware('perm:user')
        ->name('settings.users');

    // Activity Audit Log (Admin / Super Admin ONLY)
    Route::get('/admin-dashboard-user-activity-log', UserActivityLogPage::class)
        ->middleware('admin')
        ->name('settings.activity-log');

    // Geographic Masters (preserving exact legacy URL spellings)
    Route::middleware('perm:customer')->group(function () {
        Route::get('/admin-dashboard-dsistrict', LocationManagerPage::class)->name('settings.districts');
        Route::get('/admin-dashboard-upazila', LocationManagerPage::class)->name('settings.upazilas');
        Route::get('/admin-dashboard-thana', LocationManagerPage::class)->name('settings.thanas');
        Route::get('/admin-dashboard-location', LocationManagerPage::class)->name('settings.locations');
    });
});
