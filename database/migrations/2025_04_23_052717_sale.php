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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('deliverer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('cash_register_session_id')->nullable()->constrained()->nullOnDelete();
            $table->string('invoice_number')->unique();
            $table->date('sale_date');
            $table->enum('payment_method', ['cash', 'card', 'transfer']);
            $table->enum('delivery_status', ['pending', 'delivered', 'cancelled']);
            $table->decimal('subtotal', 10, 2);
            $table->enum('status', ['draft', 'completed', 'cancelled', 'returned', 'refunded']);
            $table->decimal('delivery_fee', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('tax', 10, 2)->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */ 
    public function down(): void
    {
        //
    }
};
