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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('shipping_name');
            $table->string('shipping_address');
            $table->string('shipping_city');
            $table->string('shipping_state')->nullable();
            $table->string('shipping_zipcode');
            $table->string('shipping_country');
            $table->string('shipping_phone');
            $table->decimal('subtotal', 15, 2);
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('shipping_cost', 10, 2)->default(0);
            $table->string('payment_method')->default('cod'); // e.g., cod, credit_card, paypal
            $table->string('payment_status')->default('pending'); // e.g., pending, paid, failed
            $table->string('order_number')->unique();
            $table->text('notes')->nullable();
            $table->string('transaction_id')->nullable()->after('payment_status');
            $table->timestamp('paid_at')->nullable()->after('transaction_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order', function (Blueprint $table) {
            //
        });
    }
};
