<?php

namespace Database\Factories;

use App\Enums\KategoriItemStatus;
use App\Models\KategoriItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class KategoriItemFactory extends Factory
{
    protected $model = KategoriItem::class;

    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->lexify('KTG-?')),
            'name' => fake()->unique()->words(2, true),
            'description' => fake()->sentence(),
            'status' => KategoriItemStatus::Aktif,
        ];
    }
}
