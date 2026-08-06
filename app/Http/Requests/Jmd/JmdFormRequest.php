<?php

namespace App\Http\Requests\Jmd;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Validator;

abstract class JmdFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if (! $user || $user->role === 'pemohon') {
            return false;
        }
        if ($user->access_level === 'read' && ! in_array($user->role, ['admin', 'administrator'], true)) {
            return false;
        }
        $project = $this->jmdProject();

        return ! $project?->locked_at;
    }

    protected function jmdProject(): ?Project
    {
        $bound = $this->route('project');
        if ($bound instanceof Project) {
            return $bound;
        }

        return $this->integer('project_id') ? Project::find($this->integer('project_id')) : null;
    }

    protected function validateProjectOwnership(Validator $validator, string $table, string $field): void
    {
        if (! $this->filled($field) || ! $this->integer('project_id')) {
            return;
        }
        $belongs = DB::table($table)
            ->where('id', $this->integer($field))
            ->where('project_id', $this->integer('project_id'))
            ->exists();
        if (! $belongs) {
            $validator->errors()->add($field, 'Record yang dipilih tidak terhubung ke proyek ini.');
        }
    }
}
