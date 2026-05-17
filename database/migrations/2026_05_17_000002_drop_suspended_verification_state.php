<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Spec 009 backfilled every legacy `verification_state = SUSPENDED (2)` row to
     * `PENDING (0)` plus the new orthogonal `suspended_at` flag. This safety
     * migration runs the same UPDATE one more time so the follow-up enum
     * removal cannot strand a row that some environment somehow left at 2.
     *
     * Idempotent: a no-op on any environment where the spec-009 migration ran
     * cleanly. Fail-fast: aborts if any row remains at 2 after the UPDATE,
     * which would mean the dropped enum case is still being referenced.
     */
    public function up(): void
    {
        DB::table('organizations')
            ->where('verification_state', 2)
            ->update(['verification_state' => 0]);

        $remaining = DB::table('organizations')->where('verification_state', 2)->count();

        if ($remaining > 0) {
            throw new \RuntimeException(
                "Refusing to drop OrganizationVerificationState::SUSPENDED — {$remaining} organizations still have verification_state = 2 after the safety UPDATE."
            );
        }
    }

    public function down(): void
    {
        // No-op: the dropped enum case cannot be restored by a migration.
    }
};
