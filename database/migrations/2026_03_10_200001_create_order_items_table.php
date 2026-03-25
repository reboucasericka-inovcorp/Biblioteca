<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 8, 2);
            $table->decimal('total', 10, 2);
            $table->timestamps();

            // Campos squashed do add_book_title_to_order_items_table.php e do add_book_cover_to_order_items_table.php
            $table->string('book_title')->nullable();
            $table->string('book_cover')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
