<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blacklist_phones', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('phone', 20);
            $table->text('reason')->nullable();
            $table->boolean('is_auto')->default(false);
            $table->timestamp('banned_at');
            $table->timestamp('banned_until')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['phone', 'is_active'], 'idx_blacklist_phones_phone_active');
            $table->index('is_active', 'idx_blacklist_phones_is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blacklist_phones');
    }
};
