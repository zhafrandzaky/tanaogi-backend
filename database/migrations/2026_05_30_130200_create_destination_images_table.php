<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('destination_images', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->char('destination_id', 36);
            $table->string('path', 500);
            $table->string('url', 500);
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->foreign('destination_id')->references('id')->on('destinations')->cascadeOnDelete();
            $table->index('destination_id', 'idx_destination_images_destination_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('destination_images');
    }
};
