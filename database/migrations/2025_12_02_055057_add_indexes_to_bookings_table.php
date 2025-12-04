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
        Schema::table('bookings', function (Blueprint $table) {
            // Composite index for filtering by space + time range
            $table->index(['space_id', 'start_time', 'end_time'], 'bookings_space_time_index');
            
            // Index for status filtering
            $table->index('status', 'bookings_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('bookings_space_time_index');
            $table->dropIndex('bookings_status_index');
        });
    }
};
