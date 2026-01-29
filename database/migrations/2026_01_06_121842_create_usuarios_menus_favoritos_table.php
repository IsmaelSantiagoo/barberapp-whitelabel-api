<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('usuarios_menus_favoritos', function (Blueprint $table) {
            $table
                ->unsignedInteger('usuario_id')
                ->constrained('usuarios')
                ->cascadeOnUpdate()
                ->cascadeOnDelete()
            ;

            $table
                ->unsignedInteger('menu_id')
                ->constrained('menus')
                ->cascadeOnUpdate()
                ->cascadeOnDelete()
            ;

            $table->unique(['usuario_id', 'menu_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuarios_menus_favoritos');
    }
};
