<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\Publisher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Book>
 */
class BookFactory extends Factory
{
    protected $model = Book::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $publisher = Publisher::factory();

        return [
            'name' => fake()->sentence(3),
            'isbn' => 'ISBN-' . fake()->unique()->numerify('##############'),
            'isbn_13' => 'ISBN13-' . fake()->unique()->numerify('###############'),
            'price' => fake()->randomFloat(2, 10, 100),
            'discount' => fake()->randomFloat(2, 0, 30),
            'stock' => fake()->numberBetween(0, 50),
            'reserved_stock' => 0,
            'is_active' => true,
            'publisher_id' => $publisher,
            'pages' => fake()->numberBetween(100, 800),
            'language' => 'Português',
            'published_date' => fake()->dateTimeBetween('-10 years'),
        ];
    }

    /**
     * Indicar que o livro está indisponível
     */
    public function unavailable(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock' => 0,
            'reserved_stock' => 0,
        ]);
    }

    /**
     * Indicar que o livro está inativo
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
