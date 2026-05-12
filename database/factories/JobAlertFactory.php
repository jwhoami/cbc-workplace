<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\JobAlertFrequency;
use App\Models\Category;
use App\Models\JobAlert;
use App\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;

class JobAlertFactory extends Factory
{
    protected $model = JobAlert::class;

    public function definition(): array
    {
        return [
            'member_id' => Member::factory(),
            'category_id' => $this->faker->boolean(70)
                ? Category::query()->where('scope', 'JobListing')->inRandomOrder()->value('id')
                : null,
            'city' => $this->faker->randomElement(['Lima', 'Trujillo', 'Arequipa', 'Cusco', null]),
            'frequency' => $this->faker->randomElement(JobAlertFrequency::cases())->value,
            'active' => true,
        ];
    }

    public function daily(): static
    {
        return $this->state(fn () => ['frequency' => JobAlertFrequency::Daily->value]);
    }

    public function weekly(): static
    {
        return $this->state(fn () => ['frequency' => JobAlertFrequency::Weekly->value]);
    }

    public function instant(): static
    {
        return $this->state(fn () => ['frequency' => JobAlertFrequency::Instant->value]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['active' => false]);
    }
}
