<?php

namespace App\Http\Requests\Jmd;

use App\Enums\SpecimenType;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreTrialMixRequest extends JmdFormRequest
{
    public function rules(): array
    {
        return [
            'project_id' => ['required', 'integer', 'exists:projects,id'],
            'mix_design_calculation_id' => ['required', 'integer', 'exists:mix_design_calculations,id'],
            'batch_number' => ['required', 'string', 'max:100'],
            'specimen_type' => ['required', Rule::enum(SpecimenType::class)],
            'specimen_count' => ['required', 'integer', 'min:1'],
            'diameter_mm' => ['nullable', 'numeric', 'gt:0', 'required_if:specimen_type,cylinder'],
            'height_mm' => ['nullable', 'numeric', 'gt:0', 'required_if:specimen_type,cylinder,beam'],
            'length_mm' => ['nullable', 'numeric', 'gt:0', 'required_if:specimen_type,cube,beam'],
            'width_mm' => ['nullable', 'numeric', 'gt:0', 'required_if:specimen_type,beam'],
            'manual_specimen_volume_m3' => ['nullable', 'numeric', 'gt:0', 'required_if:specimen_type,custom'],
            'waste_factor' => ['required', 'numeric', 'gte:1'],
            'slump_volume_m3' => ['required', 'numeric', 'min:0'],
            'manual_extra_volume_m3' => ['required', 'numeric', 'min:0'],
            'materials_per_m3' => ['required', 'array', 'min:4'],
            'materials_per_m3.*' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function after(): array
    {
        return [fn (Validator $validator) => $this->validateProjectOwnership($validator, 'mix_design_calculations', 'mix_design_calculation_id')];
    }
}
