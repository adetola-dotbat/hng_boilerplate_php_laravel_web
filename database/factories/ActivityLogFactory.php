<?php

namespace Database\Factories;

use App\Models\ActivityLog;
use App\Models\User;
use App\Models\Organisation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ActivityLog>
 */
class ActivityLogFactory extends Factory
{
    protected $model = ActivityLog::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid,
            'action' => $this->faker->word,
            'user_id' => User::factory(),
            'organisation_id' => Organisation::factory(),
        ];
    }
}
