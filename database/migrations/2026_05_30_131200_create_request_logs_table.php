<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('request_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('ip_address', 45);
            $table->string('phone', 20)->nullable();
            $table->string('endpoint', 255);
            $table->timestamp('created_at')->nullable();

            $table->index(['ip_address', 'created_at'], 'idx_request_logs_ip_created');
            $table->index(['phone', 'created_at'], 'idx_request_logs_phone_created');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_logs');
    }
};
