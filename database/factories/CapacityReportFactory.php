<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CapacityReportFactory extends Factory
{
    public function definition(): array
    {
        $rows = array_map(fn ($label) => ['key' => $label, 'value' => fake()->numberBetween(0, 1000)], array_values(config('settings.capacity_reports.metrics')));

        return ['fiscal_year' => fake()->unique()->randomElement(config('settings.fiscal_years')), 'development' => $rows, 'collaboration' => $rows];
    }
}
