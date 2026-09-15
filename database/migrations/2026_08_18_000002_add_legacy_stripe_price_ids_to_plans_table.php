<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            // Accumulates replaced Stripe price IDs so resolvePlan() never
            // loses track of subscribers who signed up at an old price.
            $table->json('legacy_stripe_price_ids')->nullable()->after('stripe_price_id');
        });
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn('legacy_stripe_price_ids');
        });
    }
};
