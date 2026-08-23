<?php

namespace Database\Factories;

use App\Models\ContentAuthor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<ContentAuthor> */
class ContentAuthorFactory extends Factory
{
    protected $model = ContentAuthor::class;

    public function definition(): array
    {
        $name = fake()->name();

        return ['name' => $name, 'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 99999), 'bio' => fake()->optional()->paragraph(), 'email' => fake()->safeEmail(), 'status' => true];
    }
}
