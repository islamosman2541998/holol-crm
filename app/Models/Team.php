<?php

namespace App\Models;

use App\Traits\HasActivityLogs;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class Team extends Model
{
    use HasFactory, SoftDeletes, HasActivityLogs;

    protected $fillable = [
        'manager_member_id',
        'name',
        'code',
        'role_name',
        'description',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
        ];
    }

    public function members()
    {
        return $this->hasMany(Member::class);
    }

    public function activeMembers()
    {
        return $this->hasMany(Member::class)->where('status', 'active');
    }

    public function manager()
    {
        return $this->belongsTo(Member::class, 'manager_member_id');
    }

    public function role()
    {
        return Role::query()->where('name', $this->role_name)->first();
    }

    public function ensureRole(): Role
    {
        return Role::findOrCreate($this->role_name, 'web');
    }

    public function syncRolePermissions(array $permissions): void
    {
        $role = $this->ensureRole();

        $role->syncPermissions($permissions);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function syncMembersTeamRole(): void
    {
        $role = $this->ensureRole();

        $this->members()
            ->with('user')
            ->whereNotNull('user_id')
            ->get()
            ->each(function (Member $member) use ($role) {
                if ($member->user) {
                    $member->user->assignRole($role);
                }
            });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
    public function getStatusLabelAttribute(): string
    {
        return $this->status ? 'نشط' : 'غير نشط';
    }
public function projects()
{
    return $this->hasMany(Project::class);
}
    public function getStatusBadgeClassAttribute(): string
    {
        return $this->status ? 'bg-success' : 'bg-secondary';
    }
}
