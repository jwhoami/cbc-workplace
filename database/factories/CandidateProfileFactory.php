<?php

namespace Database\Factories;

use App\Models\CandidateProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

class CandidateProfileFactory extends Factory
{
  protected $model = CandidateProfile::class;

  public function definition(): array
  {
    return [
      'headline' => $this->faker->jobTitle(),
      'summary' => $this->faker->paragraphs(2, true),
      'city' => 'Ciudad de Panamá',
      'province' => 'Panamá',
      'phone' => '+507 6' . $this->faker->numerify('###-####'),
      'photo' => null,
      'cv_path' => null,
      'faith_statement' => null,
      'is_visible' => true,
    ];
  }

  public function hidden(): static
  {
    return $this->state(fn (array $attributes) => [
      'is_visible' => false,
    ]);
  }

  public function withPhoto(): static
  {
    return $this->state(fn (array $attributes) => [
      'photo' => 'candidates/photos/test-photo.jpg',
    ]);
  }

  public function withCv(): static
  {
    return $this->state(fn (array $attributes) => [
      'cv_path' => 'candidates/cvs/test-cv.pdf',
    ]);
  }
}
