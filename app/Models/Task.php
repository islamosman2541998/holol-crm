<?php

namespace App\Models;

use App\Traits\HasActivityLogs;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Task extends Model
{
    use HasFactory, SoftDeletes, HasActivityLogs;

    protected $fillable = [
        'project_id',
        'created_by',
        'client_id',
        'lead_id',
        'title',
        'description',
        'priority',
        'status',
        'start_at',
        'due_at',
        'completed_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'start_at' => 'datetime',
            'due_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function assignedMembers()
    {
        return $this->belongsToMany(Member::class, 'task_member')->withTimestamps();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

public function project()
{
    return $this->belongsTo(Project::class);
}
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'new' => 'جديدة',
            'in_progress' => 'قيد التنفيذ',
            'review' => 'في المراجعة',
            'completed' => 'مكتملة',
            'cancelled' => 'ملغية',
            default => 'غير معروف',
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'new' => 'bg-info',
            'in_progress' => 'bg-primary',
            'review' => 'bg-warning',
            'completed' => 'bg-success',
            'cancelled' => 'bg-danger',
            default => 'bg-dark',
        };
    }

    public function getPriorityLabelAttribute(): string
    {
        return match ($this->priority) {
            'low' => 'منخفضة',
            'medium' => 'متوسطة',
            'high' => 'عالية',
            'urgent' => 'عاجلة',
            default => 'غير معروف',
        };
    }

    public function getPriorityBadgeClassAttribute(): string
    {
        return match ($this->priority) {
            'low' => 'bg-secondary',
            'medium' => 'bg-info',
            'high' => 'bg-warning',
            'urgent' => 'bg-danger',
            default => 'bg-dark',
        };
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->due_at &&
            $this->due_at->isPast() &&
            ! in_array($this->status, ['completed', 'cancelled']);
    }

    public function scopeDueToday($query)
    {
        return $query->whereBetween('due_at', [now(), today()->endOfDay()])
            ->whereNotIn('status', ['completed', 'cancelled']);
    }

    public function scopeOverdue($query)
    {
        return $query->whereNotNull('due_at')
            ->where('due_at', '<', now())
            ->whereNotIn('status', ['completed', 'cancelled']);
    }

    public function scopeForMember($query, int $memberId)
    {
        return $query->whereHas('assignedMembers', function ($query) use ($memberId) {
            $query->where('members.id', $memberId);
        });
    }
    public function comments()
{
    return $this->hasMany(TaskComment::class)->latest();
}

public function attachments()
{
    return $this->hasMany(TaskAttachment::class)->latest();
}
}
