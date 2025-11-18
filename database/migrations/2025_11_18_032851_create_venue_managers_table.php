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
        Schema::create('venue_managers', function (Blueprint $table) {
            $table->comment('Bảng N-N gán Manager cho cả Venue');
            $table->bigInteger('venue_id');
            $table->bigInteger('user_id')->index('user_id')->comment('ID của user được gán làm quản lý');

            $table->primary(['venue_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('venue_managers');
    }
};
