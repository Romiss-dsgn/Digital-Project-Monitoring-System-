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
        Schema::table('users', function (Blueprint $table) {
            $table->string('badge_number')->nullable()->unique()->after('email');
        });

        Schema::table('access_requests', function (Blueprint $table) {
            $table->string('badge_number')->nullable()->after('email');
            $table->index('badge_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('access_requests', function (Blueprint $table) {
            $table->dropIndex(['badge_number']);
            $table->dropColumn('badge_number');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['badge_number']);
            $table->dropColumn('badge_number');
        });
    }
};
