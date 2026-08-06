<?php

namespace App\Http\Requests\Jmd;

class StoreManualOverrideRequest extends JmdFormRequest
{
    public function rules(): array
    {
        return [
            'project_id' => ['required', 'integer', 'exists:projects,id'],
            'module' => ['required', 'string', 'max:80'],
            'field_name' => ['required', 'string', 'max:100'],
            'record_type' => ['nullable', 'string', 'max:255'],
            'record_id' => ['nullable', 'integer', 'min:1'],
            'original_value' => ['nullable', 'array'],
            'override_value' => ['required', 'array'],
            'reason' => ['required', 'string', 'min:10'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
