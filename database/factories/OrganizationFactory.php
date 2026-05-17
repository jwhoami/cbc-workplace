<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\OrganizationType;
use App\Enums\OrganizationVerificationState;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrganizationFactory extends Factory
{
    protected $model = Organization::class;

    public function definition(): array
    {
        return [
            'legal_name' => $this->faker->company(),
            'display_name' => $this->faker->company(),
            'type' => OrganizationType::CHURCH,
            'denomination' => null,
            'description' => $this->faker->paragraph(),
            'culture_statement' => null,
            'logo' => null,
            'website' => $this->faker->url(),
            'email_contact' => $this->faker->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'city' => 'Ciudad de Panamá',
            'province' => 'Panamá',
            'country' => 'Panama',
            'verification_state' => OrganizationVerificationState::PENDING,
            'is_active' => true,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'verification_state' => OrganizationVerificationState::PENDING,
            'verification_by' => null,
            'verified_at' => null,
            'verification_reason' => null,
        ]);
    }

    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'verification_state' => OrganizationVerificationState::VERIFIED,
            'verification_by' => 'Admin Test',
            'verified_at' => now(),
        ]);
    }

    /**
     * Apply the orthogonal suspension flag (spec 009 §R1).
     * Does NOT modify verification_state.
     */
    public function suspended(?string $reason = null, ?string $by = 'Test Admin'): static
    {
        return $this->state(fn (array $attributes) => [
            'suspended_at' => now(),
            'suspended_by' => $by,
            'suspension_reason' => $reason,
        ]);
    }

    public function verifiedSuspended(?string $reason = null, ?string $by = 'Test Admin'): static
    {
        return $this->verified()->suspended($reason, $by);
    }

    public function pendingSuspended(?string $reason = null, ?string $by = 'Test Admin'): static
    {
        return $this->pending()->suspended($reason, $by);
    }
}
