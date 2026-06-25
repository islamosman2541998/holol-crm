<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_type',
        'subject_id',
        'causer_id',
        'event',
        'title',
        'description',
        'old_values',
        'new_values',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
        ];
    }

    public function subject()
    {
        return $this->morphTo();
    }

    public function causer()
    {
        return $this->belongsTo(User::class, 'causer_id');
    }

    public function getEventIconAttribute(): string
    {
        return match ($this->event) {
            'created' => 'bi-plus-circle',
            'assigned' => 'bi-person-check',
            'status_changed' => 'bi-arrow-repeat',
            'updated' => 'bi-pencil-square',
            'followup_created' => 'bi-chat-dots',
            'followup_done' => 'bi-check2-circle',
            'followup_deleted' => 'bi-trash',
            'converted' => 'bi-person-check-fill',
            'payment_created' => 'bi-wallet2',
            'payment_deleted' => 'bi-wallet',
            'deleted' => 'bi-trash',
            default => 'bi-clock-history',
        };
    }
}