<?php

namespace Database\Factories;

use App\Enums\VendorStatus;
use App\Models\Klaster;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vendor>
 */
class VendorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'klaster_id' => Klaster::factory(),
            'code' => 'VDR-'.fake()->unique()->numberBetween(1, 99999),
            'name' => fake()->company(),
            'contact_person' => fake()->name(),
            'phone' => fake()->numerify('08##########'),
            'email' => fake()->unique()->safeEmail(),
            'address' => fake()->address(),
            'npwp' => fake()->numerify('##.###.###.#-###.###'),
            'status' => VendorStatus::Aktif->value,
        ];
    }
}
