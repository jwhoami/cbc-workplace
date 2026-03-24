<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum MembershipState: int implements HasLabel
{
    case UNDEFINED = 0;
    case PENDING = 1;
    case APPROVED = 2;
    case REJECTED = 3;

    public function getLabel(): ?string
    {
        return match ($this) {
            self::UNDEFINED => __('models/member.membership-state.undefined'),
            self::PENDING => __('models/member.membership-state.pending'),
            self::APPROVED => __('models/member.membership-state.approved'),
            self::REJECTED => __('models/member.membership-state.rejected'),
        };
    }
}
