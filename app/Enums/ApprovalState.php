<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ApprovalState: int implements HasLabel
{
  case UNDEFINED = 0;
  case PENDING = 1;
  case APPROVED = 2;
  case REJECTED = 3;

  public function getLabel(): ?string
  {
    return match ($this) {
      static::UNDEFINED => __('common.enums.approval-state.undefined'),
      static::PENDING => __('common.enums.approval-state.pending'),
      static::APPROVED => __('common.enums.approval-state.approved'),
      static::REJECTED => __('common.enums.approval-state.rejected'),
    };
  }
}
