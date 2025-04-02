<?php

namespace Database\Factories;

use App\Models\Medication;
use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Factories\Factory;
use Database\Factories\Concerns\CanCreateMedImage;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Medication>
 */
class MedicationFactory extends Factory
{
    use CanCreateMedImage;

    protected $model = Medication::class;

    private static array $usedMedications = [];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $medications = [
            ['name' => 'Paracetamol', 'generic_name' => 'Paracetamol', 'brand_name' => 'Biogesic', 'dosage_form' => 'Tablet'],
            ['name' => 'Ibuprofen', 'generic_name' => 'Ibuprofen', 'brand_name' => 'Advil', 'dosage_form' => 'Capsule'],
            ['name' => 'Amoxicillin', 'generic_name' => 'Amoxicillin', 'brand_name' => 'Himox', 'dosage_form' => 'Capsule'],
            ['name' => 'Cefuroxime', 'generic_name' => 'Cefuroxime', 'brand_name' => 'Zinacef', 'dosage_form' => 'Tablet'],
            ['name' => 'Metformin', 'generic_name' => 'Metformin', 'brand_name' => 'Glucophage', 'dosage_form' => 'Tablet'],
            ['name' => 'Losartan', 'generic_name' => 'Losartan', 'brand_name' => 'Lifezar', 'dosage_form' => 'Tablet'],
            ['name' => 'Amlodipine', 'generic_name' => 'Amlodipine', 'brand_name' => 'Norvasc', 'dosage_form' => 'Tablet'],
            ['name' => 'Salbutamol', 'generic_name' => 'Salbutamol', 'brand_name' => 'Ventolin', 'dosage_form' => 'Inhaler'],
            ['name' => 'Omeprazole', 'generic_name' => 'Omeprazole', 'brand_name' => 'Losec', 'dosage_form' => 'Capsule'],
            ['name' => 'Cetirizine', 'generic_name' => 'Cetirizine', 'brand_name' => 'Virlix', 'dosage_form' => 'Tablet'],
        ];

        // $medicines = [
        //     'paracetamol',
        //     'ibuprofen',
        //     'amoxicillin',
        //     'cefuroxime',
        //     'metformin',
        //     'losartan',
        //     'amlodipine',
        //     'salbutamol',
        //     'omeprazole',
        //     'cetirizine',
        // ];

        // Filter out already used medications
        // $availableMedications = array_values(array_filter($medications, function ($med) {
        //     return !in_array($med['name'], self::$usedMedications);
        // }));

        $availableMedications = array_values(array_filter($medications, function ($med) {
            return !in_array($med['name'], self::$usedMedications);
        }));

        if (empty($availableMedications)) {
            throw new \Exception("All medications have been used! Increase the dataset or reduce the factory count.");
        }

        $medication = $availableMedications[array_rand($availableMedications)];

        // Store used medication to prevent duplicates
        self::$usedMedications[] = $medication['name'];

        return [
            'name' => $medication['name'],
            'generic_name' => $medication['generic_name'],
            'brand_name' => $medication['brand_name'],
            'med_img' => $this->createMedImage(strtolower($medication['name'])),
            'description' => $this->faker->sentence(),
            'dosage_form' => $medication['dosage_form'],
            'stock' => $this->faker->numberBetween(10, 500),
            'expiry_date' => $this->faker->dateTimeBetween('now', '+2 years')->format('Y-m-d'),
            'manufacturer' => $this->faker->company(),
            'notes' => $this->faker->sentence(),
        ];
    }
}
