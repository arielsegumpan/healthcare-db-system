<?php

namespace Database\Seeders;

use App\Models\MedicalStaff;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Student;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
         $studentRole = Role::firstOrCreate(['name' => 'student']);
         $nurseRole = Role::firstOrCreate(['name' => 'medical staff']);

         // Create 10 students with users
         User::factory(10)->create()->each(function ($user) use ($studentRole) {
             $user->assignRole($studentRole);
             Student::factory()->create(['user_id' => $user->id]);
         });

         // Create 5 nurses (without students)
         User::factory(5)->create()->each(function ($user) use ($nurseRole) {
             $user->assignRole($nurseRole);
         });


         $this->call([
            MedicationSeeder::class,
         ]);
    }
}
