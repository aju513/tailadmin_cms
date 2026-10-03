<?php

namespace Database\Factories;

use App\Models\ResourceCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class ResourceCategoryFactory extends Factory
{
    protected $model = ResourceCategory::class;

    public function definition(): array
    {
        return [
            'name' => fake()->sentence(3),
            'slug' => fake()->unique()->slug(),
            'description' => fake()->paragraph(),
            'sort_order' => 0, 'is_active' => true,

        ];
    }
}
