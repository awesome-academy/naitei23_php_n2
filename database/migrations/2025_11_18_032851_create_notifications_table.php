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
        Schema::create('notifications', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->bigInteger('user_id')->index('user_id');
            $table->text('message');
            $table->enum('type', ['email', 'sms', 'app'])->default('app');
            $table->boolean('is_read')->nullable()->default(false);
            $table->string('related_url')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
