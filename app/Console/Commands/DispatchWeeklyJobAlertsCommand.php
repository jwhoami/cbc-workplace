<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\Alerts\DispatchWeeklyDigestAction;
use Illuminate\Console\Command;

class DispatchWeeklyJobAlertsCommand extends Command
{
    protected $signature = 'alerts:dispatch-weekly';

    protected $description = 'Despacha el resumen semanal de alertas de empleo a los miembros con alertas activas (frecuencia semanal).';

    public function handle(): int
    {
        $summary = DispatchWeeklyDigestAction::run();

        $this->info('Resumen del despacho semanal:');
        foreach ($summary as $key => $value) {
            $this->line("  {$key}: {$value}");
        }

        return self::SUCCESS;
    }
}
