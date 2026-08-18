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
        // 1. Add price to courses
        Schema::table('courses', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->default(0.00)->after('type');
        });

        // 2. Add pricing/installments status to enrollments
        Schema::table('course_enrollments', function (Blueprint $table) {
            $table->decimal('total_price', 10, 2)->default(0.00)->after('status');
            $table->decimal('paid_amount', 10, 2)->default(0.00)->after('total_price');
            $table->enum('payment_status', ['unpaid', 'partially_paid', 'fully_paid'])->default('unpaid')->after('paid_amount');
        });

        // 3. Create course payments/installments history table
        Schema::create('course_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_enrollment_id')->constrained('course_enrollments')->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->string('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_payments');

        Schema::table('course_enrollments', function (Blueprint $table) {
            $table->dropColumn(['total_price', 'paid_amount', 'payment_status']);
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn('price');
        });
    }
};
