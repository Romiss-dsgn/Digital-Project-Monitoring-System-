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
            $table->foreign('role_id')->references('id')->on('roles')->nullOnDelete();
        });

        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->string('module');
            $table->boolean('can_view')->default(false);
            $table->boolean('can_create')->default(false);
            $table->boolean('can_edit')->default(false);
            $table->boolean('can_delete')->default(false);
            $table->boolean('can_approve')->default(false);
            $table->boolean('can_export')->default(false);
            $table->timestamps();

            $table->unique(['role_id', 'module']);
        });

        Schema::create('access_requests', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('email');
            $table->string('contact_number')->nullable();
            $table->string('position')->nullable();
            $table->string('office_unit')->nullable();
            $table->foreignId('requested_role_id')->nullable()->constrained('roles')->nullOnDelete();
            $table->text('reason')->nullable();
            $table->string('status')->default('Pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('token_hash');
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->timestamps();
        });

        Schema::create('contractors', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->string('contact_person')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('license_number')->nullable()->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('project_code')->unique();
            $table->string('project_name');
            $table->text('description')->nullable();
            $table->string('location')->nullable();
            $table->string('project_type')->nullable();
            $table->string('funding_source')->nullable();
            $table->decimal('approved_budget', 15, 2)->default(0);
            $table->string('phase')->nullable();
            $table->string('status')->default('Planning');
            $table->decimal('progress_percent', 5, 2)->default(0);
            $table->date('target_start_date')->nullable();
            $table->date('target_end_date')->nullable();
            $table->date('actual_start_date')->nullable();
            $table->date('actual_end_date')->nullable();
            $table->string('implementing_office')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_archived')->default(false);
            $table->timestamps();
        });

        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->string('contract_number')->unique();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contractor_id')->constrained()->restrictOnDelete();
            $table->string('contract_title');
            $table->string('contract_type')->nullable();
            $table->decimal('original_contract_amount', 15, 2)->default(0);
            $table->decimal('revised_contract_amount', 15, 2)->default(0);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->date('notice_to_proceed_date')->nullable();
            $table->date('signed_date')->nullable();
            $table->unsignedInteger('duration_days')->nullable();
            $table->string('status')->default('Draft');
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->boolean('is_archived')->default(false);
            $table->timestamps();
        });

        Schema::create('contract_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
            $table->string('document_title');
            $table->string('document_category')->nullable();
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type')->nullable();
            $table->string('version')->nullable();
            $table->string('status')->default('Uploaded');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('uploaded_at')->nullable();
            $table->text('remarks')->nullable();
            $table->boolean('is_archived')->default(false);
            $table->timestamps();
        });

        Schema::create('project_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('document_title');
            $table->string('document_category')->nullable();
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type')->nullable();
            $table->string('version')->nullable();
            $table->string('status')->default('Uploaded');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('uploaded_at')->nullable();
            $table->text('remarks')->nullable();
            $table->boolean('is_archived')->default(false);
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
            $table->string('file_type')->nullable();
            $table->string('status')->default('Uploaded');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('uploaded_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('remarks')->nullable();
            $table->boolean('is_archived')->default(false);
            $table->timestamps();
        });

        Schema::create('cashflow_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
            $table->string('period_label');
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->decimal('planned_amount', 15, 2)->default(0);
            $table->decimal('actual_amount', 15, 2)->default(0);
            $table->decimal('variance', 15, 2)->default(0);
            $table->string('status')->default('On Track');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cashflow_period_id')->nullable()->constrained('cashflow_periods')->nullOnDelete();
            $table->string('invoice_number')->unique();
            $table->string('billing_period')->nullable();
            $table->decimal('invoice_amount', 15, 2)->default(0);
            $table->date('invoice_date')->nullable();
            $table->date('due_date')->nullable();
            $table->string('status')->default('Pending');
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->string('payment_reference_no')->nullable();
            $table->decimal('amount_paid', 15, 2)->default(0);
            $table->date('payment_date')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('payment_status')->default('Paid');
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('invoice_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->string('document_title');
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('uploaded_at')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('variation_orders', function (Blueprint $table) {
            $table->id();
            $table->string('vo_number')->unique();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
            $table->text('description')->nullable();
            $table->text('reason')->nullable();
            $table->decimal('amount_change', 15, 2)->default(0);
            $table->integer('time_impact_days')->nullable();
            $table->string('status')->default('Draft');
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_remarks')->nullable();
            $table->boolean('is_archived')->default(false);
            $table->timestamps();
        });

        Schema::create('variation_order_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('variation_order_id')->constrained()->cascadeOnDelete();
            $table->string('document_title');
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('uploaded_at')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('contract_time_extensions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
            $table->string('extension_number');
            $table->text('reason')->nullable();
            $table->date('original_end_date')->nullable();
            $table->date('requested_new_end_date')->nullable();
            $table->date('approved_new_end_date')->nullable();
            $table->integer('additional_days')->nullable();
            $table->string('status')->default('Draft');
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('requested_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('remarks')->nullable();
            $table->boolean('is_archived')->default(false);
            $table->timestamps();
        });

        Schema::create('work_suspensions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
            $table->string('suspension_number');
            $table->text('reason')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->integer('duration_days')->nullable();
            $table->string('status')->default('Active');
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('issued_at')->nullable();
            $table->foreignId('lifted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('lifted_at')->nullable();
            $table->text('remarks')->nullable();
            $table->boolean('is_archived')->default(false);
            $table->timestamps();
        });

        Schema::create('project_accomplishments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('milestone_title');
            $table->text('description')->nullable();
            $table->date('target_date')->nullable();
            $table->date('completion_date')->nullable();
            $table->decimal('percent_complete', 5, 2)->default(0);
            $table->string('status')->default('Not Started');
            $table->foreignId('reported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('validated_at')->nullable();
            $table->text('remarks')->nullable();
            $table->boolean('is_archived')->default(false);
            $table->timestamps();
        });

        Schema::create('accomplishment_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_accomplishment_id')->constrained()->cascadeOnDelete();
            $table->string('document_title');
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('uploaded_at')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('contractor_performance_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contractor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
            $table->date('evaluation_period_start')->nullable();
            $table->date('evaluation_period_end')->nullable();
            $table->decimal('completion_rate', 5, 2)->default(0);
            $table->decimal('timeline_compliance', 5, 2)->default(0);
            $table->unsignedInteger('vo_frequency')->default(0);
            $table->decimal('accomplishment_score', 5, 2)->default(0);
            $table->decimal('overall_rating', 5, 2)->default(0);
            $table->string('rating_label')->nullable();
            $table->string('formula_version')->nullable();
            $table->foreignId('evaluated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('evaluated_at')->nullable();
            $table->text('remarks')->nullable();
            $table->boolean('is_archived')->default(false);
            $table->timestamps();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('message');
            $table->string('type')->nullable();
            $table->string('reference_module')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['reference_module', 'reference_id']);
        });

        Schema::create('notification_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notification_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->unique(['notification_id', 'user_id']);
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action');
            $table->string('module');
            $table->unsignedBigInteger('record_id')->nullable();
            $table->string('record_code')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->text('remarks')->nullable();
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
        Schema::dropIfExists('notification_recipients');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('contractor_performance_ratings');
        Schema::dropIfExists('accomplishment_documents');
        Schema::dropIfExists('project_accomplishments');
        Schema::dropIfExists('work_suspensions');
        Schema::dropIfExists('contract_time_extensions');
        Schema::dropIfExists('variation_order_documents');
        Schema::dropIfExists('variation_orders');
        Schema::dropIfExists('invoice_documents');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('cashflow_periods');
        Schema::dropIfExists('engineering_plans');
        Schema::dropIfExists('project_documents');
        Schema::dropIfExists('contract_documents');
        Schema::dropIfExists('contracts');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('contractors');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('access_requests');
        Schema::dropIfExists('role_permissions');

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
        });

        Schema::dropIfExists('roles');
    }
};
