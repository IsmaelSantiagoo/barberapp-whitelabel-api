<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('business_hours', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('barbershop_id');
            $table->foreign('barbershop_id')
                ->references('id')
                ->on('barbershops')
                ->onDelete('cascade');
            $table->integer('day_of_week');
            $table->time('open_time')->default('09:00');
            $table->time('close_time')->default('18:00');
            $table->boolean('is_open')->default(true);

            // Add unique constraint on barbershop_id and day_of_week
            $table->unique(['barbershop_id', 'day_of_week']);

            $table->timestamps();
        });

        // Add CHECK constraint using raw SQL
        DB::statement('ALTER TABLE business_hours ADD CONSTRAINT business_hours_day_of_week_check CHECK (day_of_week >= 0 AND day_of_week <= 6)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_hours');
    }
};
