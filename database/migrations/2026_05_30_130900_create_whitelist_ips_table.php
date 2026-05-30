<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whitelist_ips', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('ip_address', 45)->unique();
            $table->text('note')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('ip_address', 'idx_whitelist_ips_ip_address');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whitelist_ips');
    }
};
