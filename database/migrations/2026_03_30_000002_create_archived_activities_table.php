<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('archived_activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('original_activity_id')->nullable()->index();
            $table->string('title');
            $table->text('description_html')->nullable();
            $table->string('type')->default('workshop');
            $table->string('activity_subtype')->nullable();
            $table->string('age_group')->nullable();
            $table->string('status')->default('inactive');
            $table->boolean('is_paid')->default(false);
            $table->decimal('fee', 8, 2)->nullable();
            $table->unsignedInteger('capacity')->nullable();
            $table->unsignedInteger('seating_capacity')->nullable();
            $table->boolean('numbered_seating')->default(false);
            $table->boolean('waitlist_enabled')->default(true);
            $table->boolean('requires_selection')->default(false);
            $table->boolean('first_timers_only')->default(false);
            $table->boolean('is_space_booking')->default(false);
            $table->timestamp('reg_start_at')->nullable();
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->string('location')->nullable();
            $table->string('online_url')->nullable();
            $table->string('live_stream_url')->nullable();
            $table->text('connection_details')->nullable();
            $table->text('materials_list')->nullable();
            $table->text('custom_message_postpone')->nullable();
            $table->string('certificate_template')->nullable();
            $table->unsignedInteger('auto_archive_days')->default(30);
            $table->string('start_time_label')->nullable();
            $table->json('metadata_json')->nullable();
            $table->timestamp('archived_at')->useCurrent();
            $table->timestamps();

            $table->index(['start_at', 'end_at']);
            $table->index('archived_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('archived_activities');
    }
};
