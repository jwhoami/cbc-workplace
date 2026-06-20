<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('application_notes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('application_id');
            $table->foreign('application_id')->references('id')->on('applications')->cascadeOnDelete();
            $table->unsignedBigInteger('author_user_id')->nullable();
            $table->foreign('author_user_id')->references('id')->on('users')->nullOnDelete();
            $table->string('author_name_snapshot', 150);
            $table->text('body');
            $table->timestamps();

            $table->index('application_id');
            $table->index('author_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_notes');
    }
};
