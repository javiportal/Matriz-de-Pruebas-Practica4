<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LoanFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'requester_name' => $this->faker->name(),
            'book_id' => Book::factory(),
            'return_at' => null,
        ];
    }


    public function returned(): static
    {
        return $this->state(fn (array $attributes) => [
            'return_at' => now(),
        ]);
    }
}