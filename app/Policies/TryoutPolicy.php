<?php

namespace App\Policies;

use App\Models\Tryout;
use App\Models\User;

class TryoutPolicy
{
    public function before(User $u): ?bool
    {
        return $u->hasRole('admin') ? true : null;
    }

    public function viewAny(User $u): bool
    {
        return true;
    }

    public function view(User $u, Tryout $m): bool
    {
        return $m->is_published;
    }

    public function create(User $u): bool
    {
        return false;
    }

    public function update(User $u, Tryout $m): bool
    {
        return false;
    }

    public function delete(User $u, Tryout $m): bool
    {
        return false;
    }
}
