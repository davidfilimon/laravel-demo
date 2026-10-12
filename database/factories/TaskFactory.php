<?php

namespace Database\Factories;

use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Task> */
class TaskFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'priority' => fake()->randomElement(Task::PRIORITIES),
            'is_completed' => false,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => ['is_completed' => true]);
    }
}
