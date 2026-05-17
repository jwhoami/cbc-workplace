<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum OrganizationVerificationState: int implements HasLabel
{
    case PENDING = 0;
    case VERIFIED = 1;

    /**
     * @deprecated Spec 009 §R1: suspension is now modelled as an orthogonal
     *             flag on `organizations.suspended_at`. Do NOT write this
     *             value from application code. Retained only to keep historic
     *             database/log rows resolvable until the follow-up cleanup PR
     *             removes it entirely.
     */
    case SUSPENDED = 2;

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PENDING => __('common/enums/organization-verification-state.pending'),
            self::VERIFIED => __('common/enums/organization-verification-state.verified'),
            self::SUSPENDED => __('common/enums/organization-verification-state.suspended'),
        };
    }
}
