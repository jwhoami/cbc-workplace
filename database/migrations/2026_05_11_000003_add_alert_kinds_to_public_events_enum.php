<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;

/**
 * Comment-only marker migration.
 *
 * Spec 008 added 8 new cases to the `App\Enums\PublicEventKind` enum
 * (values 6-13: AlertCreated, AlertEdited, AlertToggled, AlertDeleted,
 * AlertUnsubscribedViaLink, AlertEmailSent, AlertEmailSuppressedNoMatch,
 * AlertEmailSuppressedInvalidRecipient).
 *
 * The `public_events.kind` column is already an `unsignedTinyInteger`
 * (capacity 0-255) so no schema change is required — the PHP enum is the
 * source of truth. This migration exists only to record the additive
 * change for the `update-codemaps` workflow.
 */
return new class extends Migration
{
    public function up(): void
    {
        // No-op. See class docblock.
    }

    public function down(): void
    {
        // No-op. See class docblock.
    }
};
