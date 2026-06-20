<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('public_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('kind');
            $table->uuid('correlation_id');
            $table->timestamp('occurred_at')->useCurrent();
            $table->string('path', 255);
            $table->text('query_string')->nullable();
            $table->string('visitor_variant', 32);
            $table->unsignedSmallInteger('page_number')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->index('correlation_id');
            $table->index('occurred_at');
            $table->index(['kind', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('public_events');
    }
};
