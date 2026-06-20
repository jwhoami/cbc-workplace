<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum PublicEventKind: int implements HasLabel
{
    case PageView = 1;
    case KeywordQuery = 2;
    case FilterChange = 3;
    case DetailOpen = 4;
    case ErrorShown = 5;

    // Spec 008 — Job alert telemetry.
    case AlertCreated = 6;
    case AlertEdited = 7;
    case AlertToggled = 8;
    case AlertDeleted = 9;
    case AlertUnsubscribedViaLink = 10;
    case AlertEmailSent = 11;
    case AlertEmailSuppressedNoMatch = 12;
    case AlertEmailSuppressedInvalidRecipient = 13;

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PageView => __('common.enums.public-event-kind.page-view'),
            self::KeywordQuery => __('common.enums.public-event-kind.keyword-query'),
            self::FilterChange => __('common.enums.public-event-kind.filter-change'),
            self::DetailOpen => __('common.enums.public-event-kind.detail-open'),
            self::ErrorShown => __('common.enums.public-event-kind.error-shown'),
            self::AlertCreated,
            self::AlertEdited,
            self::AlertToggled,
            self::AlertDeleted,
            self::AlertUnsubscribedViaLink,
            self::AlertEmailSent,
            self::AlertEmailSuppressedNoMatch,
            self::AlertEmailSuppressedInvalidRecipient => __('common/enums.public-event-kind.'.$this->name),
        };
    }
}
