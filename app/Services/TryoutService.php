<?php

namespace App\Services;

use App\Models\AiRecommendation;
use App\Models\Answer;
use App\Models\Attempt;
use App\Models\Option;
use App\Models\Result;
use App\Models\Tryout;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TryoutService
{
    public function __construct(private HuggingFaceService $ai) {}

    public function start(Tryout $tryout, User $user): Attempt
    {
        return Attempt::firstOrCreate(
            ['tryout_id' => $tryout->id, 'user_id' => $user->id, 'status' => 'in_progress'],
            ['started_at' => now()]
        );
    }

    public function submit(Attempt $attempt, array $responses): Result
    {
        return DB::transaction(function () use ($attempt, $responses) {
            $attempt->load('tryout.questions.options');
            $correct = 0;
            $wrong = 0;
            $unanswered = 0;
            foreach ($attempt->tryout->questions as $question) {
                $optionId = $responses[$question->id] ?? null;
                $option = $optionId ? Option::where('question_id', $question->id)->find($optionId) : null;
                $isCorrect = (bool) ($option?->is_correct);
                $option ? ($isCorrect ? $correct++ : $wrong++) : $unanswered++;
                Answer::updateOrCreate(
                    ['attempt_id' => $attempt->id, 'question_id' => $question->id],
                    ['option_id' => $option?->id, 'is_correct' => $isCorrect]
                );
            }
            $total = max(1, $attempt->tryout->questions->count());
            $result = Result::updateOrCreate(['attempt_id' => $attempt->id], [
                'user_id' => $attempt->user_id, 'tryout_id' => $attempt->tryout_id,
                'correct_answers' => $correct, 'wrong_answers' => $wrong, 'unanswered' => $unanswered,
                'score' => round($correct / $total * 100, 2),
            ]);
            $attempt->update(['status' => 'submitted', 'submitted_at' => now()]);
            AiRecommendation::updateOrCreate(['result_id' => $result->id], [
                'user_id' => $attempt->user_id, 'subject_id' => $attempt->tryout->subject_id,
                'recommendation' => $this->ai->generateRecommendation($attempt->tryout->subject?->name ?? 'materi tryout', (float) $result->score),
                'model' => config('services.huggingface.model'),
            ]);

            return $result;
        });
    }
}
