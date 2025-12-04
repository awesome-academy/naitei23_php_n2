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
        Schema::create('space_amenities', function (Blueprint $table) {
            $table->unsignedBigInteger('space_id');
            $table->unsignedInteger('amenity_id');

            $table->primary(['space_id', 'amenity_id']);

            $table->foreign('space_id')
                ->references('id')
                ->on('spaces')
                ->onDelete('cascade');

            $table->foreign('amenity_id')
                ->references('id')
                ->on('amenities')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('space_amenities');
    }
};
