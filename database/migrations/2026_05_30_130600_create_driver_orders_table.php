<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driver_orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->char('destination_id', 36);
            $table->char('vehicle_id', 36);
            $table->char('driver_id', 36)->nullable();
            $table->char('accommodation_id', 36)->nullable();
            $table->string('user_name');
            $table->string('user_phone', 20);
            $table->date('departure_date');
            $table->date('return_date');
            $table->boolean('is_overnight')->default(false);
            $table->text('pickup_location');
            $table->string('status', 50)->default('pending');
            $table->boolean('departure_reminded')->default(false);
            $table->boolean('return_reminded')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('destination_id')->references('id')->on('destinations')->cascadeOnDelete();
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->cascadeOnDelete();
            $table->foreign('driver_id')->references('id')->on('drivers')->nullOnDelete();
            $table->foreign('accommodation_id')->references('id')->on('accommodations')->nullOnDelete();

            $table->index('driver_id', 'idx_driver_orders_driver_id');
            $table->index('departure_date', 'idx_driver_orders_departure_date');
            $table->index('return_date', 'idx_driver_orders_return_date');
            $table->index('status', 'idx_driver_orders_status');
            $table->index('user_phone', 'idx_driver_orders_user_phone');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_orders');
    }
};
