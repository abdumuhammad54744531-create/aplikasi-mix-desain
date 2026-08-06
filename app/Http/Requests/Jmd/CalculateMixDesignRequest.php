<?php

namespace App\Http\Requests\Jmd;

use Illuminate\Validation\Validator;

class CalculateMixDesignRequest extends JmdFormRequest
{
    public function rules(): array
    {
        return [
            'project_id' => ['required', 'integer', 'exists:projects,id'],
            'jmd_design_criteria_id' => ['required', 'integer', 'exists:jmd_design_criteria,id'],
            'specified_strength_mpa' => ['required', 'numeric', 'gt:0'],
            'statistical_factor_k' => ['required', 'numeric', 'gt:0'],
            'standard_deviation_mpa' => ['required', 'numeric', 'min:0'],
            'strength_water_cement_ratio' => ['required', 'numeric', 'gt:0', 'lte:1'],
            'durability_maximum_water_cement_ratio' => ['required', 'numeric', 'gt:0', 'lte:1'],
            'manual_water_cement_ratio' => ['nullable', 'numeric', 'gt:0', 'lte:1', 'required_with:manual_override_reason'],
            'manual_override_reason' => ['nullable', 'string', 'min:10', 'required_with:manual_water_cement_ratio'],
            'water_content_kg' => ['required', 'numeric', 'gt:0'],
            'minimum_cement_kg' => ['required', 'numeric', 'min:0'],
            'maximum_cement_kg' => ['nullable', 'numeric', 'gt:minimum_cement_kg'],
            'cement_specific_gravity' => ['required', 'numeric', 'gt:0'],
            'fine_aggregate_specific_gravity' => ['required', 'numeric', 'gt:0'],
            'coarse_aggregate_specific_gravity' => ['required', 'numeric', 'gt:0'],
            'coarse_aggregate_bulk_density_kg_m3' => ['required', 'numeric', 'gt:0'],
            'coarse_aggregate_volume_factor' => ['required', 'numeric', 'gt:0'],
            'air_content_percent' => ['required', 'numeric', 'between:0,20'],
            'admixture_mass_kg' => ['nullable', 'numeric', 'min:0'],
            'admixture_specific_gravity' => ['nullable', 'numeric', 'gt:0', 'required_if_accepted:uses_admixture'],
            'uses_admixture' => ['required', 'boolean'],
            'standard_sources' => ['required', 'array', 'min:1'],
        ];
    }

    public function after(): array
    {
        return [fn (Validator $validator) => $this->validateProjectOwnership($validator, 'jmd_design_criteria', 'jmd_design_criteria_id')];
    }
}
