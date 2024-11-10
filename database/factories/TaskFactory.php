<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => 2, 
            'parent_task_id' => 21,
            'title' => $this->faker->unique()->sentence(3), // Generates a unique title of 3 words
            'content' => $this->faker->paragraph, // Random content paragraph
            'status' => $this->faker->randomElement(['to-do', 'in-progress', 'done']), // Random status
            'image_path' => null, // Leave as null for simplicity, or you could add random images if needed
            'is_draft' => $this->faker->boolean, // Randomly true or false
            'created_at' => now(),
            'updated_at' => now(),

        ];
    }

}
