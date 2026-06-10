<?php

namespace Database\Factories;

use App\Models\LearningMaterial;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LearningMaterial>
 */
class LearningMaterialFactory extends Factory
{
    protected $model = LearningMaterial::class;

    public function definition(): array
    {
        return [
            'subject_id' => Subject::factory(),
            'uploaded_by' => User::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->sentence(),
            'file_path' => 'learning-materials/'.fake()->uuid().'.pdf',
            'original_name' => 'modul.pdf',
            'file_size' => 102400,
            'is_published' => true,
        ];
    }
}
