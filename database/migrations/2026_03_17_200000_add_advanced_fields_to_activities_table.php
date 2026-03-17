<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('status');
            $table->boolean('is_archived')->default(false)->after('is_active');
            $table->decimal('fee', 8, 2)->nullable()->after('capacity');
            $table->boolean('is_paid')->default(false)->after('fee');
            $table->string('online_url')->nullable()->after('location');
            $table->string('live_stream_url')->nullable()->after('online_url');
            $table->string('activity_subtype')->nullable()->after('type');
            $table->text('connection_details')->nullable()->after('live_stream_url');
            $table->unsignedInteger('seating_capacity')->nullable()->after('capacity');
            $table->boolean('numbered_seating')->default(false)->after('seating_capacity');
        });
    }

    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropColumn([
                'is_active',
                'is_archived',
                'fee',
                'is_paid',
                'online_url',
                'live_stream_url',
                'activity_subtype',
                'connection_details',
                'seating_capacity',
                'numbered_seating',
            ]);
        });
    }
};
