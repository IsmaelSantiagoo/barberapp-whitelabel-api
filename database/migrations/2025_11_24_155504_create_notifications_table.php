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
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('title')->nullable();
            $table->string('message');
            $table->string('type');
            $table->text('link')->nullable();
            $table->dateTimeTz('sent_at')->useCurrent();
            $table->dateTimeTz('read_at')->nullable();

            $table
                ->unsignedInteger('user_id')
                ->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete()
            ;

            $table
                ->unsignedInteger('menu_id')
                ->foreignId('menu_id')
                ->nullable()
                ->constrained('menus')
                ->cascadeOnUpdate()
                ->cascadeOnDelete()
            ;

            $table->uuid('barbershop_id')->nullable();
            $table->foreign('barbershop_id')
                ->references('id')
                ->on('barbershops')
                ->onDelete('cascade');
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
