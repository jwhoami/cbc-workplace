<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum JobAlertFrequency: int implements HasLabel
{
    case Daily = 1;
    case Weekly = 2;
    case Instant = 3;

    public function getLabel(): string
    {
        return __('common/enums.job-alert-frequency.'.$this->name);
    }
}
