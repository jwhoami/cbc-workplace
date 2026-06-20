<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

class DuplicateAlertException extends RuntimeException
{
    public function __construct(
        public readonly ?int $conflictingAlertId = null,
        string $message = '',
    ) {
        parent::__construct(
            $message !== ''
                ? $message
                : __('models/job-alert.actions.duplicate_alert'),
        );
    }
}
