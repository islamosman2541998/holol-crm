<?php

namespace App\Traits;

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
}
