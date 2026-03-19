<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('anonymized_at')->nullable()->after('remember_token');
        });

        Schema::table('children', function (Blueprint $table) {
            $table->timestamp('anonymized_at')->nullable()->after('loyalty_points');
        });

        Schema::table('registrations', function (Blueprint $table) {
            $table->timestamp('anonymized_at')->nullable()->after('attended');
        });

        Schema::table('activities', function (Blueprint $table) {
            $table->timestamp('archived_at')->nullable()->after('is_archived');
        });

        Schema::create('gdpr_snapshots', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type', 50);
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->json('payload');
            $table->timestamp('captured_at');
            $table->timestamps();

            $table->index(['entity_type', 'entity_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gdpr_snapshots');

        Schema::table('activities', function (Blueprint $table) {
            $table->dropColumn('archived_at');
        });

        Schema::table('registrations', function (Blueprint $table) {
            $table->dropColumn('anonymized_at');
        });

        Schema::table('children', function (Blueprint $table) {
            $table->dropColumn('anonymized_at');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('anonymized_at');
        });
    }
};
