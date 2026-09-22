<?php

namespace App\GraphQL\Queries;

use App\Models\Team;
use App\Models\User;

class TeamMembersResolver
{
    /**
     * @param  array<string, mixed>  $args
     * @return array<int, array{id: int|string, name: string, email: string, role: string}>
     */
    public function __invoke(Team $team, array $args): array
    {
        $members = [];
        $addedUserIds = [];

        /** @var User|null $owner */
        $owner = $team->owner ?: User::find($team->user_id);
        if ($owner) {
            $members[] = [
                'id' => (string) $owner->id,
                'name' => (string) $owner->name,
                'email' => (string) $owner->email,
                'role' => 'creator',
            ];
            $addedUserIds[] = (string) $owner->id;
        }

        /** @var User $member */
        foreach ($team->members as $member) {
            $memberIdStr = (string) $member->id;
            if (in_array($memberIdStr, $addedUserIds, true)) {
                continue;
            }

            $pivotRole = $member->pivot ? (string) $member->pivot->getAttribute('role') : 'user';
            $pivotRole = strtolower($pivotRole);
            if ($pivotRole === 'member') {
                $pivotRole = 'user';
            }

            $members[] = [
                'id' => $memberIdStr,
                'name' => (string) $member->name,
                'email' => (string) $member->email,
                'role' => $pivotRole,
            ];
            $addedUserIds[] = $memberIdStr;
        }

        return $members;
    }
}
