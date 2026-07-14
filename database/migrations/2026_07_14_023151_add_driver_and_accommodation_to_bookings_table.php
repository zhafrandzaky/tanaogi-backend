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
        Schema::table('bookings', function (Blueprint $table) {
            $table->uuid('driver_id')->nullable()->after('driver_price');
            $table->uuid('accommodation_id')->nullable()->after('hotel_price');

            $table->foreign('driver_id')->references('id')->on('drivers')->nullOnDelete();
            $table->foreign('accommodation_id')->references('id')->on('accommodations')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['driver_id']);
            $table->dropForeign(['accommodation_id']);
            $table->dropColumn(['driver_id', 'accommodation_id']);
        });
    }
};
