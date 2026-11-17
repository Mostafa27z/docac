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
        Schema::table('users', function (Blueprint $table) {
            $table->string('stripe_customer_id')->nullable()->after('remember_token')->index();
        });

        Schema::table('course_payments', function (Blueprint $table) {
            $table->string('payment_method')->nullable()->default('stripe')->after('amount');
            $table->string('transaction_id')->nullable()->after('payment_method')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('course_payments', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'transaction_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('stripe_customer_id');
        });
    }
};
