<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum OrganizationType: int implements HasLabel
{
    case CHURCH = 0;
    case MINISTRY = 1;
    case NONPROFIT = 2;
    case PRIVATE_COMPANY = 3;
    case STARTUP = 4;

    public function getLabel(): ?string
    {
        return match ($this) {
            self::CHURCH => __('common/enums/organization-type.church'),
            self::MINISTRY => __('common/enums/organization-type.ministry'),
            self::NONPROFIT => __('common/enums/organization-type.nonprofit'),
            self::PRIVATE_COMPANY => __('common/enums/organization-type.private_company'),
            self::STARTUP => __('common/enums/organization-type.startup'),
        };
    }
}
