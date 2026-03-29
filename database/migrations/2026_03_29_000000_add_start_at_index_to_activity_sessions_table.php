<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_sessions', function (Blueprint $table) {
            $table->index('start_at', 'activity_sessions_start_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('activity_sessions', function (Blueprint $table) {
            $table->dropIndex('activity_sessions_start_at_index');
        });
    }
};
