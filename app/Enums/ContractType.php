<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ContractType: int implements HasLabel
{
    case FULL_TIME = 0;
    case PART_TIME = 1;
    case TEMPORARY = 2;
    case VOLUNTEER = 3;

    public function getLabel(): ?string
    {
        return match ($this) {
            self::FULL_TIME => __('common.enums.contract-type.full-time'),
            self::PART_TIME => __('common.enums.contract-type.part-time'),
            self::TEMPORARY => __('common.enums.contract-type.temporary'),
            self::VOLUNTEER => __('common.enums.contract-type.volunteer'),
        };
    }
}
