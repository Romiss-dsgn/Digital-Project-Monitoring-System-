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
            $table->string('department')->nullable()->after('badge_number');
            $table->string('requested_role')->nullable()->after('department');
            $table->string('access_status')->default('approved')->after('is_active');
            $table->timestamp('access_requested_at')->nullable()->after('access_status');
            $table->timestamp('access_approved_at')->nullable()->after('access_requested_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'badge_number',
                'department',
                'requested_role',
                'access_status',
                'access_requested_at',
                'access_approved_at',
            ]);
        });
    }
};
