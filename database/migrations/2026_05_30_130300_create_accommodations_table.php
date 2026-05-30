<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accommodations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->char('destination_id', 36);
            $table->string('name');
            $table->string('type', 50);
            $table->integer('price_per_night');
            $table->text('address');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('destination_id')->references('id')->on('destinations')->cascadeOnDelete();
            $table->index('destination_id', 'idx_accommodations_destination_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accommodations');
    }
};
