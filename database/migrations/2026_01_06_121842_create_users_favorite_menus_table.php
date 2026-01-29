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
        Schema::create('users_favorite_menus', function (Blueprint $table) {
            $table
                ->unsignedInteger('user_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete()
            ;

            $table
                ->unsignedInteger('menu_id')
                ->constrained('menus')
                ->cascadeOnUpdate()
                ->cascadeOnDelete()
            ;

            $table->unique(['user_id', 'menu_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users_favorite_menus');
    }
};
