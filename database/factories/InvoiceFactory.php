<?php

namespace Database\Factories;

use App\Models\Pengadaan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'pengadaan_id' => Pengadaan::factory(),
            'no_invoice' => 'INV-'.fake()->unique()->numberBetween(1, 99999),
            'tanggal_invoice' => fake()->date(),
            'jumlah' => fake()->numberBetween(1000000, 20000000),
            'jatuh_tempo' => fake()->dateTimeBetween('now', '+30 days')->format('Y-m-d'),
            'catatan' => fake()->sentence(),
        ];
    }
}
