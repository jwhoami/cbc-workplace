<?php

declare(strict_types=1);

namespace App\Actions\Admin;

/**
 * Discriminated outcome of {@see SuspendOrganization::handle()}.
 *
 * Mirrors the pattern established by spec 008's `DispatchDecision`:
 * the caller switches on `$result->kind` to decide which notification
 * to render.
 */
final class SuspendOrganizationResult
{
    public const KIND_SUSPENDED = 'suspended';

    public const KIND_ALREADY_SUSPENDED = 'already_suspended';

    private function __construct(
        public readonly string $kind,
        public readonly int $offersDeactivated = 0,
        public readonly int $notificationsEnqueued = 0,
    ) {}

    public static function suspended(int $offersDeactivated, int $notificationsEnqueued): self
    {
        return new self(self::KIND_SUSPENDED, $offersDeactivated, $notificationsEnqueued);
    }

    public static function alreadySuspended(): self
    {
        return new self(self::KIND_ALREADY_SUSPENDED);
    }

    public function wasSuspended(): bool
    {
        return $this->kind === self::KIND_SUSPENDED;
    }

    public function wasAlreadySuspended(): bool
    {
        return $this->kind === self::KIND_ALREADY_SUSPENDED;
    }
}
