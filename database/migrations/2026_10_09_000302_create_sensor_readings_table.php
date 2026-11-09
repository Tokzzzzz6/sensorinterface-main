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
    Schema::create('sensor_readings', function (Blueprint $table) {
        $table->id();

        $table->decimal('distance_cm', 8, 2)->nullable();
        $table->decimal('temperature_c', 6, 2)->nullable();
        $table->decimal('humidity_percent', 6, 2)->nullable();

        $table->unsignedSmallInteger('mq135_raw')->nullable();
        $table->decimal('mq135_signal_percent', 5, 2)->nullable();

        $table->timestamp('recorded_at')->index();
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('sensor_readings');
}
};
