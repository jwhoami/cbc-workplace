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
    Schema::create('members', function (Blueprint $table) {
      $table->id();
      $table->string('avatar')->nullable();
      $table->tinyInteger('type')->default(1);
      $table->string('name');
      $table->string('email')->unique();
      $table->string('password');
      $table->tinyInteger('membership_state')->default(0);
      $table->string('membership_approval_by')->nullable();
      $table->dateTime('membership_approval_at')->nullable();
      $table->text('membership_reason')->nullable();
      $table->text('membership_approval_reason')->nullable();
      $table->json('social_medias')->nullable();
      $table->rememberToken();
      $table->timestamp('email_verified_at')->nullable();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('members');
  }
};
