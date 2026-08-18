<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Insert initial contact defaults
        DB::table('settings')->insert([
            [
                'key' => 'facebook_url',
                'value' => 'https://www.facebook.com/share/1YMP1n8ySD/',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'youtube_url',
                'value' => 'https://youtube.com/@docacademy23?si=TIrT50jyVpi5DYn3',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'whatsapp_number',
                'value' => '01090214254',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'telegram_number',
                'value' => '01090214254',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'telegram_username',
                'value' => 'DocAcademyy',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
