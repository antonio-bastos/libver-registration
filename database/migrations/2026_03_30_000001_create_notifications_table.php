<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('channel', 40)->default('email');
            $table->string('recipient', 255);
            $table->string('template', 80);
            $table->string('dedupe_hash', 64)->unique();
            $table->json('payload_json')->nullable();
            $table->string('status', 30)->default('queued');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('template');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};

