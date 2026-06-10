<?php

namespace Tests\Unit;

use App\Services\HuggingFaceService;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class HuggingFaceServiceTest extends TestCase
{
    private function configureHuggingFace(): void
    {
        config([
            'services.huggingface.token' => 'test-token',
            'services.huggingface.model' => 'openai/gpt-oss-120b:novita',
            'services.huggingface.url' => 'https://router.huggingface.co/v1/chat/completions',
            'services.huggingface.timeout' => 90,
            'services.huggingface.connect_timeout' => 10,
            'services.huggingface.max_tokens' => 8192,
        ]);
    }

    public function test_it_uses_hugging_face_openai_compatible_structured_output(): void
    {
        $this->configureHuggingFace();
        Http::fake(['*' => Http::response([
            'choices' => [['message' => ['content' => json_encode([
                'questions' => [[
                    'question' => 'Berapakah hasil dari satu ditambah satu?',
                    'options' => ['A' => '2', 'B' => '3', 'C' => '4', 'D' => '5'],
                    'correct_answer' => 'A',
                    'explanation' => 'Satu ditambah satu adalah dua.',
                ]],
            ])]]],
        ])]);

        $questions = app(HuggingFaceService::class)->generateQuestions('Penjumlahan', 'easy', 1);

        $this->assertSame('Berapakah hasil dari satu ditambah satu?', $questions[0]['question']);
        Http::assertSent(fn ($request) => $request->url() === 'https://router.huggingface.co/v1/chat/completions'
            && $request['model'] === 'openai/gpt-oss-120b:novita'
            && $request['response_format']['type'] === 'json_schema'
            && $request->hasHeader('Authorization', 'Bearer test-token'));
    }

    public function test_it_rejects_duplicate_invalid_questions(): void
    {
        $this->configureHuggingFace();
        $duplicate = ['question' => 'Pertanyaan yang sama?', 'options' => ['A' => 'Sama', 'B' => 'Sama', 'C' => 'Sama', 'D' => 'Sama'], 'correct_answer' => 'A', 'explanation' => 'Tidak valid.'];
        Http::fake(['*' => Http::response(['choices' => [['message' => ['content' => json_encode(['questions' => array_fill(0, 5, $duplicate)])]]]])]);

        $this->expectException(RuntimeException::class);
        app(HuggingFaceService::class)->generateQuestions('Containerization', 'hard', 5);
    }

    public function test_it_explains_invalid_token(): void
    {
        $this->configureHuggingFace();
        Http::fake(['*' => Http::response(['error' => 'invalid token'], 403)]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('token tidak valid');
        app(HuggingFaceService::class)->generateQuestions('Containerization', 'hard', 5);
    }

    public function test_recommendation_uses_local_fallback_without_token(): void
    {
        config(['services.huggingface.token' => null]);

        $recommendation = app(HuggingFaceService::class)->generateRecommendation('Matematika', 60);

        $this->assertStringContainsString('konsep dasar Matematika', $recommendation);
        Http::assertNothingSent();
    }
}
