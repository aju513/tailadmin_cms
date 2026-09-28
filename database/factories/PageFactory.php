<?php

namespace Database\Factories;

use App\Enums\ContentStatus;
use App\Enums\PageType;
use App\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Page> */
class PageFactory extends Factory
{
    protected $model = Page::class;

    public function definition(): array
    {
        $title = fake()->unique()->sentence(3);
        $slug = Str::slug($title);

        return ['title' => ['en' => $title], 'page_type' => PageType::Article, 'slug' => $slug, 'path' => $slug, 'body' => ['en' => '<p>'.fake()->paragraph().'</p>'], 'status' => ContentStatus::Draft, 'sort_order' => 0];
    }
}
