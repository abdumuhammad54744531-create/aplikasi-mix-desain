<?php

namespace App\Http\Requests\Jmd;

class StoreStandardReferenceRequest extends StandardMasterRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'], 'standard_number' => ['nullable', 'string', 'max:255'],
            'standard_year' => ['nullable', 'string', 'max:20'], 'effective_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:effective_at'], 'source_url' => ['nullable', 'url', 'max:500'],
            'description' => ['nullable', 'string'],
        ];
    }
}
