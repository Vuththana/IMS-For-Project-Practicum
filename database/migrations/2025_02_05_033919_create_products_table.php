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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('attachments')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 8, 2);
            $table->boolean('purchasable')->default(false);
            $table->boolean('sellable')->default(false);
            $table->string('sku')->unique();
            $table->enum('unit_type', ['kg', 'g', 'box', 'l', 'ml', 'piece', 'pack'])->default('piece');
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('subcategory_id')->nullable()->constrained('sub_categories')->nullOnDelete();
            $table->foreignId('brand_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();   

            $table->string('barcode')->nullable()->unique();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
