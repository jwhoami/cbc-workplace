<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_listings', function (Blueprint $table) {
            $table->index('title_folded', 'job_listings_title_folded_index');
            $table->index('city', 'job_listings_city_index');
            $table->index(['state', 'published_at'], 'job_listings_state_published_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('job_listings', function (Blueprint $table) {
            $table->dropIndex('job_listings_title_folded_index');
            $table->dropIndex('job_listings_city_index');
            $table->dropIndex('job_listings_state_published_at_index');
        });
    }
};
