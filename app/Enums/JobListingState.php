<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum JobListingState: int implements HasLabel
{
    case DRAFT = 0;
    case PENDING = 1;
    case ACTIVE = 2;
    case REJECTED = 3;
    case CLOSED = 4;
    case EXPIRED = 5;

    public function getLabel(): ?string
    {
        return match ($this) {
            self::DRAFT => __('common.enums.job-listing-state.draft'),
            self::PENDING => __('common.enums.job-listing-state.pending'),
            self::ACTIVE => __('common.enums.job-listing-state.active'),
            self::REJECTED => __('common.enums.job-listing-state.rejected'),
            self::CLOSED => __('common.enums.job-listing-state.closed'),
            self::EXPIRED => __('common.enums.job-listing-state.expired'),
        };
    }
}
