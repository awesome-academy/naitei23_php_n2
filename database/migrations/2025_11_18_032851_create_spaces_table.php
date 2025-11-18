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
        Schema::create('spaces', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->bigInteger('venue_id')->index('venue_id');
            $table->integer('space_type_id')->index('space_type_id');
            $table->string('name');
            $table->integer('capacity')->default(1);
            $table->decimal('price_per_hour', 10)->nullable();
            $table->decimal('price_per_day', 10)->nullable();
            $table->decimal('price_per_month', 10)->nullable();
            $table->time('open_hour')->nullable()->default('08:00:00');
            $table->time('close_hour')->nullable()->default('18:00:00');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spaces');
    }
};
