<?php

namespace App\Policies;

use App\Models\Question;
use App\Models\User;

class QuestionPolicy
{
    public function before(User $u): ?bool
    {
        return $u->hasRole('admin') ? true : null;
    }

    public function viewAny(User $u): bool
    {
        return false;
    }

    public function view(User $u, Question $m): bool
    {
        return false;
    }

    public function create(User $u): bool
    {
        return false;
    }

    public function update(User $u, Question $m): bool
    {
        return false;
    }

    public function delete(User $u, Question $m): bool
    {
        return false;
    }
}
