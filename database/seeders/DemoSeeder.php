<?php

namespace Database\Seeders;

use App\Models\LearningMaterial;
use App\Models\Question;
use App\Models\Subject;
use App\Models\Tryout;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(['email' => 'admin@arbipi.id'], ['name' => 'Admin aRBiPi', 'password' => Hash::make('password')]);
        $student = User::firstOrCreate(['email' => 'siswa@arbipi.id'], ['name' => 'Siswa Demo', 'password' => Hash::make('password')]);
        $admin->assignRole('admin');
        $student->assignRole('student');
        foreach (['Matematika', 'Bahasa Indonesia', 'IPA'] as $name) {
            $subject = Subject::firstOrCreate(['slug' => str($name)->slug()], ['name' => $name, 'description' => "Materi dan latihan {$name}"]);
            for ($i = 1; $i <= 4; $i++) {
                $q = Question::firstOrCreate(['subject_id' => $subject->id, 'question_text' => "Contoh soal {$name} nomor {$i}: pilih jawaban yang paling tepat."], ['created_by' => $admin->id, 'difficulty' => ['easy', 'medium', 'hard'][$i % 3], 'explanation' => 'Jawaban A merupakan jawaban contoh yang benar.']);
                foreach (['A', 'B', 'C', 'D'] as $label) {
                    $q->options()->firstOrCreate(['label' => $label], ['option_text' => "Pilihan {$label}", 'is_correct' => $label === 'A']);
                }
            }
        }
        $subject = Subject::first();
        $tryout = Tryout::firstOrCreate(['title' => 'Tryout Diagnostik Pertama'], ['subject_id' => $subject->id, 'created_by' => $admin->id, 'description' => 'Kenali kemampuan awalmu.', 'duration_minutes' => 30, 'is_published' => true]);
        $tryout->questions()->syncWithoutDetaching($subject->questions->mapWithKeys(fn ($q, $i) => [$q->id => ['order' => $i + 1, 'points' => 1]])->all());

        $cloud = Subject::firstOrCreate(
            ['slug' => 'komputasi-awan'],
            ['name' => 'Komputasi Awan', 'description' => 'Materi dasar komputasi awan, container, dan Docker.']
        );
        foreach (['Modul 1.pdf', 'Modul 2.pdf', 'Docker.pdf'] as $index => $filename) {
            $path = 'learning-materials/demo/'.str($filename)->slug().'.pdf';
            if (! Storage::disk('local')->exists($path)) {
                Storage::disk('local')->put($path, $this->samplePdf(str($filename)->beforeLast('.pdf')->toString()));
            }
            LearningMaterial::updateOrCreate(
                ['subject_id' => $cloud->id, 'title' => str($filename)->beforeLast('.pdf')->toString()],
                [
                    'uploaded_by' => $admin->id,
                    'description' => $index === 2 ? 'Pengenalan Docker dan container.' : 'Modul pembelajaran komputasi awan.',
                    'storage_disk' => 'local',
                    'file_path' => $path,
                    'file_url' => null,
                    'original_name' => $filename,
                    'file_size' => Storage::disk('local')->size($path),
                    'is_published' => true,
                ]
            );
        }
    }

    private function samplePdf(string $title): string
    {
        return "%PDF-1.4\n1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj\n2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj\n3 0 obj<</Type/Page/Parent 2 0 R/MediaBox[0 0 612 792]/Contents 4 0 R>>endobj\n4 0 obj<</Length 49>>stream\nBT /F1 18 Tf 72 720 Td ({$title}) Tj ET\nendstream\nendobj\ntrailer<</Root 1 0 R>>\n%%EOF";
    }
}
