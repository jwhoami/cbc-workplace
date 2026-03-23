<?php

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

  public function verified(): static
  {
    return $this->state(fn (array $attributes) => [
      'verification_state' => OrganizationVerificationState::VERIFIED,
      'verification_by' => 'Admin Test',
      'verified_at' => now(),
    ]);
  }

  public function suspended(): static
  {
    return $this->state(fn (array $attributes) => [
      'verification_state' => OrganizationVerificationState::SUSPENDED,
      'verification_by' => 'Admin Test',
      'verified_at' => now(),
      'verification_reason' => 'Suspended for testing',
    ]);
  }
}
