<?php

namespace App\GraphQL\Validators;

use App\Models\Team;
use Illuminate\Validation\Rule;
use Nuwave\Lighthouse\Validation\Validator;

class UpdateTeamInputValidator extends Validator
{
    public function rules(): array
    {
        $teamId = $this->arg('id');
        $team = Team::find($teamId);
        $userId = $team ? $team->user_id : auth()->id();

        return [
            'id' => ['required'],
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('teams', 'name')->where('user_id', $userId)->ignore($teamId),
            ],
            'description' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'You already have a workspace with this name.',
        ];
    }
}
