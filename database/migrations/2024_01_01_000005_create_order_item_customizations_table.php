<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_item_customizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->constrained('order_items')->cascadeOnDelete();
            $table->string('ingredient'); // e.g. "cheese", "onions", "bacon"
            $table->enum('action', ['added', 'removed'])->default('added');
            $table->unsignedInteger('extra_price_pence')->default(0); // for paid extras like bacon
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_item_customizations');
    }
};
