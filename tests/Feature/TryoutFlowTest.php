<?php

namespace Tests\Feature;

use App\Models\Question;
use App\Models\Subject;
use App\Models\Tryout;
use App\Models\User;
use App\Services\HuggingFaceService;
use App\Services\TryoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TryoutFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_attempt_page_shows_timer_without_student_assistant(): void
    {
        Role::findOrCreate('student');
        $student = User::factory()->create();
        $student->assignRole('student');
        $subject = Subject::create(['name' => 'Matematika', 'slug' => 'matematika']);
        $tryout = Tryout::create([
            'subject_id' => $subject->id,
            'title' => 'Tes Berwaktu',
            'duration_minutes' => 15,
            'is_published' => true,
        ]);
        $attempt = app(TryoutService::class)->start($tryout, $student);

        $this->actingAs($student)
            ->get(route('student.attempts.show', $attempt))
            ->assertOk()
            ->assertSee('Waktu Pengerjaan')
            ->assertSee('Durasi 15 menit')
            ->assertSee('tryoutTimer', false)
            ->assertDontSee('aRBi Assistant');
    }

    public function test_student_can_submit_tryout_and_receive_result_and_recommendation(): void
    {
        Role::findOrCreate('student');
        $student = User::factory()->create();
        $student->assignRole('student');
        $subject = Subject::create(['name' => 'Matematika', 'slug' => 'matematika']);
        $question = Question::create(['subject_id' => $subject->id, 'difficulty' => 'easy', 'question_text' => '1 + 1 = ?']);
        $correct = $question->options()->create(['label' => 'A', 'option_text' => '2', 'is_correct' => true]);
        $question->options()->create(['label' => 'B', 'option_text' => '3', 'is_correct' => false]);
        $tryout = Tryout::create(['subject_id' => $subject->id, 'title' => 'Tes Dasar', 'duration_minutes' => 10, 'is_published' => true]);
        $tryout->questions()->attach($question, ['order' => 1, 'points' => 1]);
        $this->mock(HuggingFaceService::class, fn ($mock) => $mock->shouldReceive('generateRecommendation')
            ->once()
            ->andReturn('Terus berlatih.'));

        $service = app(TryoutService::class);
        $attempt = $service->start($tryout, $student);
        $result = $service->submit($attempt, [$question->id => $correct->id]);

        $this->assertEquals(100, $result->score);
        $this->assertDatabaseHas('answers', ['attempt_id' => $attempt->id, 'is_correct' => true]);
        $this->assertDatabaseHas('ai_recommendations', ['result_id' => $result->id]);
    }
}
