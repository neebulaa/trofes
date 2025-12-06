<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RecipeIngredient;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RecipeIngredientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $filePath = database_path('seeders/data/recipe_ingredient.csv');
        $csvData = array_map('str_getcsv', file($filePath));

        // Remove header row
        $header = array_shift($csvData);

        foreach ($csvData as $row) {
            $data = array_combine($header, $row);

            RecipeIngredient::create([
                'recipe_id' => $data['recipe_id'],
                'ingredient_id' => $data['ingredient_id']
            ]);
        }
    }
}
