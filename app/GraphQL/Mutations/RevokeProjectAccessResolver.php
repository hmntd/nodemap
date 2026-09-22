<?php

namespace App\GraphQL\Mutations;

use App\Models\Project;

class RevokeProjectAccessResolver
{
    /**
     * @param  null  $_
     * @param  array{project_id: int|string, user_id: int|string}  $args
     */
    public function __invoke($_, array $args): Project
    {
        $project = Project::findOrFail($args['project_id']);
        $project->sharedUsers()->detach($args['user_id']);

        return $project->fresh(['sharedUsers', 'team', 'diagrams']);
    }
}
