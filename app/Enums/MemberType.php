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
      static::MEMBER => __('models/members.type.member'),
      static::VISITOR => __('models/members.type.visitor'),
    };
  }
}
