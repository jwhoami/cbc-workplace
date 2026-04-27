<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('job_listing_id');
            $table->foreign('job_listing_id')->references('id')->on('job_listings')->restrictOnDelete();
            $table->unsignedBigInteger('member_id')->nullable();
            $table->foreign('member_id')->references('id')->on('members')->restrictOnDelete();
            $table->unsignedBigInteger('candidate_profile_id')->nullable();
            $table->foreign('candidate_profile_id')->references('id')->on('candidate_profiles')->nullOnDelete();
            $table->text('cover_letter')->nullable();
            $table->json('screening_answers')->nullable();
            $table->string('cv_snapshot_path', 255)->nullable();
            $table->string('cv_snapshot_filename', 255)->nullable();
            $table->string('candidate_name_snapshot', 150);
            $table->string('candidate_email_snapshot', 255)->nullable();
            $table->unsignedTinyInteger('status')->default(0);
            $table->datetime('submitted_at');
            $table->datetime('last_status_changed_at')->nullable();
            $table->string('last_status_changed_by', 150)->nullable();
            $table->datetime('anonymized_at')->nullable();
            $table->timestamps();

            $table->unique(['job_listing_id', 'member_id']);
            $table->index('status');
            $table->index('member_id');
            $table->index('job_listing_id');
            $table->index(['job_listing_id', 'status']);
            $table->index(['member_id', 'submitted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
