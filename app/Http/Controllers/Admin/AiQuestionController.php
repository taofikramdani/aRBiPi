<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateQuestionsJob;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AiQuestionController extends Controller
{
    /**
     * Dispatch job generate soal ke queue, langsung redirect tanpa tunggu API.
     */
    public function __invoke(Request $r)
    {
        $d = $r->validate([
            'subject_id' => ['required', 'exists:subjects,id'],
            'material' => ['required', 'string', 'max:500'],
            'difficulty' => ['required', 'in:easy,medium,hard'],
            'count' => ['required', 'integer', 'in:1,5,10'],
        ]);

        // Bersihkan draft & job lama
        $r->session()->forget('ai_question_draft');
        $r->session()->forget('ai_job_key');
        $r->session()->forget('ai_generation_error');

        $jobKey = 'ai_job_'.$r->user()->id.'_'.time();
        Cache::put($jobKey, ['status' => 'pending', 'created_at' => now()->timestamp], now()->addMinutes(15));
        $r->session()->put('ai_job_key', $jobKey);

        GenerateQuestionsJob::dispatch(
            $jobKey,
            (int) $d['subject_id'],
            $d['material'],
            $d['difficulty'],
            (int) $d['count'],
        );

        return back()->with('ai_generating', "Sedang membuat {$d['count']} soal tentang \"{$d['material']}\"...");
    }

    /**
     * Endpoint polling: cek status job dari cache.
     */
    public function poll(Request $r)
    {
        $jobKey = $r->session()->get('ai_job_key');

        if (! $jobKey) {
            return response()->json(['status' => 'none']);
        }

        $result = Cache::get($jobKey);

        if (! $result) {
            $r->session()->forget('ai_job_key');
            $error = 'Status proses AI sudah kedaluwarsa. Generate ulang dan pastikan queue worker tetap berjalan.';
            $r->session()->flash('ai_generation_error', $error);

            return response()->json(['status' => 'expired', 'error' => $error]);
        }

        if ($result['status'] === 'done') {
            // Pindahkan hasil ke session draft (format yang sudah dipakai oleh store & view)
            $r->session()->put('ai_question_draft', [
                'subject_id' => $result['subject_id'],
                'material' => $result['material'],
                'difficulty' => $result['difficulty'],
                'questions' => $result['questions'],
            ]);
            $r->session()->forget('ai_job_key');
            Cache::forget($jobKey);

            return response()->json([
                'status' => 'done',
                'count' => count($result['questions']),
            ]);
        }

        if ($result['status'] === 'failed') {
            $error = $this->normalizeProviderError($result['error']);
            $r->session()->forget('ai_job_key');
            Cache::forget($jobKey);
            $r->session()->flash('ai_generation_error', $error);

            return response()->json([
                'status' => 'failed',
                'error' => $error,
            ]);
        }

        if (($result['created_at'] ?? now()->timestamp) < now()->subMinutes(3)->timestamp) {
            $r->session()->forget('ai_job_key');
            Cache::forget($jobKey);
            $error = 'Proses AI berhenti terlalu lama. Pastikan queue worker berjalan, lalu generate ulang.';
            $r->session()->flash('ai_generation_error', $error);

            return response()->json(['status' => 'failed', 'error' => $error]);
        }

        return response()->json(['status' => 'pending']);
    }

    public function store(Request $request)
    {
        $draft = $request->session()->get('ai_question_draft');
        abort_unless(is_array($draft) && ! empty($draft['questions']), 422, 'Draft soal AI tidak ditemukan.');
        $questions = collect($draft['questions']);
        $uniqueQuestions = $questions->unique(fn ($question) => mb_strtolower(trim($question['question'] ?? '')));
        $hasInvalidOptions = $questions->contains(function ($question) {
            $options = collect($question['options'] ?? [])->map(fn ($option) => mb_strtolower(trim($option)));

            return $options->count() !== 4
                || $options->unique()->count() !== 4
                || $options->contains(fn ($option) => str_contains($option, 'konsep utama'));
        });
        if ($uniqueQuestions->count() !== $questions->count() || $hasInvalidOptions) {
            $request->session()->forget('ai_question_draft');

            return back()->withErrors(['ai' => 'Draft ditolak karena memiliki pertanyaan duplikat atau opsi jawaban tidak valid. Silakan generate ulang.']);
        }

        $saved = DB::transaction(function () use ($draft, $request) {
            return collect($draft['questions'])->map(function (array $item) use ($draft, $request) {
                $question = Question::create([
                    'subject_id' => $draft['subject_id'],
                    'created_by' => $request->user()->id,
                    'difficulty' => $draft['difficulty'],
                    'question_text' => $item['question'],
                    'explanation' => $item['explanation'],
                    'is_ai_generated' => true,
                ]);
                foreach ($item['options'] as $label => $text) {
                    $question->options()->create([
                        'label' => $label,
                        'option_text' => $text,
                        'is_correct' => $label === $item['correct_answer'],
                    ]);
                }

                return $question;
            })->count();
        });
        $request->session()->forget('ai_question_draft');

        return redirect()->route('admin.questions.index')->with('success', "{$saved} soal AI berhasil disimpan ke bank soal.");
    }

    public function discard(Request $request)
    {
        $request->session()->forget('ai_question_draft');
        $request->session()->forget('ai_job_key');

        return back()->with('success', 'Draft soal AI dibuang.');
    }

    private function normalizeProviderError(string $error): string
    {
        if (str_contains(strtolower($error), 'gemini')) {
            return 'Proses sebelumnya menggunakan konfigurasi AI lama. Silakan generate ulang menggunakan Hugging Face.';
        }

        return $error;
    }
}
