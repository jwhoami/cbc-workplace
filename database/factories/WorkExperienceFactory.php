<?php

namespace Database\Factories;

use App\Models\WorkExperience;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkExperienceFactory extends Factory
{
  protected $model = WorkExperience::class;

  public function definition(): array
  {
    $startDate = $this->faker->dateTimeBetween('-10 years', '-1 year');
    $endDate = $this->faker->dateTimeBetween($startDate, 'now');

    return [
      'company' => $this->faker->company(),
      'position' => $this->faker->jobTitle(),
      'description' => $this->faker->paragraph(),
      'start_date' => $startDate,
      'end_date' => $endDate,
      'is_current' => false,
    ];
  }

  public function current(): static
  {
    return $this->state(fn (array $attributes) => [
      'is_current' => true,
      'end_date' => null,
    ]);
  }
}
