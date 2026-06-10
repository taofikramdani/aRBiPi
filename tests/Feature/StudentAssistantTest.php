<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\HuggingFaceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StudentAssistantTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_chat_with_assistant_and_view_history(): void
    {
        Role::findOrCreate('student');
        $student = User::factory()->create();
        $student->assignRole('student');
        $this->mock(HuggingFaceService::class, fn ($mock) => $mock->shouldReceive('assistantReply')
            ->once()
            ->andReturn('Container adalah paket aplikasi beserta dependensinya.'));

        $this->actingAs($student)->postJson(route('student.assistant.store'), [
            'message' => 'Apa itu container?',
        ])->assertOk()->assertJsonPath('assistant_message.role', 'assistant');

        $this->actingAs($student)->getJson(route('student.assistant.index'))
            ->assertOk()
            ->assertJsonCount(2, 'messages')
            ->assertJsonPath('messages.0.role', 'user')
            ->assertJsonPath('messages.1.role', 'assistant');
    }

    public function test_student_can_clear_assistant_history(): void
    {
        Role::findOrCreate('student');
        $student = User::factory()->create();
        $student->assignRole('student');
        $student->assistantMessages()->createMany([
            ['role' => 'user', 'content' => 'Halo'],
            ['role' => 'assistant', 'content' => 'Halo juga'],
        ]);

        $this->actingAs($student)->deleteJson(route('student.assistant.destroy'))->assertOk();

        $this->assertDatabaseCount('assistant_messages', 0);
    }

    public function test_admin_cannot_access_student_assistant(): void
    {
        Role::findOrCreate('admin');
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)->getJson(route('student.assistant.index'))->assertForbidden();
    }
}
