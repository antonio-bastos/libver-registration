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
        Schema::table('registrations', function (Blueprint $table) {
            $table->decimal('fee_amount', 10, 2)->default(0)->after('status')->comment('Amount charged specifically for this registration');
            $table->decimal('amount_paid', 10, 2)->default(0)->after('fee_amount');
            $table->string('currency', 3)->default('USD')->after('amount_paid');
            $table->json('payment_metadata')->nullable()->after('currency')->comment('Stores transaction IDs, gateway responses, etc.');
            $table->timestamp('invoice_generated_at')->nullable()->after('payment_metadata');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropColumn(['fee_amount', 'amount_paid', 'currency', 'payment_metadata', 'invoice_generated_at']);
        });
    }
};
