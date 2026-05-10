<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'              => fake()->name(),
            'username'          => fake()->unique()->userName() . rand(10, 99),
            'email'             => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password'          => Hash::make('password'),
            'bio'               => fake()->sentence(),
            'status_message'    => fake()->sentence(5),
            'country'           => fake()->country(),
            'is_online'         => fake()->boolean(30),
            'last_seen_at'      => fake()->dateTimeBetween('-7 days', 'now'),
            'subscription_plan' => fake()->randomElement(['free', 'free', 'free', 'premium', 'business']),
            'remember_token'    => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => ['email_verified_at' => null]);
    }
}
