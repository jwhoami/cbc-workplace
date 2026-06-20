<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->morphs('ownable');
            $table->string('file');
            $table->string('disk')->default('files');
            $table->string('caption')->nullable();
            $table->unsignedInteger('size')->nullable();
            $table->string('media_type', 20)->default('image');
            $table->string('mime_type', 100)->nullable();
            $table->boolean('is_mobile')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
