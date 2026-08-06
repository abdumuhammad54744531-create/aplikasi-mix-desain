<?php

namespace App\Http\Requests\Jmd;

use App\Enums\AggregateType;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreSiltTestRequest extends JmdFormRequest
{
    public function rules(): array
    {
        return [
            'project_id' => ['required', 'integer', 'exists:projects,id'],
            'jmd_project_material_id' => ['nullable', 'integer', 'exists:jmd_project_materials,id'],
            'test_number' => ['required', 'string', 'max:100'],
            'aggregate_type' => ['required', Rule::enum(AggregateType::class)],
            'tested_at' => ['required', 'date'],
            'technician' => ['required', 'string', 'max:255'],
            'limit_percent' => ['required', 'numeric', 'min:0'],
            'observations' => ['required', 'array', 'min:2'],
            'observations.*.container_mass' => ['required', 'numeric', 'min:0'],
            'observations.*.before_wash_container_mass' => ['required', 'numeric', 'gt:observations.*.container_mass'],
            'observations.*.after_wash_container_mass' => ['required', 'numeric', 'gt:observations.*.container_mass', 'lte:observations.*.before_wash_container_mass'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function after(): array
    {
        return [fn (Validator $validator) => $this->validateProjectOwnership($validator, 'jmd_project_materials', 'jmd_project_material_id')];
    }
}
