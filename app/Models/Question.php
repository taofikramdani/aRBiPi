<?php

namespace App\Models;

use Database\Factories\QuestionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    /** @use HasFactory<QuestionFactory> */
    use HasFactory;

    protected $fillable = ['subject_id', 'created_by', 'difficulty', 'question_text', 'explanation', 'is_ai_generated'];

    protected function casts(): array
    {
        return ['is_ai_generated' => 'boolean'];
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function options()
    {
        return $this->hasMany(Option::class)->orderBy('label');
    }

    public function tryouts()
    {
        return $this->belongsToMany(Tryout::class, 'tryout_questions')->withPivot(['order', 'points']);
    }
}
