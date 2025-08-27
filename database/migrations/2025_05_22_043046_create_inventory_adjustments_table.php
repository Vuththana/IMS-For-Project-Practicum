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
        Schema::create('inventory_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('batch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['add', 'remove', 'initial_stock_correction', 'return_sale', 'return_purchase', 'damage', 'spoilage', 'theft', 'other']);
            $table->integer('adjusted_quantity');
            $table->text('reason')->nullable();
            $table->foreignId('related_sale_id')->nullable()->constrained('sales')->nullOnDelete(); // Optional
            $table->foreignId('related_purchase_id')->nullable()->constrained('purchases')->nullOnDelete(); // Optional
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_adjustments');
    }
};
