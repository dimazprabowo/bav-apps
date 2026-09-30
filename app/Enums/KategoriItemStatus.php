<?php

namespace App\Enums;

enum KategoriItemStatus: string
{
    case Aktif = 'aktif';
    case Nonaktif = 'nonaktif';

    public function label(): string
    {
        return match ($this) {
            self::Aktif => 'Aktif',
            self::Nonaktif => 'Nonaktif',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Aktif => 'emerald',
            self::Nonaktif => 'red',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Aktif => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400',
            self::Nonaktif => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
        };
    }

    public static function values(): array
    {
        return array_map(fn ($case) => $case->value, self::cases());
    }
}
