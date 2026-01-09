<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\LikeRecipe;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory(10)->create();
        User::create([
            "full_name" => "Admin 7DS",
            "password" => "trofesadmin",
            "username" => "trofesadmin",
            "email" => "trofesadmin@gmail.com",
            "phone" => "+6289694636303",
            "bio" => "There is no mercy for light",
            "birth_date" => "2006-03-18",
            "is_admin" => true
        ]);

        User::create([
            "full_name" => "Guest 7DS",
            "password" => "trofesguest",
            "username" => "trofesguest",
            "email" => "trofesguest@gmail.com",
            "phone" => "+6289694636302",
            "bio" => "There is no mercy for light",
            "birth_date" => "2006-03-18",
            "is_admin" => false
        ]);

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

        LikeRecipe::create([
            'user_id' => 11,
            'recipe_id' => 1,
        ]);

        LikeRecipe::create([
            'user_id' => 11,
            'recipe_id' => 2,
        ]);

        LikeRecipe::create([
            'user_id' => 12,
            'recipe_id' => 1,
        ]);

        LikeRecipe::create([
            'user_id' => 12,
            'recipe_id' => 4,
        ]);
    }
}
