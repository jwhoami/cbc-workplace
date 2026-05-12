<?php

declare(strict_types=1);

namespace App\Actions\Alerts;

use App\Models\JobAlert;
use App\Models\JobListing;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\AsJob;

class CoalesceInstantMatchAction
{
    use AsAction;
    use AsJob;

    public function handle(JobAlert $alert, JobListing $offer): void
    {
        $lock = Cache::lock('alert-window-lock:'.$alert->id, 10);

        try {
            $lock->block(5, function () use ($alert, $offer) {
                $cacheKey = 'alert-window:'.$alert->id;
                $window = Cache::get($cacheKey);
                $ttl = (int) config('alerts.instant_window_seconds', 300) + 60;

                if ($window === null) {
                    $opensAt = now();
                    $windowKey = 'instant:'.$opensAt->copy()->floorMinutes(5)->timestamp;

                    Cache::put($cacheKey, [
                        'opens_at' => $opensAt->toIso8601String(),
                        'offer_ids' => [$offer->id],
                        'window_key' => $windowKey,
                    ], $ttl);

                    DispatchInstantAlertAction::dispatch($alert->id, $windowKey)
                        ->onQueue('instant')
                        ->delay(now()->addSeconds((int) config('alerts.instant_window_seconds', 300)));

                    return;
                }

                $window['offer_ids'] = array_values(array_unique(array_merge($window['offer_ids'], [$offer->id])));
                Cache::put($cacheKey, $window, $ttl);
            });
        } catch (LockTimeoutException) {
            // Re-dispatch with backoff so another worker can drain the queue.
            static::dispatch($alert, $offer)
                ->onQueue('instant')
                ->delay(now()->addSeconds(30));
        }
    }
}
