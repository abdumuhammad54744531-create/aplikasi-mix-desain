<?php

namespace App\Http\Requests\Jmd;

use App\Services\Jmd\StandardMasterService;
use Illuminate\Validation\Rule;

class StoreStandardTableRequest extends StandardMasterRequest
{
    public function rules(): array
    {
        return [
            'key' => ['required', 'string', 'max:100', Rule::in(array_keys(StandardMasterService::CATALOG))],
            'name' => ['required', 'string', 'max:255'], 'unit' => ['nullable', 'string', 'max:50'],
            'dimensions' => ['nullable', 'array'], 'dimensions.*' => ['string', 'max:100'], 'notes' => ['nullable', 'string'],
        ];
    }
}
