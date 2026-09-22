<?php

namespace App\GraphQL\Validators;

use Illuminate\Validation\Rule;
use Nuwave\Lighthouse\Validation\Validator;

class CreateTeamInputValidator extends Validator
{
    public function rules(): array
    {
        $userId = $this->arg('user_id') ?? auth()->id();

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('teams', 'name')->where('user_id', $userId),
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
