<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The finalized ConTrackPro schema already creates audit_logs. Keep this
        // migration idempotent so older branches can merge without resetting data.
        if (Schema::hasTable("audit_logs")) {
            return;
        }

        Schema::create("audit_logs", function (Blueprint $table) {
            $table->id();
            $table->foreignId("user_id")->nullable()->constrained()->nullOnDelete();
            $table->string("action");
            $table->string("module");
            $table->unsignedBigInteger("record_id")->nullable();
            $table->string("record_code")->nullable();
            $table->json("old_values")->nullable();
            $table->json("new_values")->nullable();
            $table->string("ip_address")->nullable();
            $table->text("user_agent")->nullable();
            $table->text("remarks")->nullable();
            $table->timestamp("performed_at")->useCurrent();

            $table->index(["module", "record_id"]);
        });
    }

    public function down(): void
    {
        // No-op: audit_logs belongs to the finalized base schema migration.
    }
};
