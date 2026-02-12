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

        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new \Exception("CSV file not found or not readable at $filePath");
        }

        $csv = fopen($filePath, 'r');

        // Read header row
        $header = fgetcsv($csv);

        while (($row = fgetcsv($csv)) !== false) {
            $data = array_combine($header, $row);
            $data['cooking_time'] = ($data['cooking_time'] === '' || $data['cooking_time'] === null)
            ? null
            : (float) $data['cooking_time'];

            Recipe::create([
                'recipe_id' => $data['recipe_id'],
                'title' => $data['title'],
                'rating' => $data['rating'],
                'image' => $data['image'],
                'cooking_time' => $data['cooking_time'],
                'instructions' => $data['instructions'],
                'slug' => $data['slug'],
                'measured_ingredients' => $data['measured_ingredients'],
                'calories' => $data['calories'],
                'carbohydrate' => $data['carbohydrate'],
                'protein' => $data['protein'],
                'fat' => $data['fat'],
                'sodium' => $data['sodium']
            ]);
        }

        fclose($csv);
    }
}
