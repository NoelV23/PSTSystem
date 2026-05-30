<?php

namespace App\Support;

use App\Models\UserLog;
use Illuminate\Support\Str;
use Throwable;

class UserActivity
{
    /**
     * Persist an audit row. Safe to call from auth flows; failures are swallowed so login never breaks.
     */
    public static function record(?int $userId, string $action, ?string $description = null, ?array $properties = null): void
    {
        try {
            $req = request();
            UserLog::create([
                'user_id' => $userId,
                'action' => Str::limit($action, 128, ''),
                'description' => $description,
                'ip_address' => $req?->ip(),
                'user_agent' => Str::limit((string) ($req?->userAgent() ?? ''), 2000, ''),
                'properties' => $properties,
            ]);
        } catch (Throwable $e) {
            \Log::warning('UserActivity::record failed: '.$e->getMessage());
        }
    }
}
