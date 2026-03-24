<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidate_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('member_id')->unique();
            $table->foreign('member_id')->references('id')->on('members')->cascadeOnDelete();
            $table->string('headline', 150);
            $table->text('summary');
            $table->string('city', 100);
            $table->string('province', 100);
            $table->string('phone', 30);
            $table->string('photo', 255)->nullable();
            $table->string('cv_path', 255)->nullable();
            $table->text('faith_statement')->nullable();
            $table->boolean('is_visible')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidate_profiles');
    }
};
