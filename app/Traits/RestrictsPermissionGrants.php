<?php

namespace App\Traits;

use App\Models\Team;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

trait RestrictsPermissionGrants
{
    /**
     * Return only the base roles that the current user is allowed to grant.
     * Team roles are managed automatically through Member::syncUserTeamRole().
     */
    private function grantableRoles(?string $currentRole = null): Collection
    {
        $roles = Role::query()
            ->where('guard_name', 'web')
            ->where('name', 'not like', 'team\_%')
            ->with('permissions')
            ->orderBy('name')
            ->get();

        $user = auth()->user();

        if ($user->hasRole('SEO Manager')) {
            return $roles;
        }

        $granted = $user->getAllPermissions()->pluck('name');

        return $roles->filter(function (Role $role) use ($currentRole, $granted) {
            if ($role->name === $currentRole) {
                return true;
            }

            return $role->permissions->pluck('name')->diff($granted)->isEmpty();
        })->values();
    }

    private function guardRoleAssignment(string $roleName, ?string $currentRole = null): void
    {
        if (str_starts_with($roleName, 'team_')) {
            throw ValidationException::withMessages([
                'role' => 'أدوار الفرق تُدار من شاشة الأعضاء ولا يمكن تعيينها كدور أساسي.',
            ]);
        }

        if ($roleName === $currentRole || auth()->user()->hasRole('SEO Manager')) {
            return;
        }

        $role = Role::query()
            ->where('guard_name', 'web')
            ->where('name', $roleName)
            ->with('permissions')
            ->first();

        $granted = auth()->user()->getAllPermissions()->pluck('name');

        if (! $role || $role->permissions->pluck('name')->diff($granted)->isNotEmpty()) {
            throw ValidationException::withMessages([
                'role' => 'لا يمكنك منح دور يحتوي على صلاحيات لا تملكها.',
            ]);
        }
    }

    /**
     * Prevent a user from granting a role/team permissions they don't hold themselves,
     * so editing a team or role can't be used as a side door to gain new privileges.
     */
    private function filterGrantablePermissions(array $permissions): array
    {
        $user = auth()->user();

        if ($user->hasRole('SEO Manager')) {
            return $permissions;
        }

        $granted = $user->getAllPermissions()->pluck('name')->all();

        return array_values(array_intersect($permissions, $granted));
    }

    /**
     * Block assigning a member to a team whose role grants permissions, unless the
     * actor holds teams.permissions. Team membership auto-propagates the team's role
     * to the linked user account (Member::syncUserTeamRole), so without this check,
     * anyone who can edit members could grant themselves/others arbitrary
     * permissions simply by linking a user to a highly-privileged team.
     */
    private function guardTeamAssignment(?int $teamId): void
    {
        if (! $teamId) {
            return;
        }

        $user = auth()->user();

        if ($user->hasRole('SEO Manager') || $user->can('teams.permissions')) {
            return;
        }

        $team = Team::find($teamId);
        $role = $team?->role();

        if (! $role || $role->permissions->isEmpty()) {
            return;
        }

        throw ValidationException::withMessages([
            'team_id' => 'لا يمكنك تعيين هذا الفريق لأنه يمنح صلاحيات. يلزم صلاحية إدارة صلاحيات الفرق.',
        ]);
    }
}
