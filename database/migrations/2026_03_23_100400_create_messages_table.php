<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('body');
            $table->morphs('messageable');
            $table->timestamp('read_at')->nullable();
            $table->string('type')->default('text');
            $table->timestamps();

            // Índices squashed do add_chat_query_indexes_to_messages_table.php
            $table->index(['messageable_type', 'messageable_id', 'created_at'], 'messages_target_created_at_index');
            $table->index(['user_id', 'created_at'], 'messages_user_created_at_index');
            $table->index(['read_at', 'created_at'], 'messages_read_at_created_at_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
