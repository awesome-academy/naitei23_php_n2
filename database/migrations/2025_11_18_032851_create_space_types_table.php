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
        Schema::create('space_types', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('type_name', 100)->unique('type_name')->comment('Tên loại: Private Office, Meeting Room, Desk');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('space_types');
    }
};
