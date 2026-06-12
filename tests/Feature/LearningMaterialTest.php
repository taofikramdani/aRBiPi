<?php

namespace Tests\Feature;

use App\Models\LearningMaterial;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LearningMaterialTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        config(['filesystems.materials_disk' => 'local']);
        Role::findOrCreate('admin');
        Role::findOrCreate('student');
    }

    public function test_admin_can_upload_pdf_material(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $subject = Subject::factory()->create(['name' => 'Komputasi Awan']);

        $this->actingAs($admin)->post(route('admin.learning-materials.store'), [
            'subject_id' => $subject->id,
            'title' => 'Docker',
            'description' => 'Pengenalan Docker.',
            'pdf' => UploadedFile::fake()->create('Docker.pdf', 100, 'application/pdf'),
            'is_published' => '1',
        ])->assertRedirect(route('admin.learning-materials.index'));

        $material = LearningMaterial::first();
        $this->assertSame('Docker.pdf', $material->original_name);
        $this->assertSame('local', $material->storage_disk);
        $this->assertNull($material->file_url);
        Storage::disk('local')->assertExists($material->file_path);
    }

    public function test_student_only_sees_and_opens_published_material(): void
    {
        $student = User::factory()->create();
        $student->assignRole('student');
        $subject = Subject::factory()->create(['name' => 'Komputasi Awan']);
        Storage::disk('local')->put('learning-materials/docker.pdf', '%PDF-1.4 test');
        Storage::disk('local')->put('learning-materials/draft.pdf', '%PDF-1.4 test');
        $published = LearningMaterial::factory()->create([
            'subject_id' => $subject->id,
            'title' => 'Docker',
            'file_path' => 'learning-materials/docker.pdf',
            'is_published' => true,
        ]);
        $draft = LearningMaterial::factory()->create([
            'subject_id' => $subject->id,
            'title' => 'Draft Internal',
            'file_path' => 'learning-materials/draft.pdf',
            'is_published' => false,
        ]);

        $this->actingAs($student)->get(route('student.learning-materials.index'))
            ->assertOk()
            ->assertSee('Komputasi Awan')
            ->assertSee('Docker')
            ->assertDontSee('Draft Internal');

        $this->actingAs($student)->get(route('student.learning-materials.open', $published))->assertOk();
        $this->actingAs($student)->get(route('student.learning-materials.open', $draft))->assertForbidden();
    }

    public function test_upload_to_azure_disk_saves_file_url(): void
    {
        Storage::fake('azure');
        config([
            'filesystems.materials_disk' => 'azure',
            'filesystems.disks.azure.url' => 'https://account.blob.core.windows.net/materials',
            'filesystems.disks.azure.prefix' => 'arbipi',
        ]);
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $subject = Subject::factory()->create();

        $this->actingAs($admin)->post(route('admin.learning-materials.store'), [
            'subject_id' => $subject->id,
            'title' => 'Azure Module',
            'pdf' => UploadedFile::fake()->create('azure.pdf', 100, 'application/pdf'),
            'is_published' => '1',
        ])->assertRedirect(route('admin.learning-materials.index'));

        $material = LearningMaterial::first();
        $this->assertSame('azure', $material->storage_disk);
        $this->assertSame(
            'https://account.blob.core.windows.net/materials/arbipi/'.$material->file_path,
            $material->file_url,
        );
        Storage::disk('azure')->assertExists($material->file_path);
    }

    public function test_admin_and_student_are_redirected_to_material_file_url(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $student = User::factory()->create();
        $student->assignRole('student');
        $fileUrl = 'https://arpibi2026.blob.core.windows.net/materials/learning-materials/module.pdf';
        $material = LearningMaterial::factory()->create([
            'storage_disk' => 'azure',
            'file_url' => $fileUrl,
            'is_published' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.learning-materials.open', $material))
            ->assertRedirect($fileUrl);

        $this->actingAs($student)
            ->get(route('student.learning-materials.open', $material))
            ->assertRedirect($fileUrl);
    }

    public function test_deleting_material_also_deletes_pdf(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        Storage::disk('local')->put('learning-materials/delete-me.pdf', '%PDF-1.4 test');
        $material = LearningMaterial::factory()->create(['file_path' => 'learning-materials/delete-me.pdf']);

        $this->actingAs($admin)->delete(route('admin.learning-materials.destroy', $material))
            ->assertRedirect();

        $this->assertDatabaseMissing('learning_materials', ['id' => $material->id]);
        Storage::disk('local')->assertMissing('learning-materials/delete-me.pdf');
    }
}
