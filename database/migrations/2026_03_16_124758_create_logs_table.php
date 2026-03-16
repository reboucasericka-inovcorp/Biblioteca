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
        Schema::create('logs', function (Blueprint $table) {
            $table->id();
            $table->date('log_date');
            $table->time('log_time');
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('module');
            $table->unsignedBigInteger('object_id')->nullable();
            $table->text('change');
            $table->ipAddress('ip')->nullable();
            $table->string('browser')->nullable();
            $table->timestamps();
            
            // Índices para melhor performance nas buscas
            $table->index('log_date');
            $table->index('user_id');
            $table->index('module');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logs');
    }
};
