<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fix engineering_plans table issues:
     *
     * 1. status default was 'Uploaded' (capital U) — the app writes lowercase
     *    snake_case values ('for_review', 'approved', 'revision') so every new
     *    row gets a default that doesn't match what the frontend expects.
     *
     * 2. Add a CHECK constraint so invalid status values can never be written
     *    even if validation is bypassed (direct SQL, seeders, future bugs).
     *
     * NOTE: uploaded_at already exists in the original migration — no change needed.
     */
    public function up(): void
    {
        Schema::table('engineering_plans', function (Blueprint $table) {
            // Fix 1 — correct the status default to match the app's actual values
            $table->string('status')->default('for_review')->change();
        });

        // Fix 2 — enforce valid status values at the DB level
        // Works on MySQL 8.0.16+ and MariaDB 10.2.1+
        DB::statement("
            ALTER TABLE engineering_plans
            ADD CONSTRAINT chk_ep_status
            CHECK (status IN ('for_review', 'approved', 'revision', 'uploaded'))
        ");
    }

    public function down(): void
    {
        // Remove CHECK constraint first before reverting the column
        DB::statement(
            'ALTER TABLE engineering_plans DROP CONSTRAINT chk_ep_status'
        );

        Schema::table('engineering_plans', function (Blueprint $table) {
            // Revert to original default
            $table->string('status')->default('Uploaded')->change();
        });
    }
};