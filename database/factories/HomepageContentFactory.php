<?php

namespace Database\Factories;

use App\Models\HomepageContent;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<HomepageContent> */
class HomepageContentFactory extends Factory
{
    protected $model = HomepageContent::class;

    public function definition(): array
    {
        return [
            'key' => HomepageContent::KEY,
            'title' => ['en' => fake()->sentence(4)],
            'subtitle' => ['en' => 'About us'],
            'body' => ['en' => '<p>'.fake()->paragraph().'</p>'],
        ];
    }
}
