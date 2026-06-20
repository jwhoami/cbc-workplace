<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\Public\GenerateSitemapAction;
use Illuminate\Console\Command;

class GenerateSitemapCommand extends Command
{
    protected $signature = 'app:generate-sitemap
                            {--path= : Override the output path (default: public/sitemap.xml)}';

    protected $description = 'Regenerate the public sitemap.xml from active job listings (FR-023).';

    public function handle(): int
    {
        $path = $this->option('path');
        $result = GenerateSitemapAction::run($path !== null ? (string) $path : null);

        $this->info("Wrote {$result['count']} URLs to {$result['path']}.");

        return self::SUCCESS;
    }
}
