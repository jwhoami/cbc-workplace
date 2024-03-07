<?php

namespace Database\Seeders;

use App\Enums\MembershipState;
use App\Models\Member;
use Illuminate\Database\Seeder;

class MemberSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    Member::factory()->create([
      'email' => 'member@gmail.com',
      'email_verified_at' => now(),
      'type' => MembershipState::APPROVED
    ]);

    Member::factory()->count(12)->create();
  }
}
