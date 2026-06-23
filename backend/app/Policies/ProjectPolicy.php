<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Project;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProjectPolicy
{
    use HandlesAuthorization;

    private function isAdmin(User $user): bool
    {
        return $user->role?->name === 'System Administrator';
    }

    public function viewAny(User $user)
    {
        return $this->isAdmin($user);
    }

    public function view(User $user, Project $project)
    {
        return $this->isAdmin($user);
    }

    public function create(User $user)
    {
        return $this->isAdmin($user);
    }

    public function update(User $user, Project $project)
    {
        return $this->isAdmin($user);
    }

    public function delete(User $user, Project $project)
    {
        return $this->isAdmin($user);
    }
}