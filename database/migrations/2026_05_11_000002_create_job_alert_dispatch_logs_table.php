<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_alert_dispatch_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_alert_id')
                ->constrained('job_alerts')
                ->cascadeOnDelete();
            $table->string('window_key', 64);
            $table->unsignedTinyInteger('decision');
            $table->json('matched_offer_ids')->nullable();
            $table->uuid('correlation_id');
            $table->timestamp('dispatched_at')->useCurrent();

            $table->unique(['job_alert_id', 'window_key'], 'uniq_alert_window');
            $table->index('job_alert_id', 'idx_dispatch_alert');
            $table->index(['decision', 'dispatched_at'], 'idx_dispatch_decision');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_alert_dispatch_logs');
    }
};
