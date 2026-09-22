<?php

namespace App\GraphQL\Queries;

use App\Models\Project;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class ProjectsResolver
{
    /**
     * @param  null  $_
     * @param  array{team_id?: int|string}  $args
     * @return Collection<int, Project>
     */
    public function __invoke($_, array $args): Collection
    {
        $teamId = $args['team_id'] ?? null;
        $user = Auth::user();

        if ($teamId) {
            return Project::where('team_id', $teamId)
                ->orWhereNull('team_id')
                ->get();
        }

        if (! $user) {
            return Project::all();
        }

        $teamIds = $user->teams()->pluck('teams.id')
            ->merge($user->ownedTeams()->pluck('id'))
            ->unique();

        return Project::where(function ($query) use ($teamIds, $user) {
            $query->whereIn('team_id', $teamIds)
                ->orWhere('user_id', $user->id)
                ->orWhereNull('team_id');
        })->get();
    }
}
