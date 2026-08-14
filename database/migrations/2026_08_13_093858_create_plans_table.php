<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();           // starter, pro, agency, internal
            $table->string('name');                    // display name from payment provider
            $table->string('stripe_price_id')->nullable()->unique(); // price_... from Stripe (null for internal)
            $table->unsignedInteger('unit_amount');    // price in smallest currency unit (centavos, cents…)
            $table->string('currency', 3);             // MXN, USD…
            $table->string('interval', 10);            // month | year
            $table->unsignedInteger('quota');          // max scans per billing period
            $table->unsignedTinyInteger('sort_order')->default(0); // display order in billing page
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
