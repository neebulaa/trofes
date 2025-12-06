<?php

namespace Database\Seeders;

use App\Models\RecipeDietaryPreference;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RecipeDietaryPreferenceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $filePath = database_path('seeders/data/recipe_dietary_preference.csv');
        $csvData = array_map('str_getcsv', file($filePath));

        // Remove header row
        $header = array_shift($csvData);

        foreach ($csvData as $row) {
            $data = array_combine($header, $row);

            RecipeDietaryPreference::create([
                'recipe_id' => $data['recipe_id'],
                'dietary_preference_id' => $data['dietary_preference_id']
            ]);
        }
    }
}
