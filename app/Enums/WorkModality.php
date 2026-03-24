<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum WorkModality: int implements HasLabel
{
    case ON_SITE = 0;
    case REMOTE = 1;
    case HYBRID = 2;

    public function getLabel(): ?string
    {
        return match ($this) {
            self::ON_SITE => __('common.enums.work-modality.on-site'),
            self::REMOTE => __('common.enums.work-modality.remote'),
            self::HYBRID => __('common.enums.work-modality.hybrid'),
        };
    }
}
