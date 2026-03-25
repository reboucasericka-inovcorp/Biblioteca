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
        Schema::create('requisitions', function (Blueprint $table) {
            $table->id();

        $table->string('sequential_number')->unique();

        $table->foreignId('user_id')
              ->constrained()
              ->cascadeOnDelete();

        $table->foreignId('book_id')
              ->constrained()
              ->cascadeOnDelete();

        $table->dateTime('request_date');
        $table->dateTime('due_date');
        $table->dateTime('return_date')->nullable();

        // Squashed do add_pending_rejected_to_requisitions_status.php
        // (SQLite não suporta ENUM com CHECK de forma consistente; mantemos como string)
        $table->string('status', 32)->default('active')->index();

        $table->integer('days_elapsed')->nullable();

        $table->string('photo_path')->nullable();

        $table->timestamps();

        // Índices estratégicos
        $table->index('user_id');
        $table->index('book_id');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requisitions');
    }
};
