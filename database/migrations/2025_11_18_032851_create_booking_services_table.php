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
        Schema::create('booking_services', function (Blueprint $table) {
            $table->comment('Các dịch vụ/options mà khách hàng đã chọn khi đặt chỗ');
            $table->bigInteger('booking_id');
            $table->integer('service_id')->index('service_id');
            $table->integer('quantity')->default(1);
            $table->decimal('price_at_booking', 10);

            $table->primary(['booking_id', 'service_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_services');
    }
};
