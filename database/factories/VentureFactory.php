<?php

namespace Database\Factories;

use App\Enums\ApprovalState;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Venture;

class VentureFactory extends Factory
{
  /**
   * The name of the factory's corresponding model.
   *
   * @var string
   */
  protected $model = Venture::class;

  /**
   * Define the model's default state.
   */
  public function definition(): array
  {
    return [
      'title' => $this->faker->sentence(4),
      'content' => $this->faker->paragraphs(3, true),
      'approval_state' => ApprovalState::UNDEFINED,
    ];
  }
}
