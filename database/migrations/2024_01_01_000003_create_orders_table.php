<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id(); // internal id, never shown to customer
            $table->unsignedInteger('order_number'); // shown to customer, resets daily
            $table->date('trading_date');
            $table->enum('source', ['web', 'pos'])->default('web');
            $table->enum('status', ['awaiting_payment', 'queued', 'preparing', 'ready', 'collected', 'cancelled'])
                ->default('awaiting_payment');
            $table->string('customer_name')->nullable(); // optional, for calling out orders
            $table->unsignedInteger('total_pence'); // store money as integer pence, never floats
            $table->foreignId('staff_id')->nullable()->constrained('staff')->nullOnDelete(); // set if POS order
            $table->string('sumup_checkout_id')->nullable();
            $table->string('payment_status')->default('pending'); // pending|paid|failed
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('preparing_at')->nullable();
            $table->timestamp('ready_at')->nullable();
            $table->timestamps();

            $table->unique(['trading_date', 'order_number']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
