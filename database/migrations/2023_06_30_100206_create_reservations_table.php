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
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->boolean('status');
            $table->boolean('done');
            $table->date('day');
            $table->double('start_time');
            $table->double('end_time');
            $table->foreignId('restaurant_id')->constrained('rusturants');
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('tabel_id')->constrained('tabels');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
