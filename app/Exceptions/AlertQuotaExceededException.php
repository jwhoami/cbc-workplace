<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

class AlertQuotaExceededException extends RuntimeException
{
    public function __construct(
        public readonly int $currentCount,
        public readonly int $max,
        string $message = '',
    ) {
        parent::__construct(
            $message !== ''
                ? $message
                : __('models/job-alert.actions.quota_exceeded', ['max' => $max]),
        );
    }
}
