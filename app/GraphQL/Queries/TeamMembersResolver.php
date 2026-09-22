<?php

namespace App\GraphQL\Queries;

use App\Models\Team;
use App\Models\User;

class TeamMembersResolver
{
    /**
     * @param  Team  $team
     * @param  array<string, mixed>  $args
     * @return array<int, array{id: int|string, name: string, email: string, role: string}>
     */
    public function __invoke(Team $team, array $args): array
    {
        $members = [];
        $addedUserIds = [];

        $owner = $team->owner ?: User::find($team->user_id);
        if ($owner) {
            $members[] = [
                'id' => (string) $owner->id,
                'name' => $owner->name,
                'email' => $owner->email,
                'role' => 'creator',
            ];
            $addedUserIds[] = (string) $owner->id;
        }

        foreach ($team->members as $member) {
            $memberIdStr = (string) $member->id;
            if (in_array($memberIdStr, $addedUserIds, true)) {
                continue;
            }

            $pivotRole = strtolower($member->pivot->role ?? 'user');
            if ($pivotRole === 'member') {
                $pivotRole = 'user';
            }

            $members[] = [
                'id' => $memberIdStr,
                'name' => $member->name,
                'email' => $member->email,
                'role' => $pivotRole,
            ];
            $addedUserIds[] = $memberIdStr;
        }

        return $members;
    }
}
