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
      static::UNDEFINED => __('models/member.membership-state.undefined'),
      static::PENDING => __('models/member.membership-state.pending'),
      static::APPROVED => __('models/member.membership-state.approved'),
      static::REJECTED => __('models/member.membership-state.rejected'),
    };
  }
}
