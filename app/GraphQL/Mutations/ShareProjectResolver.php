<?php

namespace App\GraphQL\Mutations;

use App\Models\Project;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class ShareProjectResolver
{
    /**
     * @param  null  $_
     * @param  array{project_id: int|string, email: string}  $args
     */
    public function __invoke($_, array $args): Project
    {
        $email = trim($args['email']);
        $projectId = $args['project_id'];

        $project = Project::findOrFail($projectId);
        $user = User::where('email', $email)->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'email' => ["No user found with email '{$email}'."],
            ]);
        }

        // Check if user is already a member of the project's team
        $team = $project->team;
        if ($team) {
            $isTeamMember = $team->members()->where('users.id', $user->id)->exists()
                || $team->user_id === $user->id;

            if ($isTeamMember) {
                throw ValidationException::withMessages([
                    'email' => ["User '{$email}' is already a member of this team."],
                ]);
            }
        }

        $project->sharedUsers()->syncWithoutDetaching([$user->id => ['role' => 'viewer']]);

        return $project->fresh(['sharedUsers', 'team', 'diagrams']);
    }
}
