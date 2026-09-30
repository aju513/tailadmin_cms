<?php

namespace Database\Factories;

use App\Enums\ContentStatus;
use App\Models\NoticeCategory;
use App\Models\Notice;
use Illuminate\Database\Eloquent\Factories\Factory;

class NoticeFactory extends Factory
{
    protected $model = Notice::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4), 'slug' => fake()->unique()->slug(),
            'description' => fake()->paragraph(), 'notice_category_id' => NoticeCategory::factory(),
            'deadline_at' => null, 'status' => ContentStatus::Draft, 'sort_order' => 0,
        ];
    }
}
