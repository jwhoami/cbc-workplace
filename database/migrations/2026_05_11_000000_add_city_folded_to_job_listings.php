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
            $table->string('city_folded', 80)->nullable()->after('city');
            $table->index('city_folded', 'idx_listing_city_folded');
        });
    }

    public function down(): void
    {
        Schema::table('job_listings', function (Blueprint $table) {
            $table->dropIndex('idx_listing_city_folded');
            $table->dropColumn('city_folded');
        });
    }
};
