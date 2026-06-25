<?php

namespace App\Traits;

use App\Models\ActivityLog;

trait HasActivityLogs
{
    public function activities()
    {
        return $this->morphMany(ActivityLog::class, 'subject')->latest();
    }

    public function logActivity(
        string $event,
        string $title,
        ?string $description = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): ActivityLog {
        return $this->activities()->create([
            'causer_id' => auth()->id(),
            'event' => $event,
            'title' => $title,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
        ]);
    }
}