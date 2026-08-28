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
            $table->foreignId('category_id')
                  ->constrained('categories')
                  ->restrictOnDelete();
            $table->string('name', 255);
            $table->string('slug', 255)->unique();
            $table->text('description')->nullable();
            $table->string('image', 255);
            $table->json('types')->nullable();
            $table->string('weight', 50)->default('500g');
            $table->unsignedInteger('weight_value')->nullable();
            $table->enum('unit', ['gram', 'kg', 'pcs', 'pack'])->default('gram');
            $table->decimal('normal_price', 12, 2);
            $table->enum('discount_type', ['percentage', 'fixed'])->nullable();
            $table->decimal('discount_value', 12, 2)->nullable();
            $table->enum('stock_status', ['READY_STOCK', 'OUT_OF_STOCK', 'PRE_ORDER'])->default('READY_STOCK');
            $table->boolean('is_flash_sale')->default(false);
            $table->enum('flash_sale_discount_type', ['percentage', 'fixed'])->nullable();
            $table->decimal('flash_sale_discount_value', 12, 2)->nullable();
            $table->integer('flash_sale_sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            // Composite indexes for fast catalog and promo queries
            $table->index(['category_id', 'is_active', 'sort_order'], 'products_category_active_idx');
            $table->index(['is_flash_sale', 'is_active', 'flash_sale_sort_order'], 'products_flash_sale_idx');
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
