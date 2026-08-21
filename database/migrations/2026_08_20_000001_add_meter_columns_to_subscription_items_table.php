<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Required by Laravel Cashier 16.x (added in v15.5 / 2025-06).
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscription_items', function (Blueprint $table) {
            $table->string('meter_id')->nullable()->after('stripe_price');
            $table->string('meter_event_name')->nullable()->after('meter_id');
        });
    }

    public function down(): void
    {
        Schema::table('subscription_items', function (Blueprint $table) {
            $table->dropColumn(['meter_id', 'meter_event_name']);
        });
    }
};
