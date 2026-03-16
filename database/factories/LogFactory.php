<?php

namespace Database\Factories;

use App\Models\Log;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Log>
 */
class LogFactory extends Factory
{
    protected $model = Log::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'log_date' => fake()->dateTimeBetween('-30 days')->format('Y-m-d'),
            'log_time' => fake()->time('H:i:s'),
            'user_id' => User::factory(),
            'module' => fake()->randomElement(['Book', 'Author', 'Publisher', 'Requisition', 'Review']),
            'object_id' => fake()->numberBetween(1, 1000),
            'change' => fake()->sentence(),
            'ip' => fake()->ipv4(),
            'browser' => fake()->randomElement(['Chrome', 'Firefox', 'Safari', 'Edge']),
        ];
    }
}
