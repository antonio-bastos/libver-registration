<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('surname')->nullable()->after('name');
            $table->string('phone', 40)->nullable()->after('email');
            $table->string('card_number', 60)->nullable()->after('phone');
            $table->date('dob')->nullable()->after('card_number');
            $table->string('role')->default('parent')->after('password');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['surname', 'phone', 'card_number', 'dob', 'role']);
        });
    }
};
