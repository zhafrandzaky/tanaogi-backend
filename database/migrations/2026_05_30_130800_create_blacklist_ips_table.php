<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blacklist_ips', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('ip_address', 45);
            $table->text('reason')->nullable();
            $table->boolean('is_auto')->default(false);
            $table->timestamp('banned_at');
            $table->timestamp('banned_until')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['ip_address', 'is_active'], 'idx_blacklist_ips_ip_active');
            $table->index('is_active', 'idx_blacklist_ips_is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blacklist_ips');
    }
};
