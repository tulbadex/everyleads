<?php

namespace Database\Factories;

use App\Models\{Lead, User};
use Illuminate\Database\Eloquent\Factories\Factory;

class LeadFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Lead::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph,
            'value' => $this->faker->numberBetween(1_000, 2_00000),
            'source' => $this->faker->words(2, true),
            'contact_person' => $this->faker->name(),
            'contact_email' => $this->faker->safeEmail(),
            'contact_phone' => $this->faker->phoneNumber(),
            'contact_organization' => $this->faker->company(),
            'start_date' => now()->addDay(1)->format('Y-m-d'),
            'end_date' => now()->addDay(5)->format('Y-m-d'),
            'status' => Lead::STATUS_FOLLOW_UP,
            'creator' => User::factory(),
            'assign_to' => User::factory(),
        ];
    }
}
