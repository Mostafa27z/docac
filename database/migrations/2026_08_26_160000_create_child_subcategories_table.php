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
        Schema::create('child_subcategories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subcategory_id')->constrained('subcategories')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->timestamps();
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->foreignId('child_subcategory_id')->nullable()->constrained('child_subcategories')->nullOnDelete()->after('subcategory_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropForeign(['child_subcategory_id']);
            $table->dropColumn('child_subcategory_id');
        });

        Schema::dropIfExists('child_subcategories');
    }
};
