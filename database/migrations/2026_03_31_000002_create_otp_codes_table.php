<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('otp_codes', function (Blueprint $table) {
      $table->id();
      $table->string('phone', 20);
      $table->string('code', 6);
      $table->uuid('barbershop_id');
      $table->timestamp('expires_at');
      $table->timestamp('verified_at')->nullable();
      $table->timestamp('created_at')->useCurrent();

      $table->foreign('barbershop_id')
        ->references('id')
        ->on('barbershops')
        ->onDelete('cascade');

      $table->index(['phone', 'barbershop_id']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('otp_codes');
  }
};
