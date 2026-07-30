<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AuditLogService
{
    public function record(
        string $action,
        ?Model $subject = null,
        array|null $oldValue = null,
        array|null $newValue = null,
        ?Request $request = null,
        ?int $actorId = null,
        ?int $userId = null,
    ): ActivityLog {
        return ActivityLog::create([
            'admin_id' => $actorId ?? $request?->user()?->id,
            'user_id' => $userId ?? $subject?->getAttribute('id'),
            'action' => $action,
            'subject_type' => $subject ? $subject::class : null,
            'subject_id' => $subject?->getAttribute('id'),
            'old_value' => $oldValue,
            'new_value' => $newValue,
        ]);
    }
}
