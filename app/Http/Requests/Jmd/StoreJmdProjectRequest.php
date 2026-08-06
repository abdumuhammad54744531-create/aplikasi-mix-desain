<?php

namespace App\Http\Requests\Jmd;

use App\Enums\JmdStatus;
use Illuminate\Validation\Rule;

class StoreJmdProjectRequest extends JmdFormRequest
{
    public function rules(): array
    {
        $project = $this->route('project');

        return [
            'jmd_number' => ['nullable', 'string', 'max:100', Rule::unique('projects', 'jmd_number')->ignore($project)],
            'report_number' => ['nullable', 'string', 'max:100'],
            'sample_number' => ['nullable', 'string', 'max:100'],
            'request_letter_number' => ['nullable', 'string', 'max:100'],
            'request_letter_date' => ['nullable', 'date'],
            'materials_received_at' => ['nullable', 'date'],
            'testing_date' => ['nullable', 'date'],
            'report_date' => ['nullable', 'date'],
            'name' => ['required', 'string', 'max:255'],
            'activity_name' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:255'],
            'owner' => ['nullable', 'string', 'max:255'],
            'employer' => ['nullable', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'director_name' => ['nullable', 'string', 'max:255'],
            'company_address' => ['nullable', 'string'],
            'tester_name' => ['nullable', 'string', 'max:255'],
            'reviewer_name' => ['nullable', 'string', 'max:255'],
            'laboratory_head_name' => ['nullable', 'string', 'max:255'],
            'laboratory_name' => ['nullable', 'string', 'max:255'],
            'work_type' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'jmd_status' => ['nullable', Rule::enum(JmdStatus::class)],
            'use_global_institution' => ['required', 'boolean'],
            'laboratory_logo' => ['nullable', 'image', 'max:4096'],
            'university_logo' => ['nullable', 'image', 'max:4096'],
            'letterhead' => ['nullable', 'image', 'max:8192'],
            'signature_stamp' => ['nullable', 'image', 'max:4096'],
        ];
    }
}
