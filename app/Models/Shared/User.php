<?php

namespace App\Models\Shared;

use App\Support\PermissionRegistry;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';

    protected $attributes = [
        'mobile' => '',
        'otp' => '0',
    ];

    protected $fillable = [
        'img_url',
        'name',
        'email',
        'mobile',
        'password',
        'otp',
        'otp_expires_at',
        'status',
        'role',
        'permissions',
    ];

    protected $hidden = [
        'password',
        'otp',
    ];

    protected function casts(): array
    {
        return [
            'otp_expires_at' => 'datetime',
            'permissions' => 'array',
        ];
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(UserActivityLog::class, 'user_id');
    }

    public function isAdmin(): bool
    {
        return PermissionRegistry::isAdmin($this);
    }

    public function isSuperAdmin(): bool
    {
        return PermissionRegistry::isSuperAdmin($this);
    }

    public function canAccess(string $permission): bool
    {
        return PermissionRegistry::allows($this, $permission);
    }

    public function canViewProfit(): bool
    {
        return PermissionRegistry::canViewProfit($this);
    }

    public function avatarUrl(): string
    {
        if ($this->img_url && file_exists(public_path($this->img_url))) {
            return asset($this->img_url);
        }

        return asset('backend/assets/img/profile-img.png');
    }

    public function isApproved(): bool
    {
        return strtolower(trim((string) $this->status)) === 'approved';
    }
}
