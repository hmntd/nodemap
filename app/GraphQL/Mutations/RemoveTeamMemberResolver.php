<?php

namespace App\GraphQL\Mutations;

use App\Models\Team;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class RemoveTeamMemberResolver
{
    /**
     * @param  null  $_
     * @param  array{team_id: int|string, user_id: int|string}  $args
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

        // Determine Auth User Role
        $authRole = 'user';
        if ($team->user_id === $authUser->id) {
            $authRole = 'creator';
        } else {
            $memberPivot = $team->members()->where('users.id', $authUser->id)->first();
            $pivotRole = $memberPivot ? (string) $memberPivot->pivot->getAttribute('role') : '';
            if (strtolower($pivotRole) === 'admin') {
                $authRole = 'admin';
            }
        }

        if ($authRole === 'user') {
            throw ValidationException::withMessages([
                'role' => ['Only workspace admins and creators can remove members.'],
            ]);
        }

        // Check Target User Role
        if ($targetUserId === $team->user_id) {
            throw ValidationException::withMessages([
                'role' => ['Workspace creator cannot be removed from their workspace.'],
            ]);
        }

        $targetMemberPivot = $team->members()->where('users.id', $targetUserId)->first();
        if (! $targetMemberPivot) {
            throw ValidationException::withMessages([
                'user_id' => ['User is not a member of this workspace.'],
            ]);
        }

        $targetPivotRole = (string) $targetMemberPivot->pivot->getAttribute('role');
        $targetRole = strtolower($targetPivotRole) === 'admin' ? 'admin' : 'user';

        if ($authRole === 'admin' && $targetRole === 'admin') {
            throw ValidationException::withMessages([
                'role' => ['Workspace admins cannot remove other admins.'],
            ]);
        }

        // Detach member
        $team->members()->detach($targetUserId);

        return $team->fresh(['owner', 'members']);
    }
}
