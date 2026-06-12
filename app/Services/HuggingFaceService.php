<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class HuggingFaceService
{
    private ?string $lastError = null;

    public function generateQuestions(string $material, string $difficulty = 'medium', int $count = 5): array
    {
        $this->ensureConfigured();

        $prompt = <<<PROMPT
Buat tepat {$count} soal pilihan ganda tingkat {$difficulty} tentang "{$material}" dalam Bahasa Indonesia.

Aturan wajib:
- Setiap pertanyaan menguji aspek yang berbeda dan bukan variasi kalimat dari soal lain.
- Semua opsi harus konkret, masuk akal, berbeda, dan relevan dengan pertanyaan.
- Posisi jawaban benar harus bervariasi antara A, B, C, dan D.
- Hindari opsi generik seperti "semua benar", "konsep utama", atau "tidak berkaitan".
- Pembahasan menjelaskan alasan jawaban benar secara spesifik.
PROMPT;
        $data = $this->request($prompt, $this->questionSchema($count));
        $questions = $this->normalizeQuestions($data, $count);

        if (count($questions) === $count) {
            return $questions;
        }

        throw new RuntimeException($this->lastError ?? 'Hugging Face tidak menghasilkan soal yang valid dan unik. Coba materi yang lebih spesifik.');
    }

    public function generateExplanation(string $question, string $answer): string
    {
        if (! config('services.huggingface.token')) {
            return 'Pembahasan belum tersedia.';
        }

        return $this->text("Jelaskan secara ringkas dalam Bahasa Indonesia mengapa jawaban '{$answer}' benar untuk soal: {$question}")
            ?? 'Pembahasan belum tersedia.';
    }

    public function generateRecommendation(string $subject, float $score): string
    {
        if (! config('services.huggingface.token')) {
            return $this->fallbackRecommendation($subject, $score);
        }

        return $this->text("Beri rekomendasi belajar singkat dalam Markdown Bahasa Indonesia untuk siswa dengan nilai {$score} pada {$subject}. Batasi maksimal 180 kata. Gunakan judul pendek dan bullet list; jangan gunakan tabel.")
            ?? $this->fallbackRecommendation($subject, $score);
    }

    public function assistantReply(User $user, array $history, string $message): string
    {
        $this->ensureConfigured('aRBi Assistant belum aktif karena HUGGINGFACE_TOKEN belum diisi.');

        $average = round((float) $user->results()->avg('score'), 1);
        $recent = $user->results()->with('tryout.subject')->latest()->limit(3)->get()
            ->map(fn ($result) => ($result->tryout->subject?->name ?? $result->tryout->title).": {$result->score}")
            ->join(', ');
        $conversation = collect($history)->take(-10)
            ->map(fn ($item) => strtoupper($item['role']).': '.$item['content'])
            ->join("\n");
        $prompt = <<<PROMPT
Anda adalah aRBi Assistant, tutor belajar ramah untuk siswa Indonesia.
Jawab dengan jelas, ringkas, bertahap, dan sesuai usia siswa. Batasi jawaban normal maksimal 250 kata kecuali siswa meminta penjelasan lebih panjang. Gunakan Markdown dengan judul pendek dan bullet list bila membantu; hindari tabel kecuali benar-benar diperlukan. Bantu siswa memahami konsep, tetapi jangan langsung mengerjakan tugas atau ujian tanpa penjelasan. Gunakan Bahasa Indonesia.

Konteks siswa:
- Nama: {$user->name}
- Nilai rata-rata: {$average}
- Hasil terbaru: {$recent}

Riwayat percakapan:
{$conversation}

Pertanyaan siswa:
{$message}
PROMPT;
        $reply = $this->text($prompt);

        if (! $reply) {
            throw new RuntimeException($this->lastError ?? 'aRBi Assistant belum dapat menjawab. Coba kembali beberapa saat lagi.');
        }

        return $reply;
    }

    private function text(string $prompt): ?string
    {
        $response = $this->request($prompt);

        return is_string($response) && $response !== '' ? $response : null;
    }

    private function request(string $prompt, ?array $schema = null): array|string|null
    {
        $this->ensureConfigured();

        $payload = [
            'model' => config('services.huggingface.model'),
            'messages' => [
                ['role' => 'system', 'content' => 'Anda adalah asisten pendidikan aRBiPi. Selalu ikuti format dan bahasa yang diminta.'],
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => 0.35,
            'max_tokens' => config('services.huggingface.max_tokens'),
        ];

        if ($schema) {
            $payload['response_format'] = [
                'type' => 'json_schema',
                'json_schema' => [
                    'name' => 'generated_questions',
                    'strict' => true,
                    'schema' => $schema,
                ],
            ];
        }

        try {
            $response = Http::withToken(config('services.huggingface.token'))
                ->acceptJson()
                ->connectTimeout(config('services.huggingface.connect_timeout'))
                ->timeout(config('services.huggingface.timeout'))
                ->post(config('services.huggingface.url'), $payload)
                ->throw()
                ->json();
            $content = data_get($response, 'choices.0.message.content');

            if (! is_string($content) || trim($content) === '') {
                $this->lastError = 'Hugging Face tidak mengembalikan teks dari model.';

                return null;
            }

            if (! $schema) {
                return trim($content);
            }

            $decoded = json_decode($this->stripCodeFence($content), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->lastError = 'Model mengembalikan format soal yang tidak dapat dibaca.';

                return null;
            }

            return $decoded;
        } catch (ConnectionException $exception) {
            $this->lastError = 'Hugging Face terlalu lama merespons. Tunggu sebentar lalu coba kembali.';
            $this->logFailure('timeout', $exception->getMessage());
        } catch (RequestException $exception) {
            $status = $exception->response->status();
            $this->lastError = match ($status) {
                400, 422 => 'Permintaan Hugging Face ditolak. Periksa model dan format konfigurasi.',
                401, 403 => 'Hugging Face token tidak valid atau tidak memiliki izin Inference Providers.',
                402 => 'Saldo atau kredit Hugging Face Inference Providers tidak mencukupi.',
                404 => 'Model atau provider Hugging Face tidak ditemukan.',
                429 => 'Rate limit Hugging Face tercapai. Coba kembali nanti.',
                503 => 'Provider Hugging Face sedang sibuk. Silakan coba beberapa saat lagi.',
                default => "Hugging Face gagal merespons (HTTP {$status}).",
            };
            $this->logFailure((string) $status, str($exception->response->body())->limit(500)->toString());
        } catch (\Throwable $exception) {
            $this->lastError = $exception->getMessage();
            $this->logFailure('error', $exception->getMessage());
        }

        return null;
    }

    private function ensureConfigured(string $message = 'Hugging Face token belum diisi. Isi HUGGINGFACE_TOKEN pada file .env.'): void
    {
        if (! config('services.huggingface.token')) {
            throw new RuntimeException($message);
        }
    }

    private function stripCodeFence(string $content): string
    {
        return trim(preg_replace('/^```(?:json)?\s*|\s*```$/i', '', trim($content)));
    }

    private function logFailure(string $type, string $error): void
    {
        Log::warning('Hugging Face API request failed', [
            'model' => config('services.huggingface.model'),
            'type' => $type,
            'error' => $error,
        ]);
    }

    private function questionSchema(int $count): array
    {
        return [
            'type' => 'object',
            'additionalProperties' => false,
            'properties' => [
                'questions' => [
                    'type' => 'array',
                    'minItems' => $count,
                    'maxItems' => $count,
                    'items' => [
                        'type' => 'object',
                        'additionalProperties' => false,
                        'properties' => [
                            'question' => ['type' => 'string'],
                            'options' => [
                                'type' => 'object',
                                'additionalProperties' => false,
                                'properties' => collect(['A', 'B', 'C', 'D'])->mapWithKeys(fn ($label) => [$label => ['type' => 'string']])->all(),
                                'required' => ['A', 'B', 'C', 'D'],
                            ],
                            'correct_answer' => ['type' => 'string', 'enum' => ['A', 'B', 'C', 'D']],
                            'explanation' => ['type' => 'string'],
                        ],
                        'required' => ['question', 'options', 'correct_answer', 'explanation'],
                    ],
                ],
            ],
            'required' => ['questions'],
        ];
    }

    private function fallbackRecommendation(string $subject, float $score): string
    {
        return match (true) {
            $score >= 85 => "Pertahankan performa {$subject}. Fokus pada latihan soal tingkat lanjut dan evaluasi kesalahan kecil.",
            $score >= 70 => "Pemahaman {$subject} sudah baik. Ulangi topik yang masih salah lalu kerjakan latihan bertahap.",
            default => "Bangun kembali konsep dasar {$subject}, buat jadwal belajar singkat harian, lalu ulangi latihan dari tingkat mudah.",
        };
    }

    private function normalizeQuestions(array|string|null $data, int $count): array
    {
        $items = is_array($data) ? ($data['questions'] ?? $data) : [];
        $normalized = collect($items)->take($count)->map(function ($item) {
            if (! is_array($item)) {
                return null;
            }

            $options = collect(['A', 'B', 'C', 'D'])->mapWithKeys(fn ($label) => [$label => (string) ($item['options'][$label] ?? '')])->all();
            $answer = strtoupper((string) ($item['correct_answer'] ?? ''));
            $normalizedOptions = collect($options)->map(fn ($option) => mb_strtolower(trim($option)))->all();

            if (! isset($options[$answer]) || in_array('', $options, true) || count(array_unique($normalizedOptions)) !== 4) {
                return null;
            }

            return [
                'question' => (string) ($item['question'] ?? ''),
                'options' => $options,
                'correct_answer' => $answer,
                'explanation' => (string) ($item['explanation'] ?? ''),
            ];
        })->filter(fn ($item) => $item && mb_strlen(trim($item['question'])) >= 10)->values();

        return $normalized->unique(fn ($item) => mb_strtolower(preg_replace('/\s+/', ' ', trim($item['question']))))->take($count)->values()->all();
    }
}
