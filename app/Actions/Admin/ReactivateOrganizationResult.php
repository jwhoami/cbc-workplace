<?php

declare(strict_types=1);

namespace App\Actions\Admin;

final class ReactivateOrganizationResult
{
    public const KIND_REACTIVATED = 'reactivated';

    public const KIND_NOT_SUSPENDED = 'not_suspended';

    private function __construct(public readonly string $kind) {}

    public static function reactivated(): self
    {
        return new self(self::KIND_REACTIVATED);
    }

    public static function notSuspended(): self
    {
        return new self(self::KIND_NOT_SUSPENDED);
    }

    public function wasReactivated(): bool
    {
        return $this->kind === self::KIND_REACTIVATED;
    }

    public function wasNotSuspended(): bool
    {
        return $this->kind === self::KIND_NOT_SUSPENDED;
    }
}
