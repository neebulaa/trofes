<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory(10)->create();
        $this->call([
            GuideSeeder::class,
            AllergySeeder::class,
            DietaryPreferenceSeeder::class,
            IngredientSeeder::class,
            RecipeSeeder::class,
            RecipeIngredientSeeder::class,
            RecipeAllergySeeder::class,
            RecipeDietaryPreferenceSeeder::class,
        ]);
    }
}
