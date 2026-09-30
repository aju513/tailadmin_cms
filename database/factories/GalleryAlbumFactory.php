<?php

namespace Database\Factories;

use App\Enums\ContentStatus;
use App\Models\GalleryAlbum;
use Illuminate\Database\Eloquent\Factories\Factory;

class GalleryAlbumFactory extends Factory
{
    protected $model = GalleryAlbum::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'slug' => fake()->unique()->slug(),
            'description' => fake()->paragraph(),
            'status' => ContentStatus::Draft,
            'sort_order' => 0,
            'event_date' => fake()->date(),
        ];
    }
}
