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
        Schema::table('books', function (Blueprint $table) {
            $table->decimal('discount', 5, 2)->nullable()->default(0);
            $table->unsignedInteger('pages')->nullable();
            $table->string('language', 50)->nullable();
            $table->string('dimensions', 100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn(['discount', 'pages', 'language', 'dimensions']);
        });
    }
};
