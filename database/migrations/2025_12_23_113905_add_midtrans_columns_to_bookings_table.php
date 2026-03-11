<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Menyimpan harga deal saat booking terjadi (PENTING)
            $table->decimal('total_price', 10, 2)->after('user_id');

            // Token dari Midtrans
            $table->string('snap_token')->nullable()->after('status');

            // Opsional: Info tambahan
            $table->string('payment_type')->nullable()->after('snap_token');
        });
    }



    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['total_price', 'snap_token', 'payment_type']);
        });
    }
};
