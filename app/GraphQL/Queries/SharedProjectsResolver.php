<?php

namespace App\GraphQL\Queries;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class SharedProjectsResolver
{
    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     * @return Collection<int, Project>
     */
    public function __invoke($_, array $args): Collection
    {
        $user = Auth::user();
        if (! ($user instanceof User)) {
            return new Collection;
        }

        return $user->sharedProjects()->with(['team', 'diagrams'])->get();
    }
}
