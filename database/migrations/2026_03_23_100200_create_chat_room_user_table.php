<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_room_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_room_id')->constrained('chat_rooms')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            // Campo squashed do add_chat_role_to_chat_room_user_table.php
            $table->string('role', 32)->default('member');

            $table->unique(['chat_room_id', 'user_id']);

            // Índice squashed do add_chat_role_to_chat_room_user_table.php
            $table->index(['chat_room_id', 'role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_room_user');
    }
};
