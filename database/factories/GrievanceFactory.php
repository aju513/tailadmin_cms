<?php

namespace Database\Factories;

use App\Models\Grievance;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class GrievanceFactory extends Factory
{
    protected $model = Grievance::class;

    public function definition(): array
    {
        return ['reference' => 'GRV-'.Str::ulid(), 'page_path' => 'grievance', 'page_title' => 'Grievance Form', 'full_name' => fake()->name(), 'email' => fake()->safeEmail(), 'subject' => fake()->sentence(), 'message' => fake()->paragraph()];
    }
}
