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
        Schema::create('knowledge_articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')
                  ->constrained('knowledge_categories')
                  ->restrictOnDelete();
            $table->string('title', 255);
            $table->string('slug', 255)->unique();
            $table->text('excerpt')->nullable();
            $table->longText('content'); // Canonical Schema v1 JSON AST Document
            $table->string('image', 255)->nullable();
            $table->enum('status', ['draft', 'published', 'archived'])->default('published');
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            // Composite index for fast published articles retrieval by category
            $table->index(['category_id', 'status', 'sort_order'], 'know_arts_cat_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('knowledge_articles');
    }
};
