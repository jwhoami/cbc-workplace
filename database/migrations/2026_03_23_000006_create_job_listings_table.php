<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_listings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_id');
            $table->foreign('organization_id')->references('id')->on('organizations')->cascadeOnDelete();
            $table->unsignedBigInteger('member_id');
            $table->foreign('member_id')->references('id')->on('members')->cascadeOnDelete();
            $table->string('title', 200);
            $table->string('slug', 250)->unique();
            $table->text('description');
            $table->text('requirements');
            $table->unsignedTinyInteger('contract_type');
            $table->unsignedTinyInteger('work_modality');
            $table->string('city', 100);
            $table->string('province', 100);
            $table->decimal('salary_min', 10, 2)->nullable();
            $table->decimal('salary_max', 10, 2)->nullable();
            $table->string('currency', 3)->default('USD');
            $table->date('application_deadline');
            $table->unsignedTinyInteger('state')->default(0);
            $table->json('screening_questions')->nullable();
            $table->datetime('published_at')->nullable();
            $table->string('approval_by', 150)->nullable();
            $table->datetime('approval_at')->nullable();
            $table->text('approval_reason')->nullable();
            $table->unsignedInteger('view_count')->default(0);
            $table->datetime('closed_at')->nullable();
            $table->timestamps();

            $table->index('organization_id');
            $table->index('member_id');
            $table->index('state');
            $table->index('application_deadline');
            $table->index(['organization_id', 'state']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_listings');
    }
};
