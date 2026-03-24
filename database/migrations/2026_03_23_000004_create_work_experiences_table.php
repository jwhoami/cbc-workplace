<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_experiences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('candidate_profile_id');
            $table->foreign('candidate_profile_id')->references('id')->on('candidate_profiles')->cascadeOnDelete();
            $table->string('company', 150);
            $table->string('position', 150);
            $table->text('description');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_current')->default(false);
            $table->timestamps();

            $table->index('candidate_profile_id');
            $table->index(['candidate_profile_id', 'start_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_experiences');
    }
};
