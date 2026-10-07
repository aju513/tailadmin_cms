<?php

namespace Database\Factories;

use App\Models\MediaAsset;
use Illuminate\Database\Eloquent\Factories\Factory;

class PopupFactory extends Factory
{
    public function definition(): array
    {
        return ['title' => fake()->sentence(3), 'status' => 'draft', 'sort_order' => 0, 'media_id' => fn () => MediaAsset::query()->create(['disk' => 'public', 'path' => 'cms/popup.png', 'original_name' => 'popup.png', 'mime_type' => 'image/png', 'size' => 100])->id];
    }
}
