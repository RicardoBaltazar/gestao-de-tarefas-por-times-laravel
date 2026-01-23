<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        return [
            'name' => fake()->sentence(3),
            'status' => fake()->randomElement(['pending', 'in_progress', 'completed', 'cancelled']),
            'project_id' => Project::factory(),
        ];
    }
}