<?php

namespace App\Models;

use Database\Factories\TryoutFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tryout extends Model
{
    /** @use HasFactory<TryoutFactory> */
    use HasFactory;

    protected $fillable = ['subject_id', 'created_by', 'title', 'description', 'duration_minutes', 'starts_at', 'ends_at', 'is_published'];

    protected function casts(): array
    {
        return ['starts_at' => 'datetime', 'ends_at' => 'datetime', 'is_published' => 'boolean'];
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function questions()
    {
        return $this->belongsToMany(Question::class, 'tryout_questions')->withPivot(['order', 'points'])->orderByPivot('order');
    }

    public function attempts()
    {
        return $this->hasMany(Attempt::class);
    }
}
