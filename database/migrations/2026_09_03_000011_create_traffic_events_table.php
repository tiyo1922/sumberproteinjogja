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
        Schema::create('traffic_events', function (Blueprint $table) {
            $table->id();
            $table->string('visitor_id', 64);
            $table->string('event_type', 30)->default('pageview'); // pageview, chat_admin, pesan_order_wa
            $table->string('page_path', 255)->default('/');
            $table->string('source', 30)->default('direct'); // meta_ads, google_organic, direct, referral
            $table->string('utm_source', 100)->nullable();
            $table->string('utm_medium', 100)->nullable();
            $table->string('utm_campaign', 100)->nullable();
            $table->string('device', 20)->default('unknown'); // mobile, desktop, tablet, unknown
            $table->timestamp('created_at')->useCurrent();

            // Query optimization indexes for dashboard analytics
            $table->index(['created_at', 'event_type', 'source'], 'idx_traffic_aggregation');
            $table->index(['visitor_id', 'created_at'], 'idx_visitor_lookup');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('traffic_events');
    }
};
