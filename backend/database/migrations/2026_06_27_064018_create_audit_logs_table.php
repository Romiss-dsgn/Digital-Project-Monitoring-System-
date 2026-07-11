<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable("audit_logs")) {
            Schema::create("audit_logs", function (Blueprint $table) {
                $table->id();
                $table->foreignId("user_id")->nullable()->constrained("users")->nullOnDelete();
                $table->string("user_name")->nullable();
                $table->string("user_role")->nullable();
                $table->string("module")->nullable();
                $table->string("action");
                $table->string("record_affected")->nullable();
                $table->text("remarks")->nullable();
                $table->string("ip_address")->nullable();
                $table->string("user_agent")->nullable();
                $table->timestamps();
            });
        }
    }
    public function down(): void
    {
        // No-op: audit_logs belongs to the finalized base schema migration.
    }
};