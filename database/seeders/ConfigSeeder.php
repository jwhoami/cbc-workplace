<?php

namespace Database\Seeders;

use App\Models\Config;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ConfigSeeder extends Seeder
{
  public $tableName = "configs";
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
    Config::factory()->create(['name' => 'default', 'jsondata' => config('app_config')]);
  }
}
