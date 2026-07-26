<?php

namespace Database\Factories;

use App\Models\BusinessUser;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<BusinessUser>
 */
class BusinessUserFactory extends Factory
{
    protected static ?string $password = null;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'is_premium' => false,
            'trial_ends_at' => now()->addMinutes((int) config('business.free_trial_minutes', 10)),
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

    /**
     * Premium account with unlimited public access and enterprises.
     */
    public function premium(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_premium' => true,
            'trial_ends_at' => null,
        ]);
    }

    /**
     * Free account whose trial has already expired.
     */
    public function trialExpired(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_premium' => false,
            'trial_ends_at' => now()->subMinute(),
        ]);
    }
}
