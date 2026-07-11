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
        Schema::table('businesses', function (Blueprint $table) {
            $table->unsignedInteger('total_views')->default(0)->after('is_active');
            $table->unsignedInteger('views_this_week')->default(0)->after('total_views');
            $table->decimal('growth_percentage', 5, 2)->default(0)->after('views_this_week');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn(['total_views', 'views_this_week', 'growth_percentage']);
        });
    }
};
