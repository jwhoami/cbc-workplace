<?php

namespace Database\Factories;

use App\Models\Application;
use App\Models\ApplicationNote;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ApplicationNote>
 */
class ApplicationNoteFactory extends Factory
{
    protected $model = ApplicationNote::class;

    public function definition(): array
    {
        return [
            'application_id' => Application::factory(),
            'author_user_id' => User::factory(),
            'author_name_snapshot' => fake()->name(),
            'body' => fake()->text(500),
        ];
    }
}
