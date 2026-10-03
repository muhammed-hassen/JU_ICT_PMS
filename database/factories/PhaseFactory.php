<?php

namespace Database\Factories;

use App\Models\Phase;
use App\Models\PhaseStatus;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PhaseFactory extends Factory
{
    protected $model = Phase::class;

    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'phase_status_id' => PhaseStatus::inRandomOrder()->first()?->id ?? 1,
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph(),
            'sort_order' => $this->faker->numberBetween(1, 10),
            'progress_percentage' => $this->faker->numberBetween(0, 100),
            'created_by' => User::factory(),
            'updated_by' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
