<?php

namespace App\GraphQL\Validators;

use Illuminate\Validation\Rule;
use Nuwave\Lighthouse\Validation\Validator;

class CreateProjectInputValidator extends Validator
{
    public function rules(): array
    {
        $teamId = $this->arg('team_id');

        return [
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('projects', 'title')->where('team_id', $teamId),
            ],
            'description' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.unique' => 'A project with this title already exists in this workspace.',
        ];
    }
}
