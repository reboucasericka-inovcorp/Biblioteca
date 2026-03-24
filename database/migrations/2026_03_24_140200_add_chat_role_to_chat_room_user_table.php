<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chat_room_user', function (Blueprint $table) {
            $table->string('role', 32)->default('member')->after('user_id');
            $table->index(['chat_room_id', 'role']);
        });
    }

    public function down(): void
    {
        Schema::table('chat_room_user', function (Blueprint $table) {
            $table->dropIndex(['chat_room_id', 'role']);
            $table->dropColumn('role');
        });
    }
};
