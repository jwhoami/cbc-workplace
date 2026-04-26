<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ApplicationStatus: int implements HasLabel
{
    case RECEIVED = 0;
    case IN_REVIEW = 1;
    case INTERVIEW = 2;
    case REJECTED = 3;
    case ACCEPTED = 4;

    public function getLabel(): ?string
    {
        return match ($this) {
            self::RECEIVED => __('common.enums.application-status.received'),
            self::IN_REVIEW => __('common.enums.application-status.in_review'),
            self::INTERVIEW => __('common.enums.application-status.interview'),
            self::REJECTED => __('common.enums.application-status.rejected'),
            self::ACCEPTED => __('common.enums.application-status.accepted'),
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::REJECTED, self::ACCEPTED], true);
    }

    public function canTransitionTo(self $next): bool
    {
        if ($this->isTerminal()) {
            return false;
        }
        if ($next === self::RECEIVED) {
            return false;
        }

        return $next->value > $this->value;
    }
}
