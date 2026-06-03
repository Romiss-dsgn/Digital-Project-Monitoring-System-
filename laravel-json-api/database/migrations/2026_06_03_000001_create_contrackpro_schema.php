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
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->unique()->after('id');
            $table->foreignId('role_id')->nullable()->after('password')->constrained()->nullOnDelete();
            $table->boolean('is_active')->default(true)->after('role_id');
        });

        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->string('module');
            $table->boolean('can_view')->default(false);
            $table->boolean('can_create')->default(false);
            $table->boolean('can_edit')->default(false);
            $table->boolean('can_delete')->default(false);
            $table->timestamps();

            $table->unique(['role_id', 'module']);
        });

        Schema::create('contractors', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->string('contact_person')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('license_number')->nullable()->unique();
            $table->decimal('performance_rating', 5, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('project_code')->unique();
            $table->string('project_name');
            $table->text('description')->nullable();
            $table->string('location')->nullable();
            $table->string('phase')->nullable();
            $table->string('status')->default('pending');
            $table->date('target_start_date')->nullable();
            $table->date('target_end_date')->nullable();
            $table->date('actual_start_date')->nullable();
            $table->date('actual_end_date')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->string('contract_number')->unique();
            $table->foreignId('contractor_id')->constrained()->restrictOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('contract_title');
            $table->decimal('contract_amount', 15, 2)->default(0);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('status')->default('draft');
            $table->date('signed_date')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('contract_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('uploaded_at')->nullable();
            $table->timestamps();
        });

        Schema::create('project_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type')->nullable();
            $table->string('document_category')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('uploaded_at')->nullable();
            $table->timestamps();
        });

        Schema::create('engineering_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('plan_title');
            $table->string('plan_type')->nullable();
            $table->string('version')->nullable();
            $table->string('file_name');
            $table->string('file_path');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('uploaded_at')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('cashflows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
            $table->decimal('total_budget', 15, 2)->default(0);
            $table->decimal('total_expenditure', 15, 2)->default(0);
            $table->decimal('variance', 15, 2)->default(0);
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cashflow_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
            $table->string('invoice_number')->unique();
            $table->decimal('amount', 15, 2)->default(0);
            $table->date('invoice_date')->nullable();
            $table->date('due_date')->nullable();
            $table->string('payment_status')->default('unpaid');
            $table->date('payment_date')->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('invoice_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->string('file_name');
            $table->string('file_path');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('uploaded_at')->nullable();
            $table->timestamps();
        });

        Schema::create('variation_orders', function (Blueprint $table) {
            $table->id();
            $table->string('vo_number')->unique();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
            $table->text('description')->nullable();
            $table->decimal('amount_change', 15, 2)->default(0);
            $table->text('reason')->nullable();
            $table->string('status')->default('submitted');
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->boolean('is_archived')->default(false);
            $table->timestamps();
        });

        Schema::create('variation_order_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('variation_order_id')->constrained()->cascadeOnDelete();
            $table->string('file_name');
            $table->string('file_path');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('uploaded_at')->nullable();
            $table->timestamps();
        });

        Schema::create('project_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('milestone_title');
            $table->text('description')->nullable();
            $table->decimal('percent_complete', 5, 2)->default(0);
            $table->string('status')->default('pending');
            $table->date('accomplished_date')->nullable();
            $table->foreignId('reported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('accomplishment_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('accomplishment_id')->constrained('project_progress')->cascadeOnDelete();
            $table->string('file_name');
            $table->string('file_path');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('uploaded_at')->nullable();
            $table->timestamps();
        });

        Schema::create('contractor_performance_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contractor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
            $table->decimal('completion_rate', 5, 2)->default(0);
            $table->decimal('timeline_compliance', 5, 2)->default(0);
            $table->unsignedInteger('vo_frequency')->default(0);
            $table->decimal('accomplishment_score', 5, 2)->default(0);
            $table->decimal('overall_rating', 5, 2)->default(0);
            $table->string('rating_label')->nullable();
            $table->foreignId('evaluated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('evaluated_at')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('message');
            $table->string('type')->nullable();
            $table->string('reference_module')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['reference_module', 'reference_id']);
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action');
            $table->string('module');
            $table->unsignedBigInteger('record_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('performed_at')->useCurrent();

            $table->index(['module', 'record_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('contractor_performance_ratings');
        Schema::dropIfExists('accomplishment_documents');
        Schema::dropIfExists('project_progress');
        Schema::dropIfExists('variation_order_documents');
        Schema::dropIfExists('variation_orders');
        Schema::dropIfExists('invoice_documents');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('cashflows');
        Schema::dropIfExists('engineering_plans');
        Schema::dropIfExists('project_documents');
        Schema::dropIfExists('contract_documents');
        Schema::dropIfExists('contracts');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('contractors');
        Schema::dropIfExists('role_permissions');

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn(['username', 'role_id', 'is_active']);
        });

        Schema::dropIfExists('roles');
    }
};
