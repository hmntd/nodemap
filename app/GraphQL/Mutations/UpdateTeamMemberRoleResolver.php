<?php

namespace App\GraphQL\Mutations;

use App\Models\Team;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class UpdateTeamMemberRoleResolver
{
    /**
     * @param  null  $_
     * @param  array{team_id: int|string, user_id: int|string, role: string}  $args
     */
    public function __invoke($_, array $args): Team
    {
        $authUser = Auth::user();
        if (! $authUser) {
            throw ValidationException::withMessages([
                'auth' => ['Unauthenticated.'],
            ]);
        }

        $team = Team::findOrFail($args['team_id']);
        $targetUserId = (int) $args['user_id'];
        $newRole = strtolower(trim($args['role']));

        if (! in_array($newRole, ['admin', 'user'], true)) {
            throw ValidationException::withMessages([
                'role' => ["Invalid role '{$newRole}'. Allowed roles are 'admin' or 'user'."],
            ]);
        }

        // Only CREATOR can change roles
        if ($team->user_id !== $authUser->id) {
            throw ValidationException::withMessages([
                'role' => ['Only the workspace creator can modify user roles.'],
            ]);
        }

        if ($targetUserId === $team->user_id) {
            throw ValidationException::withMessages([
                'role' => ['The workspace creator role cannot be changed.'],
            ]);
        }

        $isMember = $team->members()->where('users.id', $targetUserId)->exists();
        if (! $isMember) {
            throw ValidationException::withMessages([
                'user_id' => ['User is not a member of this workspace.'],
            ]);
        }

        $team->members()->updateExistingPivot($targetUserId, ['role' => $newRole]);

        return $team->fresh(['owner', 'members']);
    }
}
