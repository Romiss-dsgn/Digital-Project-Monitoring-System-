<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_accomplishments', function (Blueprint $table) {
            if (! Schema::hasColumn('project_accomplishments', 'report_period')) {
                $table->date('report_period')->nullable()->after('target_date');
            }

            if (! Schema::hasColumn('project_accomplishments', 'expected_percent')) {
                $table->decimal('expected_percent', 5, 2)->default(0)->after('percent_complete');
            }

            if (! Schema::hasColumn('project_accomplishments', 'variance_percent')) {
                $table->decimal('variance_percent', 6, 2)->default(0)->after('expected_percent');
            }

            if (! Schema::hasColumn('project_accomplishments', 'elapsed_days')) {
                $table->unsignedInteger('elapsed_days')->nullable()->after('variance_percent');
            }

            if (! Schema::hasColumn('project_accomplishments', 'duration_days')) {
                $table->unsignedInteger('duration_days')->nullable()->after('elapsed_days');
            }

            if (! Schema::hasColumn('project_accomplishments', 'formula_version')) {
                $table->string('formula_version', 80)->nullable()->after('duration_days');
            }
        });
    }

    public function down(): void
    {
        Schema::table('project_accomplishments', function (Blueprint $table) {
            foreach ([
                'formula_version',
                'duration_days',
                'elapsed_days',
                'variance_percent',
                'expected_percent',
                'report_period',
            ] as $column) {
                if (Schema::hasColumn('project_accomplishments', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
