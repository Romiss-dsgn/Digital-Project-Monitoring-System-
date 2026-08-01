<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('variation_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('variation_order_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('line_number')->nullable();
            $table->text('item_description');
            $table->decimal('original_qty', 12, 3)->default(0);
            $table->string('original_unit')->nullable();
            $table->decimal('original_unit_cost', 15, 2)->default(0);
            $table->decimal('original_total_cost', 15, 2)->default(0);
            $table->decimal('additive_qty', 12, 3)->default(0);
            $table->string('additive_unit')->nullable();
            $table->decimal('additive_unit_cost', 15, 2)->default(0);
            $table->decimal('additive_total_cost', 15, 2)->default(0);
            $table->decimal('deductive_qty', 12, 3)->default(0);
            $table->string('deductive_unit')->nullable();
            $table->decimal('deductive_unit_cost', 15, 2)->default(0);
            $table->decimal('deductive_total_cost', 15, 2)->default(0);
            $table->decimal('net_line_cost', 15, 2)->default(0);
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['variation_order_id', 'line_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('variation_order_items');
    }
};
