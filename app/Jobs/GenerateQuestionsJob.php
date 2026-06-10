<?php

namespace App\Jobs;

use App\Services\HuggingFaceService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Throwable;

class GenerateQuestionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Waktu maksimum job boleh berjalan (detik) */
    public int $timeout = 120;

    /** Tidak retry otomatis — biarkan user generate ulang jika gagal */
    public int $tries = 1;

    public function __construct(
        public readonly string $jobKey,
        public readonly int $subjectId,
        public readonly string $material,
        public readonly string $difficulty,
        public readonly int $count,
    ) {}

    public function handle(HuggingFaceService $ai): void
    {
        Cache::put($this->jobKey, ['status' => 'processing', 'created_at' => now()->timestamp], now()->addMinutes(15));

        try {
            $questions = $ai->generateQuestions($this->material, $this->difficulty, $this->count);

            Cache::put($this->jobKey, [
                'status' => 'done',
                'subject_id' => $this->subjectId,
                'material' => $this->material,
                'difficulty' => $this->difficulty,
                'questions' => $questions,
            ], now()->addHour());
        } catch (Throwable $e) {
            Cache::put($this->jobKey, [
                'status' => 'failed',
                'error' => $e->getMessage(),
            ], now()->addMinutes(10));
        }
    }

    public function failed(?Throwable $exception): void
    {
        Cache::put($this->jobKey, [
            'status' => 'failed',
            'error' => $exception?->getMessage() ?? 'Job generate soal gagal dijalankan.',
        ], now()->addMinutes(10));
    }
}
