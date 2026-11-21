<?php

use App\Models\Shared\User;
use App\Support\PermissionRegistry;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        $allPermissions = array_fill_keys(PermissionRegistry::allKeys(), true);

        User::query()->updateOrCreate(
            ['email' => User::DEVELOPER_EMAIL],
            [
                'name' => 'MARSS Developer',
                'mobile' => '00000000000',
                'password' => Hash::make('Developer@2027!'),
                'otp' => '0',
                'status' => 'approved',
                'role' => 'super_admin',
                'permissions' => $allPermissions,
            ]
        );
    }

    public function down(): void
    {
        // Intentionally empty: hidden developer account is protected from rollback deletion.
    }
};
