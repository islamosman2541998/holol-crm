<?php

namespace App\Models;

use App\Traits\HasActivityLogs;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Member extends Model
{
    use HasFactory, SoftDeletes, HasActivityLogs;

    protected $fillable = [
        'user_id',
        'team_id',
        'manager_id',
        'name',
        'job_title',
        'department',
        'email',
        'phone',
        'mobile',
        'image',
        'hire_date',
        'is_manager',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'hire_date' => 'date',
            'is_manager' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function directManager()
    {
        return $this->belongsTo(Member::class, 'manager_id');
    }

    public function managedMembers()
    {
        return $this->hasMany(Member::class, 'manager_id');
    }

    public function managedTeams()
    {
        return $this->hasMany(Team::class, 'manager_member_id');
    }

    public function syncUserTeamRole(?int $oldTeamId = null): void
    {
        if (! $this->user) {
            return;
        }

        if ($oldTeamId && $oldTeamId != $this->team_id) {
            $oldTeam = Team::query()->find($oldTeamId);

            if ($oldTeam && $this->user->hasRole($oldTeam->role_name)) {
                $this->user->removeRole($oldTeam->role_name);
            }
        }

        if (! $this->team) {
            return;
        }

        $role = $this->team->ensureRole();

        $this->user->assignRole($role);
    }
    public function scopeAssignable($query)
    {
        return $query
            ->whereNotNull('user_id')
            ->where('status', 'active')
            ->with(['user', 'team'])
            ->orderBy('name');
    }
    public function tasks()
    {
        return $this->hasMany(Task::class, 'assigned_member_id');
    }

    public function openTasks()
    {
        return $this->hasMany(Task::class, 'assigned_member_id')
            ->whereNotIn('status', ['completed', 'cancelled']);
    }

    public function removeTeamRole(): void
    {
        if (! $this->user || ! $this->team) {
            return;
        }

        if ($this->user->hasRole($this->team->role_name)) {
            $this->user->removeRole($this->team->role_name);
        }
    }
    public function managedProjects()
    {
        return $this->hasMany(Project::class, 'manager_member_id');
    }
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'active' => 'نشط',
            'inactive' => 'غير نشط',
            'on_leave' => 'إجازة',
            'left' => 'ترك العمل',
            default => 'غير معروف',
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'active' => 'bg-success',
            'inactive' => 'bg-secondary',
            'on_leave' => 'bg-warning',
            'left' => 'bg-danger',
            default => 'bg-dark',
        };
    }
}
