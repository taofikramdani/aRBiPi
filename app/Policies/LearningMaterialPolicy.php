<?php

namespace App\Policies;

use App\Models\LearningMaterial;
use App\Models\User;

class LearningMaterialPolicy
{
    public function view(User $user, LearningMaterial $material): bool
    {
        return $user->hasRole('admin') || ($user->hasRole('student') && $material->is_published && $material->subject->is_active);
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function update(User $user, LearningMaterial $material): bool
    {
        return $user->hasRole('admin');
    }

    public function delete(User $user, LearningMaterial $material): bool
    {
        return $user->hasRole('admin');
    }
}
