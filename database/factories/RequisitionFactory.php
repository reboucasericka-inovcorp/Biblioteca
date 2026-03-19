<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\Requisition;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Requisition>
 */
class RequisitionFactory extends Factory
{
    protected $model = Requisition::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $requestDate = fake()->dateTimeBetween('-90 days', '-30 days');

        return [
            'sequential_number' => 'RQ-' . now()->year . '-' . fake()->unique()->numerify('####'),
            'user_id' => User::factory(),
            'book_id' => Book::factory(),
            'request_date' => $requestDate,
            'due_date' => Carbon::instance($requestDate)->addDays(14),
            'return_date' => null,
            'status' => Requisition::STATUS_ACTIVE,
            'days_elapsed' => 0,
        ];
    }

    /**
     * Estado: requisição ativa
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Requisition::STATUS_ACTIVE,
            'return_date' => null,
            'days_elapsed' => 0,
        ]);
    }

    /**
     * Estado: requisição devolvida
     */
    public function returned(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Requisition::STATUS_RETURNED,
            'return_date' => now(),
            'days_elapsed' => fake()->numberBetween(1, 14),
        ]);
    }

    /**
     * Estado: requisição em atraso
     */
    public function late(): static
    {
        $requestDate = now()->subDays(30);

        return $this->state(fn (array $attributes) => [
            'status' => Requisition::STATUS_LATE,
            'request_date' => $requestDate,
            'due_date' => $requestDate->addDays(14),
            'return_date' => null,
            'days_elapsed' => now()->diffInDays($requestDate),
        ]);
    }
}
