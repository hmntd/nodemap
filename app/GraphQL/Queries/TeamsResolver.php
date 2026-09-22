<?php

namespace App\GraphQL\Queries;

use App\Models\Team;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class TeamsResolver
{
    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     * @return Collection<int, Team>
     */
    public function __invoke($_, array $args): Collection
    {
        $user = Auth::user();
        if (! $user) {
            return new Collection();
        }

        return Team::where('user_id', $user->id)
            ->orWhereHas('members', function ($query) use ($user) {
                $query->where('users.id', $user->id);
            })
            ->with(['members', 'owner', 'projects'])
            ->get();
    }
}
