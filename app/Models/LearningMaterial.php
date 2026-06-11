<?php

namespace App\Models;

use Database\Factories\LearningMaterialFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LearningMaterial extends Model
{
    /** @use HasFactory<LearningMaterialFactory> */
    use HasFactory;

    protected $fillable = [
        'subject_id',
        'uploaded_by',
        'title',
        'description',
        'storage_disk',
        'file_path',
        'file_url',
        'original_name',
        'file_size',
        'is_published',
    ];

    protected function casts(): array
    {
        return ['is_published' => 'boolean'];
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getFormattedSizeAttribute(): string
    {
        return $this->file_size >= 1048576
            ? number_format($this->file_size / 1048576, 1).' MB'
            : number_format($this->file_size / 1024, 0).' KB';
    }
}
