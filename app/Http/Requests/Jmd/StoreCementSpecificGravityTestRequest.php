<?php

namespace App\Http\Requests\Jmd;

use Illuminate\Validation\Validator;

class StoreCementSpecificGravityTestRequest extends JmdFormRequest
{
    public function rules(): array
    {
        return [
            'project_id' => ['required', 'integer', 'exists:projects,id'],
            'jmd_project_material_id' => ['nullable', 'integer', 'exists:jmd_project_materials,id'],
            'test_id' => ['nullable', 'integer'], 'sample_number' => ['nullable', 'string', 'max:100'],
            'tested_at' => ['required', 'date'], 'technician' => ['required', 'string', 'max:255'],
            'standard_source' => ['required', 'string', 'max:255'], 'notes' => ['nullable', 'string'],
            'observations' => ['required', 'array', 'min:2'], 'observations.*.id' => ['nullable', 'integer'],
            'observations.*.bottle_kerosene_mass' => ['required', 'numeric', 'min:0'],
            'observations.*.bottle_cement_kerosene_mass' => ['required', 'numeric', 'gt:observations.*.bottle_kerosene_mass'],
            'observations.*.initial_reading_ml' => ['required', 'numeric', 'min:0'],
            'observations.*.final_reading_ml' => ['required', 'numeric', 'gt:observations.*.initial_reading_ml'],
            'observations.*.test_temperature_c' => ['nullable', 'numeric'],
            'observations.*.water_density' => ['required', 'numeric', 'min:0.000001'],
        ];
    }

    public function after(): array
    {
        return [fn (Validator $validator) => $this->validateProjectOwnership($validator, 'jmd_project_materials', 'jmd_project_material_id')];
    }
}
