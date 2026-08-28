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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->string('reviewer_name', 150);
            $table->string('reviewer_title', 150)->nullable();
            $table->string('reviewer_location', 100)->nullable();
            $table->text('review_text');
            $table->unsignedTinyInteger('rating')->default(5);
            $table->timestamp('reviewed_at')->useCurrent();
            $table->string('avatar', 255)->nullable();
            $table->enum('source', ['manual', 'google'])->default('manual');
            $table->string('google_review_id', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            // Indexes for active review resolution and Google review deduplication
            $table->index(['is_active', 'source', 'sort_order'], 'reviews_active_source_sort_idx');
            $table->index('google_review_id', 'reviews_google_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
