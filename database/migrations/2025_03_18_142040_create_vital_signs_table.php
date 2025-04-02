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
        Schema::create('vital_signs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('record_id')->constrained('medical_records')->cascadeOnDelete();
            $table->float('temperature')->nullable();
            $table->string('blood_pressure')->nullable();
            $table->integer('pulse_rate')->nullable();
            $table->integer('respiratory_rate')->nullable();
            $table->float('height')->nullable();
            $table->float('weight')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vital_signs');
    }
};
