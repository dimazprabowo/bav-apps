<?php

namespace Database\Factories;

use App\Models\Pengadaan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PengadaanItem>
 */
class PengadaanItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $qty = fake()->numberBetween(1, 5);
        $harga = fake()->numberBetween(100000, 5000000);

        return [
            'pengadaan_id' => Pengadaan::factory(),
            'nama_aset' => fake()->words(3, true),
            'kategori_aset' => fake()->word(),
            'qty' => $qty,
            'satuan' => 'unit',
            'harga_satuan' => $harga,
            'subtotal' => $qty * $harga,
        ];
    }
}
