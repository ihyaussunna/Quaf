<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLogger
{
    public static function log(string $action, ?object $model = null, ?array $oldValues = null, ?array $newValues = null): void
    {
        try {
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => $action,
                'model_type' => $model ? get_class($model) : null,
                'model_id' => $model?->id ?? null,
                'old_values' => $oldValues,
                'new_values' => $newValues,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
            ]);
        } catch (\Throwable $e) {
            // Silently ignore logging failures to not disrupt main operation
        }
    }
}
