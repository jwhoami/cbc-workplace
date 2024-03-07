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
    Schema::create('ventures', function (Blueprint $table) {
      $table->id();
      $table->foreignId('author_id')->constrained('members')->cascadeOnDelete();
      $table->string('title');
      $table->text('content');
      $table->tinyInteger('approval_state')->default(0);
      $table->string('approval_by')->nullable();
      $table->dateTime('approval_at')->nullable();
      $table->text('approval_reason')->nullable();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('posts');
  }
};
