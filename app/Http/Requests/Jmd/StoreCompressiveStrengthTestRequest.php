<?php

namespace App\Http\Requests\Jmd;

use App\Enums\SpecimenType;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreCompressiveStrengthTestRequest extends JmdFormRequest
{
    public function rules(): array
    {
        return [
            'project_id' => ['required', 'integer', 'exists:projects,id'],
            'trial_mix_id' => ['nullable', 'integer', 'exists:trial_mixes,id'],
            'test_number' => ['required', 'string', 'max:100'],
            'target_strength_mpa' => ['required', 'numeric', 'gt:0'],
            'target_age_days' => ['required', 'integer', 'min:1'],
            'kg_cm2_per_mpa' => ['required', 'numeric', 'gt:0'],
            'minimum_statistical_samples' => ['required', 'integer', 'min:2'],
            'specimens' => ['required', 'array', 'min:1'],
            'specimens.*.number' => ['required', 'string', 'max:100', 'distinct'],
            'specimens.*.type' => ['required', Rule::enum(SpecimenType::class)],
            'specimens.*.cast_at' => ['required', 'date'],
            'specimens.*.tested_at' => ['required', 'date', 'after_or_equal:specimens.*.cast_at'],
            'specimens.*.maximum_load' => ['required', 'numeric', 'gt:0'],
            'specimens.*.load_unit' => ['required', Rule::in(['N', 'kN', 'kgf', 'ton'])],
            'specimens.*.diameter_mm' => ['nullable', 'numeric', 'gt:0', 'required_if:specimens.*.type,cylinder'],
            'specimens.*.length_mm' => ['nullable', 'numeric', 'gt:0', 'required_unless:specimens.*.type,cylinder'],
            'specimens.*.width_mm' => ['nullable', 'numeric', 'gt:0', 'required_unless:specimens.*.type,cylinder'],
            'specimens.*.age_factor' => ['required', 'numeric', 'gt:0'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function after(): array
    {
        return [fn (Validator $validator) => $this->validateProjectOwnership($validator, 'trial_mixes', 'trial_mix_id')];
    }
}
