<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Classifies the current request's visitor for the purpose of choosing the
 * right Apply CTA on the public offer detail page (FR-019). String-backed so
 * the value can be persisted verbatim into `public_events.visitor_variant`
 * (TEXT-friendly for spec 009 SQL queries) — see data-model.md §4.
 */
enum VisitorVariant: string implements HasLabel
{
    case Anonymous = 'anonymous';
    case MemberWithoutCandidateProfile = 'member-no-profile';
    case MemberCandidate = 'member-candidate';
    case Admin = 'admin';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Anonymous => __('common.enums.visitor-variant.anonymous'),
            self::MemberWithoutCandidateProfile => __('common.enums.visitor-variant.member-no-profile'),
            self::MemberCandidate => __('common.enums.visitor-variant.member-candidate'),
            self::Admin => __('common.enums.visitor-variant.admin'),
        };
    }
}
