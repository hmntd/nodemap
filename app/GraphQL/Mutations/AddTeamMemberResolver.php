<?php

namespace App\GraphQL\Mutations;

use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AddTeamMemberResolver
{
    /**
     * @param  null  $_
     * @param  array{team_id: int|string, email: string, role?: string}  $args
     */
    public function __invoke($_, array $args): Team
    {
        $authUser = Auth::user();
        if (! $authUser) {
            throw ValidationException::withMessages([
                'auth' => ['Unauthenticated.'],
            ]);
        }

        $email = trim($args['email']);
        $teamId = $args['team_id'];
        $desiredRole = strtolower(trim($args['role'] ?? 'user'));
        if ($desiredRole === 'member') {
            $desiredRole = 'user';
        }

        $team = Team::findOrFail($teamId);

        // Check Auth User Role
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
                'role' => ['Only workspace admins and creators can invite members.'],
            ]);
        }

        if ($desiredRole === 'admin' && $authRole !== 'creator') {
            throw ValidationException::withMessages([
                'role' => ['Only the workspace creator can invite members directly with admin role.'],
            ]);
        }

        $user = User::where('email', $email)->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'email' => ["No user found with email '{$email}'."],
            ]);
        }

        if ($team->user_id === $user->id || $team->members()->where('users.id', $user->id)->exists()) {
            throw ValidationException::withMessages([
                'email' => ["User '{$email}' is already a member of this workspace."],
            ]);
        }

        $team->members()->syncWithoutDetaching([$user->id => ['role' => $desiredRole]]);

        return $team->fresh(['members', 'owner', 'projects']);
    }
}
