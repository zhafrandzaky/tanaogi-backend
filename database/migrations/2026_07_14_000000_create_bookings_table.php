<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->nullable();
            $table->uuid('destination_id')->nullable();
            $table->string('destination_slug');
            $table->date('visit_date');
            $table->integer('pax_count');
            $table->boolean('has_driver');
            $table->string('driver_package')->nullable();
            $table->decimal('driver_price', 12, 2)->default(0);
            $table->boolean('include_hotel');
            $table->string('selected_hotel')->nullable();
            $table->decimal('hotel_price', 12, 2)->default(0);
            $table->decimal('total_amount_web', 12, 2);
            $table->decimal('entrance_ticket_fee_onsite', 12, 2);
            $table->string('payment_status')->default('pending'); // pending, paid, expired, failed, cancelled
            $table->string('midtrans_transaction_id')->nullable();
            $table->string('midtrans_snap_token')->nullable();
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('destination_id')->references('id')->on('destinations')->nullOnDelete();

            $table->index('user_id', 'idx_bookings_user_id');
            $table->index('destination_id', 'idx_bookings_destination_id');
            $table->index('payment_status', 'idx_bookings_payment_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
