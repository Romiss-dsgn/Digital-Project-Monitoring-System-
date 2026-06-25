<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // These composite indexes match the list and summary filters used by both modules.
        Schema::table('contracts', function (Blueprint $table) {
            $table->index(['is_archived', 'status'], 'contracts_archive_status_idx');
            $table->index(['is_archived', 'start_date', 'end_date'], 'contracts_archive_dates_idx');
        });

        Schema::table('contract_documents', function (Blueprint $table) {
            $table->index(['contract_id', 'is_archived', 'status'], 'contract_docs_summary_idx');
        });

        Schema::table('project_accomplishments', function (Blueprint $table) {
            $table->index(['is_archived', 'status', 'target_date'], 'accomplishments_filter_idx');
        });
    }

    public function down(): void
    {
        Schema::table('project_accomplishments', function (Blueprint $table) {
            $table->dropIndex('accomplishments_filter_idx');
        });

        Schema::table('contract_documents', function (Blueprint $table) {
            $table->dropIndex('contract_docs_summary_idx');
        });

        Schema::table('contracts', function (Blueprint $table) {
            $table->dropIndex('contracts_archive_status_idx');
            $table->dropIndex('contracts_archive_dates_idx');
        });
    }
};
