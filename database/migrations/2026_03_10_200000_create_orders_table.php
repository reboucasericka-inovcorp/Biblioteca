<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('email')->nullable();
            $table->decimal('total', 10, 2);
            $table->string('status', 20)->default('pending');
            $table->string('stripe_session_id')->nullable()->unique();
            $table->timestamps();

            // Campos squashed do add_shipping_to_orders_table.php
            $table->string('shipping_address')->nullable();
            $table->string('shipping_city')->nullable();
            $table->string('shipping_postal_code')->nullable();
            $table->string('shipping_country', 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
