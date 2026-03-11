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
        Schema::table('lapangan', function (Blueprint $table) {
            $table->integer('price_weekday_night')->nullable()->after('price')->comment('Senin-Jumat (15:00 - 24:00)');
            $table->integer('price_weekend_day')->nullable()->after('price_weekday_night')->comment('Sabtu-Minggu (07:00 - 15:00)');
            $table->integer('price_weekend_night')->nullable()->after('price_weekend_day')->comment('Sabtu-Minggu (15:00 - 24:00)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lapangan', function (Blueprint $table) {
            $table->dropColumn(['price_weekday_night', 'price_weekend_day', 'price_weekend_night']);
        });
    }
};
