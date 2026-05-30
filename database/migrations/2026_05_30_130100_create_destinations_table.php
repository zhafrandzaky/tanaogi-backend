<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('destinations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->char('regency_id', 36);
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');
            $table->integer('ticket_price')->default(0);
            $table->json('facilities')->nullable();
            $table->text('route_text')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('regency_id')->references('id')->on('regencies')->cascadeOnDelete();
            $table->index('regency_id', 'idx_destinations_regency_id');
            $table->index('slug', 'idx_destinations_slug');
            $table->index('is_active', 'idx_destinations_is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('destinations');
    }
};
