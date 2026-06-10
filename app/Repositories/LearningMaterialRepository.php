<?php

namespace App\Repositories;

use App\Models\Subject;

class LearningMaterialRepository
{
    public function groupedForAdmin()
    {
        return Subject::with(['learningMaterials' => fn ($query) => $query->with('uploader')->latest()])
            ->withCount('learningMaterials')
            ->orderBy('name')
            ->get();
    }

    public function groupedForStudent()
    {
        return Subject::where('is_active', true)
            ->whereHas('learningMaterials', fn ($query) => $query->where('is_published', true))
            ->with(['learningMaterials' => fn ($query) => $query->where('is_published', true)->latest()])
            ->withCount(['learningMaterials' => fn ($query) => $query->where('is_published', true)])
            ->orderBy('name')
            ->get();
    }
}
