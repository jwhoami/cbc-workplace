<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $this->createAdminRole();
  }

  protected function createAdminRole()
  {
    Role::create([
      'name' => 'ADMIN',
      'title' => 'ADMIN',
      'is_active' => true,
      'is_admin' => true,
      'perm' => [],
    ]);
    Role::create([
      'name' => 'DEACONO',
      'title' => 'DEACONO',
      'is_active' => true,
      'is_admin' => false,
      'perm' => [],
    ]);
    Role::create([
      'name' => 'AFFILIADO',
      'title' => 'AFFILIADO',
      'is_active' => true,
      'is_admin' => false,
      'perm' => [],
    ]);
  }
}
