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
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('transaction_status');
            }
            if (!Schema::hasColumn('payments', 'meta')) {
                $table->json('meta')->nullable()->after('paid_at');
            }
            // Add indexes for performance
            $table->index('transaction_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['payments_transaction_status_index']);
            $table->dropColumn(['paid_at', 'meta']);
        });
    }
};
