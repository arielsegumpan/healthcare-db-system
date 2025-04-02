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
        Schema::create('medical_staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->enum('staff_type', ['nurse', 'doctor']);
            $table->string('specialization')->nullable();
            $table->string('license_number')->nullable();
            $table->text('qualification')->nullable();
            $table->date('from')->nullable();
            $table->date('to')->nullable();
            $table->string('experience')->nullable();
            $table->json('availability')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_staff');
    }
};
