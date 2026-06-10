<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\LearningMaterial;
use App\Repositories\LearningMaterialRepository;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class LearningMaterialController extends Controller
{
    public function index(LearningMaterialRepository $repository)
    {
        return view('student.learning-materials.index', ['subjects' => $repository->groupedForStudent()]);
    }

    public function open(LearningMaterial $learningMaterial)
    {
        Gate::authorize('view', $learningMaterial);
        abort_unless(Storage::disk('local')->exists($learningMaterial->file_path), 404, 'File materi tidak ditemukan.');

        return response()->file(Storage::disk('local')->path($learningMaterial->file_path), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$learningMaterial->original_name.'"',
        ]);
    }
}
