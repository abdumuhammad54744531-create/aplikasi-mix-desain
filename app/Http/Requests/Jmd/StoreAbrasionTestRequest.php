<?php

namespace App\Http\Requests\Jmd;

use Illuminate\Validation\Validator;

class StoreAbrasionTestRequest extends JmdFormRequest
{
    public function rules(): array
    {
        return [
            'project_id' => ['required', 'integer', 'exists:projects,id'],
            'jmd_project_material_id' => ['nullable', 'integer', 'exists:jmd_project_materials,id'],
            'test_id' => ['nullable', 'integer'], 'sample_number' => ['nullable', 'string', 'max:100'],
            'inspection_gradation' => ['nullable', 'string', 'max:255'],
            'steel_ball_count' => ['nullable', 'integer', 'min:0'], 'revolution_count' => ['nullable', 'integer', 'min:0'],
            'limit_percent' => ['required', 'numeric', 'between:0,100'],
            'tested_at' => ['required', 'date'], 'technician' => ['required', 'string', 'max:255'],
            'standard_source' => ['nullable', 'required_unless:value_source,table', 'string', 'max:255'], 'notes' => ['nullable', 'string'],
            'observations' => ['required', 'array', 'min:2'], 'observations.*.id' => ['nullable', 'integer'],
            'observations.*.passing_sieve_mm' => ['nullable', 'numeric', 'min:0'],
            'observations.*.retained_sieve_mm' => ['nullable', 'numeric', 'min:0'],
            'observations.*.initial_mass' => ['required', 'numeric', 'min:0.000001'],
            'observations.*.retained_no12_mass' => ['required', 'numeric', 'min:0'],
        ] + $this->standardSelectionRules();
    }

    public function after(): array
    {
        return [fn (Validator $validator) => $this->validateProjectOwnership($validator, 'jmd_project_materials', 'jmd_project_material_id')];
    }
}
