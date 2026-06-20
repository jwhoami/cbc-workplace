<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Helpers\DiacriticFolder;
use App\Models\JobListing;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillFoldedColumnsCommand extends Command
{
    protected $signature = 'app:backfill-folded-columns
                            {--chunk=500 : Number of rows to update per batch}';

    protected $description = 'Populate title_folded and description_folded for existing job_listings rows.';

    public function handle(): int
    {
        $chunk = (int) $this->option('chunk');
        $count = 0;

        $this->info('Backfilling folded columns on job_listings...');

        JobListing::query()
            ->select(['id', 'title', 'description', 'city', 'city_folded'])
            ->orderBy('id')
            ->chunkById($chunk, function ($listings) use (&$count) {
                $now = now();
                foreach ($listings as $listing) {
                    $cityFolded = $listing->city_folded;
                    if ($cityFolded === null && $listing->city !== null && $listing->city !== '') {
                        $cityFolded = DiacriticFolder::fold((string) $listing->city);
                    }

                    DB::table('job_listings')
                        ->where('id', $listing->id)
                        ->update([
                            'title_folded' => DiacriticFolder::fold((string) $listing->title),
                            'description_folded' => DiacriticFolder::fold((string) $listing->description),
                            'city_folded' => $cityFolded,
                            'updated_at' => $now,
                        ]);
                    $count++;
                }
            });

        $this->info("Backfilled {$count} rows.");

        return self::SUCCESS;
    }
}
