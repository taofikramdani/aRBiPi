<?php

namespace App\Models;

use Database\Factories\ResultFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Result extends Model
{
    /** @use HasFactory<ResultFactory> */
    use HasFactory;

    protected $fillable = ['attempt_id', 'user_id', 'tryout_id', 'correct_answers', 'wrong_answers', 'unanswered', 'score'];

    protected function casts(): array
    {
        return ['score' => 'decimal:2'];
    }

    public function attempt()
    {
        return $this->belongsTo(Attempt::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tryout()
    {
        return $this->belongsTo(Tryout::class);
    }

    public function recommendation()
    {
        return $this->hasOne(AiRecommendation::class);
    }
}
