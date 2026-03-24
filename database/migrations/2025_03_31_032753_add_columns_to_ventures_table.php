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
        Schema::table('ventures', function (Blueprint $table) {
            $table->unsignedBigInteger('view_count')->nullable()
                ->after('preview_until');
            $table->unsignedBigInteger('favorite_count')->nullable()
                ->after('view_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ventures', function (Blueprint $table) {
            $table->dropColumn('view_count');
            $table->dropColumn('favorite_count');
        });
    }
};
