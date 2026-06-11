<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * This migration is intentionally retained for local migration history.
     *
     * The finalized ConTrackPro schema stores access request data in the
     * access_requests table instead of adding request-only fields to users.
     */
    public function up(): void
    {
        //
    }

    public function down(): void
    {
        //
    }
};
