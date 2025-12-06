<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    public function definition(): array
    {
        return [
            'full_name' => $this->faker->name(),
            'bio' => $this->faker->sentence(),
            'username' => $this->faker->unique()->userName(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => Hash::make('password'), // default password
            'phone' => $this->faker->phoneNumber(),
            'gender' => $this->faker->randomElement(['male', 'female', 'other']),
            'birth_date' => $this->faker->date('Y-m-d', '2010-01-01'),
            'profile_image' => $this->faker->imageUrl(300, 300, 'people', true),
            'is_admin' => rand(0,1), // default
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
