<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dateTime('suspended_at')->nullable()->after('verification_reason');
            $table->string('suspended_by', 100)->nullable()->after('suspended_at');
            $table->text('suspension_reason')->nullable()->after('suspended_by');
            $table->index('suspended_at', 'organizations_suspended_at_index');
        });

        // Backfill: convert any rows currently using verification_state = SUSPENDED (2)
        // to the new orthogonal flag, preserving timestamps and reason text.
        DB::table('organizations')
            ->where('verification_state', 2)
            ->update([
                'suspended_at' => DB::raw('COALESCE(verified_at, NOW())'),
                'suspended_by' => DB::raw('verification_by'),
                'suspension_reason' => DB::raw('verification_reason'),
                'verification_state' => 0,
                'verified_at' => null,
                'verification_by' => null,
                'verification_reason' => null,
            ]);

        // FR-005 / R12: ensure submitted_at on applications is indexed for the
        // dashboard "Postulaciones (24h)" stat. Skip silently if already covered.
        if (Schema::hasTable('applications') && ! $this->indexExists('applications', 'applications_submitted_at_index')) {
            Schema::table('applications', function (Blueprint $table) {
                $table->index('submitted_at', 'applications_submitted_at_index');
            });
        }
    }

    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropIndex('organizations_suspended_at_index');
            $table->dropColumn(['suspended_at', 'suspended_by', 'suspension_reason']);
        });

        if (Schema::hasTable('applications') && $this->indexExists('applications', 'applications_submitted_at_index')) {
            Schema::table('applications', function (Blueprint $table) {
                $table->dropIndex('applications_submitted_at_index');
            });
        }
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $database = DB::getDatabaseName();
        $count = DB::selectOne(
            'SELECT COUNT(1) AS c FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ?',
            [$database, $table, $indexName]
        );

        return (int) ($count->c ?? 0) > 0;
    }
};
