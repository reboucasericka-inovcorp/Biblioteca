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
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('isbn')->unique();
            $table->string('name');
            $table->text('bibliography')->nullable();
            $table->string('cover')->nullable();
            $table->decimal('price', 8, 2);
            $table->foreignId('publisher_id')->constrained('publishers');
            $table->timestamps();

            // Campos squashed do add_google_books_columns_to_books_table
            $table->string('google_volume_id')->nullable()->unique();
            $table->string('isbn_13')->nullable()->unique();
            $table->string('thumbnail_url')->nullable();
            $table->string('published_date')->nullable();

            // Campo squashed do add_file_path_to_books_table
            $table->string('file_path')->nullable();

            // Campos squashed do add_ecommerce_fields_to_books_table
            $table->decimal('discount', 5, 2)->nullable()->default('0');
            $table->unsignedInteger('pages')->nullable();
            $table->string('language', 50)->nullable();
            $table->string('dimensions', 100)->nullable();

            // Campos squashed do add_stock_and_is_active_to_books_table
            $table->unsignedInteger('stock')->default('0');
            $table->boolean('is_active')->default('1');

            // Campo squashed do add_reserved_stock_to_books_table
            $table->unsignedInteger('reserved_stock')->default('0');

            // Campo squashed do stock_reconciliation_and_adjustments (se ainda não existir)
            $table->boolean('stock_reconciled')->default('0');

            // Índices squashed do add_index_reserved_stock_to_books_table e do add_created_at_index_to_books_table
            $table->index('reserved_stock');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
