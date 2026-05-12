<?php

declare(strict_types=1);

namespace App\Actions\Alerts;

use App\Enums\DispatchDecision;
use App\Enums\JobAlertFrequency;
use App\Helpers\Util;
use App\Models\JobAlert;
use Illuminate\Support\Carbon;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\AsJob;
use Throwable;

class DispatchDailyDigestAction
{
    use AsAction;
    use AsJob;

    public function handle(?Carbon $now = null): array
    {
        $now = $now ?? now();
        $windowEnd = $now;
        $windowStart = $now->copy()->subDay();
        $windowKey = 'daily:'.$now->format('Y-m-d');

        $counts = [
            'processed' => 0,
            'sent' => 0,
            'suppressed_no_match' => 0,
            'suppressed_invalid' => 0,
        ];

        JobAlert::query()
            ->active()
            ->withFrequency(JobAlertFrequency::Daily)
            ->ofActiveMember()
            ->cursor()
            ->each(function (JobAlert $alert) use (&$counts, $windowStart, $windowEnd, $windowKey) {
                $counts['processed']++;
                try {
                    $decision = BuildDigestForAlertAction::run($alert, $windowStart, $windowEnd, $windowKey);

                    match ($decision) {
                        DispatchDecision::Sent => $counts['sent']++,
                        DispatchDecision::SuppressedNoMatch => $counts['suppressed_no_match']++,
                        DispatchDecision::SuppressedInvalidRecipient => $counts['suppressed_invalid']++,
                    };
                } catch (Throwable $e) {
                    Util::logChange(
                        "alerts:dispatch-daily failed for alert {$alert->id}",
                        'error',
                        'ALERT_DISPATCH',
                        ['error' => $e->getMessage()]
                    );
                }
            });

        Util::getActivityLog('job-alert.dispatch.daily')
            ->withProperties(['summary' => $counts])
            ->log('Despacho de alertas diarias');

        return $counts;
    }
}
