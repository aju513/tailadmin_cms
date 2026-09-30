<?php

namespace Database\Factories;

use App\Enums\ContentStatus;
use App\Models\Video;
use Illuminate\Database\Eloquent\Factories\Factory;

class VideoFactory extends Factory
{
    protected $model = Video::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'slug' => fake()->unique()->slug(),
            'description' => fake()->paragraph(),
            'status' => ContentStatus::Draft,
            'sort_order' => 0,
            'video_url' => 'https://www.youtube.com/watch?v=aqz-KE-bpKQ',
        ];
    }
}
