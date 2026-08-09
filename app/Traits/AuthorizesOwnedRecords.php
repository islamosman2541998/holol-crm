<?php

namespace App\Traits;

use App\Models\Member;

trait AuthorizesOwnedRecords
{
    /**
     * Allow access when the user has the "view_all" permission, owns the record
     * (its assigned user id matches the current user), or manages the team the
     * owning user belongs to. Aborts with 403 otherwise.
     */
    private function authorizeOwnedRecordAccess(string $viewAllPermission, ?int $ownerId): void
    {
        $user = auth()->user();

        if ($user->can($viewAllPermission)) {
            return;
        }

        if ($ownerId && (int) $ownerId === (int) $user->id) {
            return;
        }

        $member = $user->member;

        if ($member && $member->is_manager && $member->team_id && $ownerId) {
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
     * following the same rule as authorizeOwnedRecordAccess() above.
     */
    private function applyOwnedRecordScope($query, string $viewAllPermission, string $ownerColumn): void
    {
        $user = auth()->user();

        if ($user->can($viewAllPermission)) {
            return;
        }

        $member = $user->member;

        if (! $member) {
            $query->whereRaw('1 = 0');

            return;
        }

        if ($member->is_manager && $member->team_id) {
            $teamUserIds = Member::query()
                ->where('team_id', $member->team_id)
                ->whereNotNull('user_id')
                ->pluck('user_id');

            $query->whereIn($ownerColumn, $teamUserIds);

            return;
        }

        $query->where($ownerColumn, $user->id);
    }
}
