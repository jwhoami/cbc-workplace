<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum OrganizationVerificationState: int implements HasLabel
{
    case PENDING = 0;
    case VERIFIED = 1;

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PENDING => __('common/enums/organization-verification-state.pending'),
            self::VERIFIED => __('common/enums/organization-verification-state.verified'),
        };
    }
}
