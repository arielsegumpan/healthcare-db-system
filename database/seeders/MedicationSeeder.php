<?php

namespace Database\Seeders;

use App\Models\Medication;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class MedicationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Medication::factory()->count(10)->create();
    }
}
