<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('trusted_devices', function (Blueprint $table) {
      $table->id();
      $table->unsignedInteger('user_id');
      $table->string('device_token_hash', 64)->unique();
      $table->uuid('barbershop_id');
      $table->timestamp('expires_at');
      $table->timestamp('last_used_at')->nullable();
      $table->timestamp('created_at')->useCurrent();

      $table->foreign('user_id')
        ->references('id')
        ->on('users')
        ->onDelete('cascade');

      $table->foreign('barbershop_id')
        ->references('id')
        ->on('barbershops')
        ->onDelete('cascade');
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('trusted_devices');
  }
};
