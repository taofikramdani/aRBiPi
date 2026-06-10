<?php

namespace App\Policies;

use App\Models\Subject;
use App\Models\User;

class SubjectPolicy
{
    public function before(User $u): ?bool
    {
        return $u->hasRole('admin') ? true : null;
    }

    public function viewAny(User $u): bool
    {
        return false;
    }

    public function view(User $u, Subject $m): bool
    {
        return false;
    }

    public function create(User $u): bool
    {
        return false;
    }

    public function update(User $u, Subject $m): bool
    {
        return false;
    }

    public function delete(User $u, Subject $m): bool
    {
        return false;
    }
}
