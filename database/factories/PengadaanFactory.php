<?php

namespace Database\Factories;

use App\Enums\PengadaanApprovalStatus;
use App\Enums\TipeBiaya;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pengadaan>
 */
class PengadaanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'no_pengadaan' => 'PG-'.fake()->unique()->numberBetween(1, 99999),
            'nama_pemohon' => fake()->name(),
            'tipe_biaya' => TipeBiaya::FixCost->value,
            'no_wbs' => null,
            'vendor_id' => Vendor::factory(),
            'cabang_id' => null,
            'tanggal_pengadaan' => fake()->date(),
            'total_biaya' => 0,
            'status_approval' => PengadaanApprovalStatus::Pending->value,
            'catatan' => fake()->sentence(),
        ];
    }

    public function approved(): static
    {
        return $this->state(fn () => [
            'status_approval' => PengadaanApprovalStatus::Approved->value,
            'approved_at' => now(),
        ]);
    }
}
