<?php

namespace Database\Factories;

use App\Enums\ContentStatus;
use App\Models\Notice;
use App\Models\NoticeCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class NoticeFactory extends Factory
{
    public function inSection(\App\Models\Page $page): static
    {
        return $this->state(fn () => ['notice_page_id' => $page->id]);
    }

    protected $model = Notice::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4), 'slug' => fake()->unique()->slug(),
            'notice_page_id' => null,
            'description' => fake()->paragraph(), 'notice_category_id' => NoticeCategory::factory(),
            'deadline_at' => null, 'status' => ContentStatus::Draft, 'sort_order' => 0,
        ];
    }
}
