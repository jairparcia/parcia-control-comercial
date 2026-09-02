<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('stripe_id')->unique();
            $table->string('stripe_customer_id')->nullable()->index();
            $table->string('invoice_number')->nullable();
            $table->integer('total_cents');
            $table->string('currency', 3);
            $table->string('status');
            $table->string('billing_interval')->nullable();
            $table->unsignedTinyInteger('billing_interval_count')->default(1);
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();
            $table->timestamp('due_date')->nullable();
            $table->timestamp('stripe_created_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
