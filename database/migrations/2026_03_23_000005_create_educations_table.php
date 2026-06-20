<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('educations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('candidate_profile_id');
            $table->foreign('candidate_profile_id')->references('id')->on('candidate_profiles')->cascadeOnDelete();
            $table->string('institution', 200);
            $table->string('degree', 200);
            $table->string('field_of_study', 150);
            $table->smallInteger('graduation_year')->nullable();
            $table->boolean('is_in_progress')->default(false);
            $table->timestamps();

            $table->index('candidate_profile_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('educations');
    }
};
