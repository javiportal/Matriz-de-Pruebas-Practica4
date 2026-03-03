<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class BookFactory extends Factory
{
    public function definition(): array
    {
        $totalCopies = $this->faker->numberBetween(5, 10);
        $availableCopies = $this->faker->numberBetween(1, $totalCopies);

        return [
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->text(200),
            'ISBN' => $this->faker->unique()->numerify('#############'),
            'total_copies' => $totalCopies,
            'available_copies' => $availableCopies,
            'is_available' => $availableCopies > 0,
        ];
    }

    
    public function unavailable(): static
    {
        return $this->state(fn (array $attributes) => [
            'available_copies' => 0,
            'is_available' => false,
        ]);
    }
}