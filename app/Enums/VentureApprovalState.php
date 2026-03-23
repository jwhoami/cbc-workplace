<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum VentureApprovalState: int implements HasLabel
{
  case NEW = 0;
  case UPDATED = 1; // when a rejected case is updated
  case APPROVAL = 2;
  case APPROVED = 3;
  case REJECTED = 4;

  public function getLabel(): ?string
  {
    return match ($this) {
      static::NEW => __('common.enums.venture-approval-state.new'),
      static::UPDATED => __('common.enums.venture-approval-state.updated'),
      static::APPROVAL => __('common.enums.venture-approval-state.approval'),
      static::APPROVED => __('common.enums.venture-approval-state.approved'),
      static::REJECTED => __('common.enums.venture-approval-state.rejected'),
    };
  }
}
