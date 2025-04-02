<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('lrn_number')->unique()->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('gender')->nullable();
            $table->string('section', 10)->nullable();
            $table->enum('grade_level', ['Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5', 'Grade 6', 'Grade 7', 'Grade 8', 'Grade 9', 'Grade 10', 'Grade 11', 'Grade 12'])->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('blood_group')->nullable();
            $table->text('address')->nullable();
            $table->string('parent_name')->nullable();
            $table->string('parent_contact', 20)->nullable();
            $table->text('allergies')->nullable();
            // Virtual column for full name based on first_name and last_name
            if (DB::getDriverName() === 'pgsql') {
                $table->string('full_name')->storedAs("first_name || ' ' || last_name")->nullable();
                $table->string('grade_level_section')->storedAs("grade_level || ' - ' || section")->nullable();
            } elseif (DB::getDriverName() === 'sqlite') {
                $table->string('full_name')->virtualAs("first_name || ' ' || last_name")->nullable();
                $table->string('grade_level_section')->storedAs("grade_level || ' - ' || section")->nullable();
            } else {
                $table->string('full_name')->virtualAs("CONCAT(first_name, ' ', last_name)")->nullable();
                $table->string('grade_level_section')->storedAs("CONCAT(grade_level, ' ', section)")->nullable();
            }
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
