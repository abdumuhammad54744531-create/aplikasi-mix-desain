<?php

namespace App\Http\Requests\Jmd;

use Illuminate\Foundation\Http\FormRequest;

abstract class StandardMasterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->role, ['admin', 'administrator'], true);
    }
}
