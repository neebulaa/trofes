<?php

namespace Database\Seeders;

use App\Models\Recipe;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RecipeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $filePath = database_path('seeders/data/recipe.csv');
        $csvData = array_map('str_getcsv', file($filePath));

        // Remove header row
        $header = array_shift($csvData);

        foreach ($csvData as $row) {
            $data = array_combine($header, $row);

            Recipe::create([
                'recipe_id' => $data['recipe_id'],
                'title' => $data['title'],
                'instructions' => $data['instructions'],
                'slug' => $data['slug'],
                'measured_ingredients' => $data['measured_ingredients'],
                'calories' => $data['calories'],
                'protein' => $data['protein'],
                'fat' => $data['fat'],
                'sodium' => $data['sodium'],
            ]);
        }
    }
}
