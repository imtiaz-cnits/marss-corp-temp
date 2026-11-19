<?php

namespace App\Services\Shared;

use App\Models\Shared\User;
use App\Models\Shared\UserActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ActivityLogger
{
    public static function log(
        string $action,
        string $module,
        string $description,
        mixed $details = null,
        ?User $user = null,
    ): ?UserActivityLog {
        try {
            $actor = $user ?? Auth::user();

            return UserActivityLog::create([
                'user_id' => $actor?->id,
                'user_name' => $actor?->name ?? 'System',
                'user_role' => $actor?->role ?? 'system',
                'user_email' => $actor?->email,
                'action' => $action,
                'module' => $module,
                'description' => $description,
                'details' => is_array($details) || is_object($details) ? $details : ($details !== null ? ['note' => $details] : null),
                'ip_address' => request()?->ip(),
                'user_agent' => request()?->userAgent(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('ActivityLogger failed: '.$e->getMessage(), [
                'action' => $action,
                'module' => $module,
            ]);

            return null;
        }
    }
}
