<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\TaskCategories;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TodoList>
 */
class TodoListFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => fake()->sentence,
            'description' => fake()->paragraph,
            'finished' => fake()->boolean,
            'created_by' => User::factory()->create()->id,
            'task_categories_id' => TaskCategories::factory()->create()->id,
        ];
    }
}
