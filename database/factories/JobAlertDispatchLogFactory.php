<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\DispatchDecision;
use App\Models\JobAlert;
use App\Models\JobAlertDispatchLog;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class JobAlertDispatchLogFactory extends Factory
{
    protected $model = JobAlertDispatchLog::class;

    public function definition(): array
    {
        return [
            'job_alert_id' => JobAlert::factory(),
            'window_key' => 'daily:'.now()->format('Y-m-d'),
            'decision' => DispatchDecision::Sent->value,
            'matched_offer_ids' => [1, 2],
            'correlation_id' => (string) Str::uuid(),
            'dispatched_at' => now(),
        ];
    }
}
