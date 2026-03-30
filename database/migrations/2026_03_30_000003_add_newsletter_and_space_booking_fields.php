<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'newsletter_subscribed')) {
                $table->boolean('newsletter_subscribed')->default(false)->after('role');
            }

            if (!Schema::hasColumn('users', 'newsletter_subscribed_at')) {
                $table->timestamp('newsletter_subscribed_at')->nullable()->after('newsletter_subscribed');
            }
        });

        Schema::table('activities', function (Blueprint $table) {
            if (!Schema::hasColumn('activities', 'is_space_booking')) {
                $table->boolean('is_space_booking')->default(false)->after('first_timers_only');
            }
        });
    }

    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            if (Schema::hasColumn('activities', 'is_space_booking')) {
                $table->dropColumn('is_space_booking');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            $dropColumns = [];
            if (Schema::hasColumn('users', 'newsletter_subscribed_at')) {
                $dropColumns[] = 'newsletter_subscribed_at';
            }
            if (Schema::hasColumn('users', 'newsletter_subscribed')) {
                $dropColumns[] = 'newsletter_subscribed';
            }

            if ($dropColumns !== []) {
                $table->dropColumn($dropColumns);
            }
        });
    }
};

