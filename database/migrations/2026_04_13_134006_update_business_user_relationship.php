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
        // Remove business_id from business_users (one user can have many businesses)
        Schema::table('business_users', function (Blueprint $table) {
            $table->dropForeign(['business_id']);
            $table->dropColumn('business_id');
        });

        // Add business_user_id to businesses (nullable for orphan businesses)
        Schema::table('businesses', function (Blueprint $table) {
            $table->foreignId('business_user_id')->nullable()->after('id')->constrained('business_users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse the changes
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropForeign(['business_user_id']);
            $table->dropColumn('business_user_id');
        });

        Schema::table('business_users', function (Blueprint $table) {
            $table->foreignId('business_id')->after('id')->constrained('businesses')->cascadeOnDelete();
        });
    }
};
