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
            $table->string('titulo')->unique();
            $table->string('icone')->nullable();
            $table->string('rota')->nullable();
            $table->integer('ordem')->default(0);
            $table->unsignedBigInteger('menu_pai_id')->nullable();
            $table->unsignedInteger('usuario_responsavel')->nullable();
            $table->timestamps();
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
