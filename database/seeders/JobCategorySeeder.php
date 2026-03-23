<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class JobCategorySeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $categories = [
      ['name' => 'Administracion y Finanzas', 'slug' => 'administracion-y-finanzas', 'icon' => 'heroicon-o-calculator', 'order' => 1],
      ['name' => 'Tecnologia e Informatica', 'slug' => 'tecnologia-e-informatica', 'icon' => 'heroicon-o-computer-desktop', 'order' => 2],
      ['name' => 'Educacion y Docencia', 'slug' => 'educacion-y-docencia', 'icon' => 'heroicon-o-academic-cap', 'order' => 3],
      ['name' => 'Pastoral y Ministerio', 'slug' => 'pastoral-y-ministerio', 'icon' => 'heroicon-o-heart', 'order' => 4],
      ['name' => 'Comunicacion y Medios', 'slug' => 'comunicacion-y-medios', 'icon' => 'heroicon-o-megaphone', 'order' => 5],
      ['name' => 'Salud y Bienestar', 'slug' => 'salud-y-bienestar', 'icon' => 'heroicon-o-shield-check', 'order' => 6],
      ['name' => 'Servicios Generales', 'slug' => 'servicios-generales', 'icon' => 'heroicon-o-wrench-screwdriver', 'order' => 7],
      ['name' => 'Diseno y Creatividad', 'slug' => 'diseno-y-creatividad', 'icon' => 'heroicon-o-paint-brush', 'order' => 8],
      ['name' => 'Voluntariado', 'slug' => 'voluntariado', 'icon' => 'heroicon-o-hand-raised', 'order' => 9],
    ];

    foreach ($categories as $category) {
      Category::firstOrCreate(
        ['name' => $category['name'], 'scope' => 'JobListing'],
        ['slug' => $category['slug'], 'icon' => $category['icon'], 'order' => $category['order']]
      );
    }
  }
}
