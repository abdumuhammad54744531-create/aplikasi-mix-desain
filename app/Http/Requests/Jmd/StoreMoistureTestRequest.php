<?php

namespace App\Http\Requests\Jmd;

use App\Enums\AggregateType;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreMoistureTestRequest extends JmdFormRequest
{
    public function rules(): array
    {
        return [
            'project_id' => ['required', 'integer', 'exists:projects,id'],
            'jmd_project_material_id' => ['nullable', 'integer', 'exists:jmd_project_materials,id'],
            'test_number' => ['required', 'string', 'max:100'],
            'sample_number' => ['nullable', 'string', 'max:100'],
            'aggregate_type' => ['required', Rule::enum(AggregateType::class)],
            'tested_at' => ['required', 'date'],
            'technician' => ['required', 'string', 'max:255'],
            'observations' => ['required', 'array', 'min:2'],
            'observations.*.id' => ['nullable', 'integer'],
            'observations.*.container_mass' => ['required', 'numeric', 'min:0'],
            'observations.*.wet_container_mass' => ['required', 'numeric', 'gt:observations.*.container_mass'],
            'observations.*.dry_container_mass' => ['required', 'numeric', 'gt:observations.*.container_mass'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function after(): array
    {
        return [fn (Validator $validator) => $this->validateProjectOwnership($validator, 'jmd_project_materials', 'jmd_project_material_id')];
    }
}
