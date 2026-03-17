<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description_html')->nullable();
            $table->string('type')->default('workshop');
            $table->string('age_group')->nullable();
            $table->string('status')->default('active');
            $table->unsignedInteger('capacity')->nullable();
            $table->boolean('waitlist_enabled')->default(true);
            $table->timestamp('reg_start_at')->nullable();
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->string('location')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
