<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // One row per trading day. We increment 'last_number' atomically
        // inside a DB transaction with a row lock to avoid duplicate
        // order numbers when web + POS orders land at the same instant.
        Schema::create('daily_counters', function (Blueprint $table) {
            $table->id();
            $table->date('trading_date')->unique();
            $table->unsignedInteger('last_number')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_counters');
    }
};
