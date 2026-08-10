<?php

namespace App\Traits;

use App\Models\Member;

trait AuthorizesOwnedRecords
{
    /**
     * Allow access when the user has the "view_all" permission, the record is
     * unclaimed (no owner assigned yet), owns the record (its assigned user id
     * matches the current user), or manages the team the owning user belongs to.
     * Aborts with 403 otherwise.
     */
    private function authorizeOwnedRecordAccess(string $viewAllPermission, ?int $ownerId): void
    {
        $user = auth()->user();

        if ($user->can($viewAllPermission)) {
            return;
        }

        if (! $ownerId) {
            return;
        }

        if ((int) $ownerId === (int) $user->id) {
            return;
        }

        $member = $user->member;

        if ($member && $member->is_manager && $member->team_id) {
            $isTeamMember = Member::query()
                ->where('team_id', $member->team_id)
                ->where('user_id', $ownerId)
                ->exists();

            abort_unless($isTeamMember, 403);

            return;
        }

        abort(403);
    }

    /**
     * Constrain a listing query to only the records the current user is allowed to see,
     * following the same rule as authorizeOwnedRecordAccess() above. Unclaimed records
     * (no owner assigned yet) stay visible to everyone with the base module permission.
     */
    private function applyOwnedRecordScope($query, string $viewAllPermission, string $ownerColumn): void
    {
        $user = auth()->user();

        if ($user->can($viewAllPermission)) {
            return;
        }

        $member = $user->member;

        $query->where(function ($query) use ($ownerColumn, $user, $member) {
            $query->whereNull($ownerColumn)
                ->orWhere($ownerColumn, $user->id);

            if ($member && $member->is_manager && $member->team_id) {
                $teamUserIds = Member::query()
                    ->where('team_id', $member->team_id)
                    ->whereNotNull('user_id')
                    ->pluck('user_id');

                $query->orWhereIn($ownerColumn, $teamUserIds);
            }
        });
    }
}
