<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->decimal('previous_accomplishment_percent', 5, 2)->nullable()->after('contract_id');
            $table->decimal('accomplishment_today_percent', 5, 2)->nullable()->after('previous_accomplishment_percent');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['previous_accomplishment_percent', 'accomplishment_today_percent']);
        });
    }
};