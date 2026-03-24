<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum MemberType: int implements HasLabel
{
    case VISITOR = 1;
    case MEMBER = 2;

    public function getLabel(): ?string
    {
        return match ($this) {
            self::MEMBER => __('models/member.type.member'),
            self::VISITOR => __('models/member.type.visitor'),
        };
    }
}
