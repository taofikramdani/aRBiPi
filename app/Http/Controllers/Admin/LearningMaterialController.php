<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\LearningMaterialRequest;
use App\Models\LearningMaterial;
use App\Models\Subject;
use App\Repositories\LearningMaterialRepository;
use App\Services\LearningMaterialService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class LearningMaterialController extends Controller
{
    public function index(LearningMaterialRepository $repository)
    {
        return view('admin.learning-materials.index', ['subjects' => $repository->groupedForAdmin()]);
    }

    public function create()
    {
        return view('admin.learning-materials.form', [
            'learningMaterial' => new LearningMaterial,
            'subjects' => Subject::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(LearningMaterialRequest $request, LearningMaterialService $service)
    {
        $service->create(
            $request->safe()->except('pdf'),
            $request->file('pdf'),
            $request->user()->id,
        );

        return redirect()->route('admin.learning-materials.index')->with('success', 'Materi pembelajaran berhasil diunggah.');
    }

    public function edit(LearningMaterial $learningMaterial)
    {
        return view('admin.learning-materials.form', [
            'learningMaterial' => $learningMaterial,
            'subjects' => Subject::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(LearningMaterialRequest $request, LearningMaterial $learningMaterial, LearningMaterialService $service)
    {
        $service->update($learningMaterial, $request->safe()->except('pdf'), $request->file('pdf'));

        return redirect()->route('admin.learning-materials.index')->with('success', 'Materi pembelajaran berhasil diperbarui.');
    }

    public function destroy(LearningMaterial $learningMaterial, LearningMaterialService $service)
    {
        $service->delete($learningMaterial);

        return back()->with('success', 'Materi pembelajaran berhasil dihapus.');
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
