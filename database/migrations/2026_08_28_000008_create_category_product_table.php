<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create Pivot Table category_product
        Schema::create('category_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')
                  ->constrained('products')
                  ->cascadeOnDelete();
            $table->foreignId('category_id')
                  ->constrained('categories')
                  ->cascadeOnDelete();
            $table->timestamps();

            // Composite unique constraint: prevent duplicate category assignments for the same product
            $table->unique(['product_id', 'category_id'], 'category_product_unique');
        });

        // 2. Safe Backfill: Populate existing product category_id relations into pivot table
        $existingProducts = DB::table('products')
            ->whereNotNull('category_id')
            ->select('id', 'category_id')
            ->get();

        $now = now();
        $records = [];
        foreach ($existingProducts as $product) {
            $records[] = [
                'product_id' => $product->id,
                'category_id' => $product->category_id,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (!empty($records)) {
            DB::table('category_product')->insertOrIgnore($records);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_product');
    }
};
