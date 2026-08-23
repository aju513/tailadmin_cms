<?php

namespace Database\Factories;

use App\Models\ContentTag;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<ContentTag> */
class ContentTagFactory extends Factory
{
    protected $model = ContentTag::class;

    public function definition(): array
    {
        $name = fake()->unique()->word();

        return ['name' => $name, 'slug' => Str::slug($name), 'status' => true];
    }
}
