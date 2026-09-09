<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isEditor();
    }

    public function view(User $user, Project $project): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, Project $project): bool
    {
        return $this->viewAny($user);
    }

    public function delete(User $user, Project $project): bool
    {
        return $user->isSuperAdmin() || $user->isEditor();
    }
}
