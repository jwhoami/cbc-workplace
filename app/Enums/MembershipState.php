<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum MembershipState: int implements HasLabel
{
  case VISITOR = 1;
  case PENDING = 2;
  case APPROVED = 3;
  case REJECTED = 4;

  public function getLabel(): ?string
  {
    return match ($this) {
      static::VISITOR => __('models/member.membership-state.visitor'),
      static::PENDING => __('models/member.membership-state.pending'),
      static::APPROVED => __('models/member.membership-state.approved'),
      static::REJECTED => __('models/member.membership-state.rejected'),
    };
  }
}
