<?php

namespace App\GraphQL\Validators;

use App\Models\Project;
use Illuminate\Validation\Rule;
use Nuwave\Lighthouse\Validation\Validator;

class UpdateProjectInputValidator extends Validator
{
    public function rules(): array
    {
        $projectId = $this->arg('id');
        $project = Project::find($projectId);
        $teamId = $project ? $project->team_id : null;

        return [
            'id' => ['required'],
            'title' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('projects', 'title')->where('team_id', $teamId)->ignore($projectId),
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
