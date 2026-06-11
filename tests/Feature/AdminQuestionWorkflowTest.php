<?php

namespace Tests\Feature;

use App\Jobs\GenerateQuestionsJob;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminQuestionWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_generate_ten_questions_and_save_them_in_bulk(): void
    {
        Role::findOrCreate('admin');
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $subject = Subject::create(['name' => 'IPA', 'slug' => 'ipa']);
        $questions = collect(range(1, 10))->map(fn ($number) => [
            'question' => "Pertanyaan unik tata surya nomor {$number}?",
            'options' => ['A' => "Jawaban A {$number}", 'B' => "Jawaban B {$number}", 'C' => "Jawaban C {$number}", 'D' => "Jawaban D {$number}"],
            'correct_answer' => ['A', 'B', 'C', 'D'][$number % 4],
            'explanation' => "Pembahasan spesifik nomor {$number}.",
        ])->all();

        $this->actingAs($admin)->withSession(['ai_question_draft' => [
            'subject_id' => $subject->id,
            'material' => 'Sistem tata surya',
            'difficulty' => 'medium',
            'questions' => $questions,
        ]])->post(route('admin.ai.questions.store'))
            ->assertRedirect(route('admin.questions.index'));

        $this->assertDatabaseCount('questions', 10);
        $this->assertDatabaseCount('options', 40);
        $this->assertDatabaseHas('questions', [
            'subject_id' => $subject->id,
            'difficulty' => 'medium',
            'is_ai_generated' => true,
        ]);
    }

    public function test_generation_is_dispatched_to_queue(): void
    {
        Queue::fake();
        Role::findOrCreate('admin');
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $subject = Subject::create(['name' => 'Informatika', 'slug' => 'informatika']);
        $this->actingAs($admin)->post(route('admin.ai.questions'), [
            'subject_id' => $subject->id,
            'material' => 'Containerization',
            'difficulty' => 'hard',
            'count' => 5,
        ])->assertSessionHas('ai_job_key')->assertSessionHas('ai_generating');

        Queue::assertPushed(GenerateQuestionsJob::class);
    }

    public function test_starting_generation_clears_old_provider_error(): void
    {
        Queue::fake();
        Role::findOrCreate('admin');
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $subject = Subject::create(['name' => 'Informatika', 'slug' => 'informatika']);

        $this->actingAs($admin)->withSession(['ai_generation_error' => 'Kuota Gemini API habis.'])
            ->post(route('admin.ai.questions'), [
                'subject_id' => $subject->id,
                'material' => 'Containerization',
                'difficulty' => 'hard',
                'count' => 5,
            ])
            ->assertSessionMissing('ai_generation_error')
            ->assertSessionHas('ai_job_key');
    }

    public function test_question_bank_groups_questions_by_subject(): void
    {
        Role::findOrCreate('admin');
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        Subject::create(['name' => 'Matematika', 'slug' => 'matematika']);
        Subject::create(['name' => 'IPA', 'slug' => 'ipa']);

        $this->actingAs($admin)->get(route('admin.questions.index'))
            ->assertOk()
            ->assertSee('Koleksi Soal per Mata Pelajaran')
            ->assertSee('Matematika')
            ->assertSee('IPA')
            ->assertSee('Generate 10 Soal');
    }

    public function test_question_polling_uses_a_relative_url(): void
    {
        Role::findOrCreate('admin');
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)->get(route('admin.questions.index'))
            ->assertOk()
            ->assertSee("const pollUrl = '/admin/ai/questions/poll';", false)
            ->assertDontSee("const pollUrl = 'http://", false);
    }

    public function test_failed_generation_poll_preserves_error_for_page_reload(): void
    {
        Role::findOrCreate('admin');
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $jobKey = 'ai_job_test_failed';
        Cache::put($jobKey, ['status' => 'failed', 'error' => 'Rate limit Hugging Face tercapai.']);

        $this->actingAs($admin)->withSession(['ai_job_key' => $jobKey])
            ->getJson(route('admin.ai.questions.poll'))
            ->assertOk()
            ->assertJson(['status' => 'failed', 'error' => 'Rate limit Hugging Face tercapai.'])
            ->assertSessionHas('ai_generation_error', 'Rate limit Hugging Face tercapai.');
    }

    public function test_expired_generation_status_is_explained(): void
    {
        Role::findOrCreate('admin');
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)->withSession(['ai_job_key' => 'missing-job'])
            ->getJson(route('admin.ai.questions.poll'))
            ->assertOk()
            ->assertJson(['status' => 'expired'])
            ->assertSessionHas('ai_generation_error');
    }

    public function test_question_bank_removes_legacy_gemini_error(): void
    {
        Role::findOrCreate('admin');
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)->withSession(['ai_generation_error' => 'Kuota Gemini API habis.'])
            ->get(route('admin.questions.index'))
            ->assertOk()
            ->assertDontSee('Gemini')
            ->assertSessionMissing('ai_generation_error');
    }
}
