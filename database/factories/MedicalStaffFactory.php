<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MedicalStaff>
 */
class MedicalStaffFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'staff_type' => $this->faker->randomElement(['nurse', 'doctor']),
            'specialization' => $this->faker->randomElement(['surgeon', 'pediatrician','school nurse', 'general practitioner']),
            'license_number' => $this->faker->randomNumber(9),
            'qualification' => $this->faker->sentence(),
            'from' => $this->faker->date(),
            'to' => $this->faker->date(),
            'experience' => $this->faker->sentence(),
            'availability' => $this->faker->json(),
        ];
    }
}
