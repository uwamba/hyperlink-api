<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Expense>
 */
class ExpenseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'description' => $this->faker->sentence(3),
            'amount' => $this->faker->randomFloat(2, 10, 500),
            'expense_date' => $this->faker->date(),
            'category' => $this->faker->randomElement(['Travel', 'Food', 'Office', 'Misc']),
        ];
    }
}
