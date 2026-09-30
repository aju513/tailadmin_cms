<?php

namespace Database\Factories;

use App\Enums\ContentStatus;
use App\Models\Hall;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Hall> */
class HallFactory extends Factory
{
    protected $model = Hall::class;

    public function definition(): array
    {
        $title = fake()->unique()->words(3, true).' Hall';

        return [
            'title' => ['en' => $title], 'slug' => Str::slug($title),
            'location' => 'Nepalgunj, Banke', 'capacity' => 100,
            'rental_rate' => '25000.00', 'rate_unit' => 'day',
            'summary' => ['en' => 'A professional space for training and events.'],
            'status' => ContentStatus::Draft, 'availability_status' => 'available', 'sort_order' => 0,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (): array => ['status' => ContentStatus::Published, 'published_at' => now()]);
    }
}
