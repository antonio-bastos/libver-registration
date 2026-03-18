<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add missing fields to `activities`
        Schema::table('activities', function (Blueprint $table) {
            $table->boolean('requires_selection')->default(false)->after('status')->comment('For unlimited interest registration');
            $table->boolean('first_timers_only')->default(false)->after('requires_selection');
            $table->text('materials_list')->nullable()->after('description_html');
            $table->text('custom_message_postpone')->nullable()->after('end_at');
            $table->string('certificate_template')->nullable()->after('materials_list');
            $table->unsignedInteger('auto_archive_days')->default(30)->after('is_active');
            $table->string('start_time_label')->nullable()->after('reg_start_at')->comment('Display label like "Registrations Open"');
        });

        // Add missing fields to `registrations`
        Schema::table('registrations', function (Blueprint $table) {
            $table->string('payment_status')->default('unpaid')->after('status');
            $table->timestamp('receipt_sent_at')->nullable()->after('payment_status');
            $table->boolean('consent_media')->default(false)->after('child_id');
            $table->timestamp('checked_in_at')->nullable()->after('status');
            $table->string('check_in_token')->nullable()->unique()->after('id');
            $table->boolean('attended')->nullable()->after('checked_in_at')->comment('True=Attended, False=Absent, Null=Pending');
        });

        // Add missing fields to `children`
        Schema::table('children', function (Blueprint $table) {
            $table->unsignedInteger('absence_count')->default(0)->after('restrictions_until');
            $table->json('tags')->nullable()->after('absence_count')->comment('Smart tags like allergies');
            $table->unsignedInteger('loyalty_points')->default(0)->after('tags');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropColumn([
                'requires_selection',
                'first_timers_only',
                'materials_list',
                'custom_message_postpone',
                'certificate_template',
                'auto_archive_days',
                'start_time_label',
            ]);
        });
        
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropColumn([
                'payment_status',
                'receipt_sent_at',
                'consent_media',
                'checked_in_at',
                'check_in_token',
                'attended',
            ]);
        });

        Schema::table('children', function (Blueprint $table) {
            $table->dropColumn([
                'absence_count',
                'tags',
                'loyalty_points',
            ]);
        });
    }
};
