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
        Schema::create('table_times', function (Blueprint $table) {
            $table->id();
            $table->foreignId('table_day_id')->constrained('table_days');
            $table->foreignId('reservation_id')->constrained('reservations');
            $table->double('start_time');
            $table->double('end_time');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_times');
    }
};
