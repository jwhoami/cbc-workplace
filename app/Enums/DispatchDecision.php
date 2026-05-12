<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum DispatchDecision: int implements HasLabel
{
    case Sent = 1;
    case SuppressedNoMatch = 2;
    case SuppressedInvalidRecipient = 3;

    public function getLabel(): string
    {
        return __('common/enums.dispatch-decision.'.$this->name);
    }
}
