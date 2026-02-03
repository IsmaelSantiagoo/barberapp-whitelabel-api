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
        Schema::create('barbershops', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('company_name'); // Nome da Barbearia
            $table->string('slug')->unique(); // ex: barbearia-do-ze (usado na URL ou subdomínio)
            $table->string('domain')->nullable()->unique(); // Para domínio próprio
            $table->string('primary_color')->default('#000000'); // Customização Whitelabel
            $table->string('logo_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barbershops');
    }
};
