<?php

namespace Database\Factories;

use App\Enums\MemberType;
use App\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;

class MemberFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Member::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->safeEmail(),
            'password' => 'password',
            'type' => $this->faker->randomElement(array_column(MemberType::cases(), 'value')),
            'social_medias' => [],
        ];
    }
}
