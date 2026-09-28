<?php

namespace Database\Factories;

use App\Enums\ContentStatus;
use App\Models\News;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<News> */
class NewsFactory extends Factory
{
    protected $model = News::class;

    public function definition(): array
    {
        $title = fake()->unique()->sentence(5);

        return ['title' => $title, 'slug' => Str::slug($title), 'excerpt' => fake()->paragraph(), 'body' => '<p>'.fake()->paragraph().'</p>', 'status' => ContentStatus::Draft, 'featured' => false, 'created_by' => User::factory(), 'updated_by' => User::factory()];
    }

    public function published(): static
    {
        return $this->state(fn (): array => ['status' => ContentStatus::Published, 'published_at' => now()]);
    }
}
