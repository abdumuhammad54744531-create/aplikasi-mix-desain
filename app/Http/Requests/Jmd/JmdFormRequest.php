<?php

namespace App\Http\Requests\Jmd;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

abstract class JmdFormRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $project = $this->route('project');
        if ($project instanceof Project) {
            $this->merge(['project_id' => $project->getKey()]);
        }
        if (! $this->filled('value_source')) {
            $this->merge(['value_source' => 'legacy']);
        }
    }

    public function authorize(): bool
    {
        $user = $this->user();
        if (! $user || $user->role === 'pemohon') {
            return false;
        }
        if ($user->access_level === 'read' && ! in_array($user->role, ['admin', 'administrator'], true)) {
            return false;
        }
        $project = $this->jmdProject();

        return ! $project?->locked_at;
    }

    protected function jmdProject(): ?Project
    {
        $bound = $this->route('project');
        if ($bound instanceof Project) {
            return $bound;
        }

        return $this->integer('project_id') ? Project::find($this->integer('project_id')) : null;
    }

    protected function validateProjectOwnership(Validator $validator, string $table, string $field): void
    {
        if (! $this->filled($field) || ! $this->integer('project_id')) {
            return;
        }
        $belongs = DB::table($table)
            ->where('id', $this->integer($field))
            ->where('project_id', $this->integer('project_id'))
            ->exists();
        if (! $belongs) {
            $validator->errors()->add($field, 'Record yang dipilih tidak terhubung ke proyek ini.');
        }
    }

    protected function standardSelectionRules(): array
    {
        return [
            'value_source' => ['required', Rule::in(['legacy', 'table', 'manual'])],
            'standard_table_value_id' => ['nullable', 'integer', 'required_if:value_source,table', 'exists:standard_table_values,id'],
            'manual_standard_reason' => ['nullable', 'string', 'min:5', 'required_if:value_source,manual'],
        ];
    }
}
