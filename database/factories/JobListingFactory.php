<?php

namespace Database\Factories;

use App\Enums\ContractType;
use App\Enums\JobListingState;
use App\Enums\WorkModality;
use App\Models\JobListing;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

class JobListingFactory extends Factory
{
    protected $model = JobListing::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->randomElement([
                'Desarrollador Full Stack',
                'Diseñador Gráfico',
                'Contador General',
                'Asistente Administrativo',
                'Pastor de Jóvenes',
                'Coordinador de Eventos',
                'Especialista en Marketing Digital',
                'Técnico en Soporte IT',
                'Gerente de Proyectos',
                'Recepcionista Bilingüe',
            ]),
            'description' => $this->faker->paragraphs(3, true),
            'requirements' => $this->faker->paragraphs(2, true),
            'contract_type' => $this->faker->randomElement(ContractType::cases()),
            'work_modality' => $this->faker->randomElement(WorkModality::cases()),
            'city' => $this->faker->randomElement(['Ciudad de Panamá', 'David', 'Colón', 'Santiago', 'Chitré']),
            'province' => $this->faker->randomElement(['Panamá', 'Chiriquí', 'Colón', 'Veraguas', 'Herrera']),
            'salary_min' => $this->faker->optional(0.6)->randomFloat(2, 500, 2000),
            'salary_max' => fn (array $attributes) => $attributes['salary_min']
              ? $this->faker->randomFloat(2, $attributes['salary_min'] + 200, $attributes['salary_min'] + 2000)
              : null,
            'currency' => 'USD',
            'application_deadline' => $this->faker->dateTimeBetween('+1 week', '+3 months'),
            'state' => JobListingState::DRAFT,
            'screening_questions' => $this->faker->optional(0.4)->passthrough(
                $this->faker->randomElements([
                    '¿Por qué le interesa este puesto?',
                    '¿Cuántos años de experiencia tiene?',
                    '¿Cuál es su disponibilidad para iniciar?',
                    '¿Tiene referencias laborales?',
                    '¿Está dispuesto a trabajar fines de semana?',
                ], $this->faker->numberBetween(1, 3))
            ),
            'view_count' => 0,
        ];
    }

    public function forOrganization(Organization $organization): static
    {
        return $this->state(fn () => [
            'organization_id' => $organization->id,
            'member_id' => $organization->member_id,
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn () => ['state' => JobListingState::DRAFT]);
    }

    public function pending(): static
    {
        return $this->state(fn () => ['state' => JobListingState::PENDING]);
    }

    public function active(): static
    {
        return $this->state(fn () => [
            'state' => JobListingState::ACTIVE,
            'published_at' => now()->subDays($this->faker->numberBetween(1, 30)),
            'approval_by' => 'Admin',
            'approval_at' => now()->subDays($this->faker->numberBetween(1, 30)),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn () => [
            'state' => JobListingState::REJECTED,
            'approval_by' => 'Admin',
            'approval_at' => now()->subDays($this->faker->numberBetween(1, 7)),
            'approval_reason' => 'La descripción del puesto necesita más detalles.',
        ]);
    }

    public function closed(): static
    {
        return $this->state(fn () => [
            'state' => JobListingState::CLOSED,
            'published_at' => now()->subDays(30),
            'approval_by' => 'Admin',
            'approval_at' => now()->subDays(30),
            'closed_at' => now()->subDays($this->faker->numberBetween(1, 10)),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn () => [
            'state' => JobListingState::EXPIRED,
            'published_at' => now()->subDays(60),
            'approval_by' => 'Admin',
            'approval_at' => now()->subDays(60),
            'application_deadline' => now()->subDays($this->faker->numberBetween(1, 7)),
        ]);
    }
}
