<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->string('google_volume_id')->nullable()->unique()->after('id');
            $table->string('isbn_13')->nullable()->unique()->after('isbn');
            $table->string('thumbnail_url')->nullable()->after('cover');
            $table->string('published_date')->nullable()->after('bibliography');
        });
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn(['google_volume_id', 'isbn_13', 'thumbnail_url', 'published_date']);
        });
    }
};
