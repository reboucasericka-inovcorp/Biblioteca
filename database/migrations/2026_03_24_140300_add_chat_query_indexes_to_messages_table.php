<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->index(['messageable_type', 'messageable_id', 'created_at'], 'messages_target_created_at_index');
            $table->index(['user_id', 'created_at'], 'messages_user_created_at_index');
            $table->index(['read_at', 'created_at'], 'messages_read_at_created_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex('messages_target_created_at_index');
            $table->dropIndex('messages_user_created_at_index');
            $table->dropIndex('messages_read_at_created_at_index');
        });
    }
};
