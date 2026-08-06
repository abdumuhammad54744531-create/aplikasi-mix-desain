<?php

namespace App\Http\Requests\Jmd;

use App\Enums\AggregateType;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreSieveTestRequest extends JmdFormRequest
{
    public function rules(): array
    {
        return [
            'project_id' => ['required', 'integer', 'exists:projects,id'],
            'jmd_project_material_id' => ['nullable', 'integer', 'exists:jmd_project_materials,id'],
            'test_id' => ['nullable', 'integer'], 'sample_number' => ['nullable', 'string', 'max:100'],
            'aggregate_type' => ['required', Rule::enum(AggregateType::class)],
            'initial_sample_mass' => ['required', 'numeric', 'min:0.000001'],
            'loss_tolerance_percent' => ['required', 'numeric', 'min:0'],
            'gradation_zone' => ['nullable', 'string', 'max:50'],
            'tested_at' => ['required', 'date'], 'technician' => ['required', 'string', 'max:255'],
            'standard_source' => ['nullable', 'required_unless:value_source,table', 'string', 'max:255'], 'notes' => ['nullable', 'string'],
            'observations' => ['required', 'array', 'min:2'], 'observations.*.id' => ['nullable', 'integer'],
            'observations.*.sieve_label' => ['required', 'string', 'max:50'],
            'observations.*.sieve_size_mm' => ['nullable', 'numeric', 'min:0'],
            'observations.*.is_pan' => ['nullable', 'boolean'],
            'observations.*.retained_mass' => ['required', 'numeric', 'min:0'],
            'observations.*.lower_limit_percent' => ['nullable', 'numeric', 'between:0,100'],
            'observations.*.upper_limit_percent' => ['nullable', 'numeric', 'between:0,100'],
            'observations.*.planned_passing_percent' => ['nullable', 'numeric', 'between:0,100'],
        ] + $this->standardSelectionRules();
    }

    public function after(): array
    {
        return [fn (Validator $validator) => $this->validateProjectOwnership($validator, 'jmd_project_materials', 'jmd_project_material_id')];
    }
}
