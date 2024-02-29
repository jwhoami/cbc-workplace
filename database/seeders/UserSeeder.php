<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
  public $tableName = "users";
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
    $this->createAdminUser();
  }

  protected function createAdminUser()
  {
    $role = Role::query()->where('name', 'ADMIN')->first();

    User::factory()
      ->create([
        'role_id' => $role->id,
        'username' => 'admin',
        'name' => fake()->name(),
        'email' => 'admin@gmail.com',
        'password' => 'password',
        'is_active' => true,
        'is_blocked' => false,
      ]);
  }
}
