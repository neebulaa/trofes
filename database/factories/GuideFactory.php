<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Guide>
 */
class GuideFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->sentence();

        return [
            'slug' => Str::slug($title) . '-' . Str::random(5),
            'published_at' => $this->faker->dateTime(),
            'content' => $this->faker->paragraphs(5, true),
            'title' => $title,
            'image' => null,
            'admin_id' => User::factory(),
        ];
    }
}
