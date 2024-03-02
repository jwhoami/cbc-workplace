<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum MemberType: int implements HasLabel
{
  case MEMBER = 1;
  case VISITOR = 2;

  public function getLabel(): ?string
  {
    return match ($this) {
      static::MEMBER => __('models/member.type.member'),
      static::VISITOR => __('models/member.type.visitor'),
    };
  }
}
