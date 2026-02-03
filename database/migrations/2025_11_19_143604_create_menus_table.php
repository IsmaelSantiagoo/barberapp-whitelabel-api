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
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('title')->unique();
            $table->string('icon')->nullable();
            $table->string('route')->nullable();
            $table->integer('order')->default(0);
            $table->unsignedBigInteger('parent_menu_id')->nullable();
            $table->unsignedInteger('responsible_user')->nullable();
            $table->timestamps();
            $table->uuid('tenant_id')->nullable();
            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
