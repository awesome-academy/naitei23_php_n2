<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->bigInteger('booking_id')->unique('booking_id');
            $table->decimal('amount', 10);
            $table->string('payment_method', 50)->nullable()->comment('VNPAY, MoMo, Stripe, Bank Transfer');
            $table->string('transaction_id')->nullable()->comment('Mã giao dịch từ cổng thanh toán');
            $table->enum('transaction_status', ['pending', 'success', 'failed'])->default('pending');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
