<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('activity_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('child_id')->nullable()->change();
            
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
            $table->foreignId('child_id')->nullable(false)->change();
        });
    }
};
