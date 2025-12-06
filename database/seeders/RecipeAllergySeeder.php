<?php

namespace Database\Seeders;

use App\Models\RecipeAllergy;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RecipeAllergySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $filePath = database_path('seeders/data/recipe_allergy.csv');
        $csvData = array_map('str_getcsv', file($filePath));

        // Remove header row
        $header = array_shift($csvData);

        foreach ($csvData as $row) {
            $data = array_combine($header, $row);

            RecipeAllergy::create([
                'recipe_id' => $data['recipe_id'],
                'allergy_id' => $data['allergy_id']
            ]);
        }
    }
}
