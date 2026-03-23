<?php

namespace Database\Seeders;

use App\Enums\MemberType;
use App\Models\Member;
use App\Models\Venture;
use Illuminate\Database\Seeder;

class VentureSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $members = Member::query()->where('type', MemberType::MEMBER)->get();

    $members->each(function (Member $member) {
      Venture::factory()
        ->count(5)
        ->sequence(fn () => ['member_id' => $member->id])
        ->create();
    });
  }
}
