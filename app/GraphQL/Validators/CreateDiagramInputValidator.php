<?php

namespace App\GraphQL\Validators;

use App\Models\Project;
use Illuminate\Validation\Rule;
use Nuwave\Lighthouse\Validation\Validator;

class CreateDiagramInputValidator extends Validator
{
    public function rules(): array
    {
        $projectId = $this->arg('project_id');
        $project = Project::find($projectId);
        $teamId = $project ? $project->team_id : null;

        return [
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('diagrams', 'title')->where(function ($query) use ($teamId, $projectId) {
                    if ($teamId) {
                        return $query->whereIn('project_id', Project::where('team_id', $teamId)->pluck('id'));
                    }
                    return $query->where('project_id', $projectId);
                }),
            ],
            'description' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.unique' => 'A diagram board with this title already exists in this workspace.',
        ];
    }
}
