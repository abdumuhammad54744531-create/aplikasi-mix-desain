<?php

namespace App\Http\Requests\Jmd;

use Illuminate\Validation\Validator;

class StoreCoarseAggregateSpecificGravityTestRequest extends JmdFormRequest
{
    public function rules(): array
    {
        return [
            'project_id' => ['required', 'integer', 'exists:projects,id'],
            'jmd_project_material_id' => ['nullable', 'integer', 'exists:jmd_project_materials,id'],
            'test_id' => ['nullable', 'integer'], 'sample_number' => ['nullable', 'string', 'max:100'],
            'tested_at' => ['required', 'date'], 'technician' => ['required', 'string', 'max:255'],
            'standard_source' => ['nullable', 'required_unless:value_source,table', 'string', 'max:255'], 'notes' => ['nullable', 'string'],
            'observations' => ['required', 'array', 'min:2'], 'observations.*.id' => ['nullable', 'integer'],
            'observations.*.ssd_air_mass' => ['required', 'numeric', 'min:0.000001'],
            'observations.*.submerged_mass' => ['required', 'numeric', 'min:0'],
            'observations.*.oven_dry_mass' => ['required', 'numeric', 'min:0.000001'],
        ] + $this->standardSelectionRules();
    }

    public function after(): array
    {
        return [fn (Validator $validator) => $this->validateProjectOwnership($validator, 'jmd_project_materials', 'jmd_project_material_id')];
    }
}
