<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\Alerts\DispatchDailyDigestAction;
use Illuminate\Console\Command;

class DispatchDailyJobAlertsCommand extends Command
{
    protected $signature = 'alerts:dispatch-daily';

    protected $description = 'Despacha el resumen diario de alertas de empleo a los miembros con alertas activas (frecuencia diaria).';

    public function handle(): int
    {
        $summary = DispatchDailyDigestAction::run();

        $this->info('Resumen del despacho diario:');
        foreach ($summary as $key => $value) {
            $this->line("  {$key}: {$value}");
        }

        return self::SUCCESS;
    }
}
