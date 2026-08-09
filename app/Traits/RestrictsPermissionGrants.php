<?php

namespace App\Traits;

use App\Models\Team;
use Illuminate\Validation\ValidationException;

trait RestrictsPermissionGrants
{
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
