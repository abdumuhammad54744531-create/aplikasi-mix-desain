<?php

namespace App\Http\Requests\Jmd;

class StoreStandardValueRequest extends StandardMasterRequest
{
    public function rules(): array
    {
        return [
            'row_key' => ['nullable', 'string', 'max:150'], 'column_key' => ['nullable', 'string', 'max:150'],
            'dimension_values' => ['nullable', 'array'], 'numeric_value' => ['nullable', 'numeric', 'required_without_all:text_value,min_value,max_value'],
            'text_value' => ['nullable', 'string'], 'min_value' => ['nullable', 'numeric'], 'max_value' => ['nullable', 'numeric'],
            'unit' => ['nullable', 'string', 'max:50'], 'notes' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'], 'is_active' => ['nullable', 'boolean'],
        ];
    }
}
