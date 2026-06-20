<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\PublicEvent;
use Illuminate\Database\Seeder;

class PublicEventSeeder extends Seeder
{
    public function run(): void
    {
        PublicEvent::factory()->count(20)->pageView()->create();
        PublicEvent::factory()->count(15)->keywordQuery()->create();
        PublicEvent::factory()->count(8)->filterChange()->create();
        PublicEvent::factory()->count(5)->detailOpen()->create();
        PublicEvent::factory()->count(2)->errorShown()->create();
    }
}
