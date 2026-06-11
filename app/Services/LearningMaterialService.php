<?php

namespace App\Services;

use App\Models\LearningMaterial;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class LearningMaterialService
{
    public function create(array $data, UploadedFile $pdf, int $userId): LearningMaterial
    {
        $disk = $this->disk();
        $this->ensureConfigured($disk);
        $path = $pdf->store('learning-materials', $disk);

        return LearningMaterial::create($this->attributes($data, $pdf, $disk, $path) + ['uploaded_by' => $userId]);
    }

    public function update(LearningMaterial $material, array $data, ?UploadedFile $pdf): LearningMaterial
    {
        $attributes = $data;

        if ($pdf) {
            $disk = $this->disk();
            $this->ensureConfigured($disk);
            $newPath = $pdf->store('learning-materials', $disk);
            Storage::disk($material->storage_disk)->delete($material->file_path);
            $attributes = $this->attributes($data, $pdf, $disk, $newPath);
        }

        $material->update($attributes);

        return $material;
    }

    public function delete(LearningMaterial $material): void
    {
        Storage::disk($material->storage_disk)->delete($material->file_path);
        $material->delete();
    }

    private function attributes(array $data, UploadedFile $pdf, string $disk, string $path): array
    {
        return $data + [
            'storage_disk' => $disk,
            'file_path' => $path,
            'file_url' => $this->url($disk, $path),
            'original_name' => $pdf->getClientOriginalName(),
            'file_size' => $pdf->getSize(),
        ];
    }

    private function disk(): string
    {
        return config('filesystems.materials_disk', 'local');
    }

    private function url(string $disk, string $path): ?string
    {
        if ($disk !== 'azure') {
            return null;
        }

        return implode('/', array_filter([
            rtrim(config('filesystems.disks.azure.url'), '/'),
            trim(config('filesystems.disks.azure.prefix', ''), '/'),
            ltrim($path, '/'),
        ]));
    }

    private function ensureConfigured(string $disk): void
    {
        if ($disk === 'azure' && blank(config('filesystems.disks.azure.url'))) {
            throw new \RuntimeException('AZURE_STORAGE_URL wajib diisi saat MATERIAL_FILESYSTEM_DISK=azure.');
        }
    }
}
