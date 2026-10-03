<?php

namespace Database\Factories;

use App\Enums\ContentStatus;
use App\Models\ResourceCategory;
use App\Models\ResourceDocument;
use Illuminate\Database\Eloquent\Factories\Factory;

class ResourceDocumentFactory extends Factory
{
    protected $model = ResourceDocument::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'slug' => fake()->unique()->slug(),
            'description' => fake()->paragraph(),
            'sort_order' => 0,
            'resource_category_id' => ResourceCategory::factory(),
            'status' => ContentStatus::Draft,
        ];
    }
}
