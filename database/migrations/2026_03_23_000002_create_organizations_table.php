<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('organizations', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('member_id')->unique();
      $table->foreign('member_id')->references('id')->on('members')->cascadeOnDelete();
      $table->string('legal_name', 150);
      $table->string('display_name', 150);
      $table->tinyInteger('type');
      $table->string('denomination', 100)->nullable();
      $table->text('description');
      $table->text('culture_statement')->nullable();
      $table->string('logo', 255)->nullable();
      $table->string('website', 255)->nullable();
      $table->string('email_contact', 150);
      $table->string('phone', 30)->nullable();
      $table->string('city', 100);
      $table->string('province', 100);
      $table->string('country', 100)->default('Panama');
      $table->tinyInteger('verification_state')->default(0)->index();
      $table->string('verification_by', 100)->nullable();
      $table->dateTime('verified_at')->nullable();
      $table->text('verification_reason')->nullable();
      $table->boolean('is_active')->default(true);
      $table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('organizations');
  }
};
