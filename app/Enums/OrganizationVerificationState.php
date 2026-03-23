<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum OrganizationVerificationState: int implements HasLabel
{
  case PENDING = 0;
  case VERIFIED = 1;
  case SUSPENDED = 2;

  public function getLabel(): ?string
  {
    return match ($this) {
      static::PENDING => __('common/enums/organization-verification-state.pending'),
      static::VERIFIED => __('common/enums/organization-verification-state.verified'),
      static::SUSPENDED => __('common/enums/organization-verification-state.suspended'),
    };
  }
}
