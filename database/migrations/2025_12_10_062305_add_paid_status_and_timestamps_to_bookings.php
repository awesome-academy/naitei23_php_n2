<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Add confirmed_at and paid_at timestamps
            if (!Schema::hasColumn('bookings', 'confirmed_at')) {
                $table->timestamp('confirmed_at')->nullable()->after('status');
            }
            if (!Schema::hasColumn('bookings', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('confirmed_at');
            }
        });

        // Update enum to include 'paid' status
        DB::statement("ALTER TABLE bookings MODIFY COLUMN status ENUM('pending_confirmation', 'awaiting_payment', 'confirmed', 'paid', 'cancelled', 'completed') DEFAULT 'pending_confirmation'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['confirmed_at', 'paid_at']);
        });

        // Revert enum
        DB::statement("ALTER TABLE bookings MODIFY COLUMN status ENUM('pending_confirmation', 'awaiting_payment', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending_confirmation'");
    }
};
