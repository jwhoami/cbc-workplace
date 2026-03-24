<?php

namespace Database\Factories;

use App\Models\Education;
use Illuminate\Database\Eloquent\Factories\Factory;

class EducationFactory extends Factory
{
  protected $model = Education::class;

  public function definition(): array
  {
    return [
      'institution' => $this->faker->company() . ' University',
      'degree' => $this->faker->randomElement(['Licenciatura', 'Maestría', 'Técnico', 'Doctorado']),
      'field_of_study' => $this->faker->randomElement(['Informática', 'Administración', 'Ingeniería', 'Contabilidad']),
      'graduation_year' => $this->faker->numberBetween(2000, 2025),
      'is_in_progress' => false,
    ];
  }

  public function inProgress(): static
  {
    return $this->state(fn (array $attributes) => [
      'is_in_progress' => true,
      'graduation_year' => null,
    ]);
  }
}
