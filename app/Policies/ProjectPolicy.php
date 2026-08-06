<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function view(User $user, Project $project): bool
    {
        return $user->role !== 'pemohon';
    }

    public function updateJmd(User $user, Project $project): bool
    {
        return $user->role !== 'pemohon' && $user->access_level === 'edit' && $project->locked_at === null;
    }

    public function createRevision(User $user, Project $project): bool
    {
        return $user->role !== 'pemohon' && $user->access_level === 'edit' && $project->locked_at !== null;
    }

    public function approve(User $user, Project $project, string $approvalRole): bool
    {
        if (in_array($user->role, ['admin', 'administrator'], true)) {
            return true;
        }
        if ($user->role === 'pemohon' || $user->access_level !== 'edit') {
            return false;
        }
        $authorities = array_filter(array_map('trim', explode(',', (string) $user->approval_authority)));

        return in_array('*', $authorities, true) || in_array($approvalRole, $authorities, true);
    }
}
