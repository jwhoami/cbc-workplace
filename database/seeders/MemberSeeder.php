<?php

namespace Database\Seeders;

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
      'username' => 'member',
    ]);

    Member::factory()->count(12)->create();
  }
}
