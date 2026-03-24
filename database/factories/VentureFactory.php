<?php

namespace Database\Factories;

use App\Enums\VentureApprovalState;
use App\Models\Venture;
use Illuminate\Database\Eloquent\Factories\Factory;

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
            'approval_state' => VentureApprovalState::UNDEFINED,
        ];
    }
}
