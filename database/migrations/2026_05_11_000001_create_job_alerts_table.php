<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')
                ->constrained('members')
                ->cascadeOnDelete();
            $table->foreignId('category_id')
                ->nullable()
                ->constrained('categories')
                ->nullOnDelete();
            $table->string('city', 80)->nullable();
            $table->string('city_folded', 80)->nullable();
            $table->unsignedTinyInteger('frequency');
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index('member_id', 'idx_alert_member');
            $table->index(['active', 'frequency'], 'idx_alert_active_frequency');
            $table->index(['active', 'frequency', 'category_id', 'city_folded'], 'idx_alert_match');
            $table->unique(
                ['member_id', 'category_id', 'city_folded', 'frequency'],
                'uniq_alert_criteria'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_alerts');
    }
};
