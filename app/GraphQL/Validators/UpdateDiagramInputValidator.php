<?php

namespace App\GraphQL\Validators;

use App\Models\Diagram;
use App\Models\Project;
use Illuminate\Validation\Rule;
use Nuwave\Lighthouse\Validation\Validator;

class UpdateDiagramInputValidator extends Validator
{
    public function rules(): array
    {
        $diagramId = $this->arg('id');
        $diagram = Diagram::where('id', $diagramId)->first();
        $projectId = $diagram ? $diagram->project_id : null;
        $project = $projectId ? Project::where('id', $projectId)->first() : null;
        $teamId = $project ? $project->team_id : null;

        return [
            'id' => ['required'],
            'title' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('diagrams', 'title')->ignore($diagramId)->where(function ($query) use ($teamId, $projectId) {
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
