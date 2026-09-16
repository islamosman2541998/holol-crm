<?php

namespace App\Models;

use App\Traits\HasActivityLogs;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Project extends Model
{
    use HasFactory, SoftDeletes, HasActivityLogs;

    protected $fillable = [
        'client_id',
        'service_id',
        'team_id',
        'manager_member_id',
        'created_by',
        'name',
        'code',
        'description',
        'priority',
        'status',
        'start_date',
        'due_date',
        'completed_date',
        'budget',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'due_date' => 'date',
            'completed_date' => 'date',
            'budget' => 'decimal:2',
        ];
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function manager()
    {
        return $this->belongsTo(Member::class, 'manager_member_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function openTasks()
    {
        return $this->hasMany(Task::class)
            ->whereNotIn('status', ['completed', 'cancelled']);
    }

    public function completedTasks()
    {
        return $this->hasMany(Task::class)->where('status', 'completed');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'new' => 'جديد',
            'planning' => 'مرحلة التخطيط',
            'in_progress' => 'قيد التنفيذ',
            'on_hold' => 'متوقف مؤقتًا',
            'completed' => 'مكتمل',
            'cancelled' => 'ملغي',
            default => 'غير معروف',
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'new' => 'bg-info',
            'planning' => 'bg-secondary',
            'in_progress' => 'bg-primary',
            'on_hold' => 'bg-warning',
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
        return $this->due_date &&
            $this->due_date->isPast() &&
            ! in_array($this->status, ['completed', 'cancelled']);
    }
    public function comments()
    {
        return $this->hasMany(ProjectComment::class)->latest();
    }

    public function attachments()
    {
        return $this->hasMany(ProjectAttachment::class)->latest();
    }
    public function milestones()
    {
        return $this->hasMany(ProjectMilestone::class)->orderBy('sort_order');
    }

    public function completedMilestones()
    {
        return $this->hasMany(ProjectMilestone::class)->where('status', 'completed');
    }
    public function getProgressPercentageAttribute(): int
    {
        $milestonesCount = array_key_exists('milestones_count', $this->attributes)
            ? (int) $this->attributes['milestones_count']
            : $this->milestones()->count();

        if ($milestonesCount > 0) {
            $completedMilestonesCount = array_key_exists('completed_milestones_count', $this->attributes)
                ? (int) $this->attributes['completed_milestones_count']
                : $this->completedMilestones()->count();

            return (int) round(($completedMilestonesCount / $milestonesCount) * 100);
        }

        $tasksCount = array_key_exists('tasks_count', $this->attributes)
            ? (int) $this->attributes['tasks_count']
            : $this->tasks()->count();

        if ($tasksCount > 0) {
            $completedTasksCount = array_key_exists('completed_tasks_count', $this->attributes)
                ? (int) $this->attributes['completed_tasks_count']
                : $this->completedTasks()->count();

            return (int) round(($completedTasksCount / $tasksCount) * 100);
        }

        return 0;
    }

    public function getProgressSourceLabelAttribute(): string
    {
        $hasMilestones = array_key_exists('milestones_count', $this->attributes)
            ? (int) $this->attributes['milestones_count'] > 0
            : $this->milestones()->exists();

        if ($hasMilestones) {
            return 'من مراحل المشروع';
        }

        $hasTasks = array_key_exists('tasks_count', $this->attributes)
            ? (int) $this->attributes['tasks_count'] > 0
            : $this->tasks()->exists();

        if ($hasTasks) {
            return 'من المهام';
        }

        return 'لا توجد مراحل أو مهام';
    }
}
