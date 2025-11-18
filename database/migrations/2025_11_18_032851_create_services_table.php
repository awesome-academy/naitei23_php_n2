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
        Schema::create('services', function (Blueprint $table) {
            $table->comment('Các dịch vụ/options cộng thêm có tính phí (máy chiếu, trà...)');
            $table->integer('id', true);
            $table->bigInteger('venue_id')->index('venue_id')->comment('Dịch vụ này thuộc venue nào');
            $table->string('name');
            $table->decimal('price', 10)->default(0);
            $table->text('description')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
