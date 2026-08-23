<?php

namespace Database\Factories;

use App\Models\ContentCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<ContentCategory> */
class ContentCategoryFactory extends Factory
{
    protected $model = ContentCategory::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return ['name' => $name, 'slug' => Str::slug($name), 'status' => true, 'sort_order' => 0];
    }
}
