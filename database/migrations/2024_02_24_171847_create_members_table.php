<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('members', function (Blueprint $table) {
      $table->id();
      $table->foreignId('role_id')->nullable()->constrained()->nullOnDelete();
      $table->foreignId('invitation_id')->nullable()->constrained()->nullOnDelete();
      $table->string('avatar')->nullable();
      $table->tinyInteger('type')->default(1);
      $table->string('name');
      $table->string('email')->unique();
      $table->string('password');
      $table->boolean('can_sponsor')->default(false);
      $table->string('sponsored_by')->nullable();
      $table->tinyInteger('membership_state')->default(0);
      $table->string('membership_approval_by')->nullable();
      $table->dateTime('membership_approval_at')->nullable();
      $table->text('membership_reason')->nullable();
      $table->text('membership_approval_reason')->nullable();
      $table->json('social_medias')->nullable();
      $table->rememberToken();
      $table->timestamp('email_verified_at')->nullable();
      $table->dateTime('expires_at')->nullable();
      $table->boolean('is_active')->default(1);
      $table->boolean('is_blocked')->default(0);
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
