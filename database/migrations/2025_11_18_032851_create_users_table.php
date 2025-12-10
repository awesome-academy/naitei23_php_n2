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
        Schema::create('users', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->string('full_name')->nullable();
            $table->string('email')->unique('email');
            $table->string('password_hash');
            $table->string('phone_number', 20)->nullable();
            $table->string('profile_avatar_url')->nullable();
            $table->boolean('is_active')->nullable()->default(true);
            $table->boolean('is_verified')->nullable()->default(false);
            $table->string('verification_token', 100)->nullable();
            $table->string('password_reset_token', 100)->nullable();
            $table->string('avatar')->nullable();
            $table->string('phone')->nullable();
            $table->text('bio')->nullable();
            $table->string('address')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
