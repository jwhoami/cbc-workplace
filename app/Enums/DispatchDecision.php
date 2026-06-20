<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum DispatchDecision: int implements HasLabel
{
    case Sent = 1;
    case SuppressedNoMatch = 2;
    case SuppressedInvalidRecipient = 3;

    /**
     * Race-result: this dispatcher invocation observed that another worker
     * had already written the (alert_id, window_key) row. The mail was queued
     * by that other invocation; this one did NOT queue mail or emit events.
     * Never persisted to job_alert_dispatch_logs.decision — only returned
     * upward so the dispatcher can report a distinct bucket separately from
     * the Sent count (spec 008 T075 Finding 2).
     */
    case AlreadySent = 4;

    public function getLabel(): string
    {
        return __('common/enums.dispatch-decision.'.$this->name);
    }
}
