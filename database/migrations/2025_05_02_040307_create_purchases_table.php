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
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->string('reference')->unique();
            $table->string('invoice_number')->nullable();
            $table->date('purchase_date');
            $table->date('expected_delivery_date');
            $table->enum('status', ['pending', 'received', 'cancelled'])->default('pending');
            $table->boolean('is_paid')->default(false);
            $table->enum('payment_method', ['cash', 'card', 'transfer'])->nullable();
            $table->date('received_date')->nullable();
            $table->date('payment_date')->nullable();
            $table->decimal('subtotal', 12, 2);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('delivery_fee', 12, 2)->default(0);
            $table->decimal('total_cost', 12, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
