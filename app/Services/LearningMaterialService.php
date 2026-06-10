<?php

namespace App\Services;

use App\Models\LearningMaterial;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class LearningMaterialService
{
    public function create(array $data, UploadedFile $pdf, int $userId): LearningMaterial
    {
        $path = $pdf->store('learning-materials', 'local');

        return LearningMaterial::create($this->attributes($data, $pdf, $path) + ['uploaded_by' => $userId]);
    }

    public function update(LearningMaterial $material, array $data, ?UploadedFile $pdf): LearningMaterial
    {
        $attributes = $data;

        if ($pdf) {
            $newPath = $pdf->store('learning-materials', 'local');
            Storage::disk('local')->delete($material->file_path);
            $attributes = $this->attributes($data, $pdf, $newPath);
        }

        $material->update($attributes);

        return $material;
    }

    public function delete(LearningMaterial $material): void
    {
        Storage::disk('local')->delete($material->file_path);
        $material->delete();
    }

    private function attributes(array $data, UploadedFile $pdf, string $path): array
    {
        return $data + [
            'file_path' => $path,
            'original_name' => $pdf->getClientOriginalName(),
            'file_size' => $pdf->getSize(),
        ];
    }
}
