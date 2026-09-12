<?php

namespace App\Enums;

enum VendorStatus: string
{
    case Aktif = 'aktif';
    case Nonaktif = 'nonaktif';

    public function label(): string
    {
        return match ($this) {
            self::Aktif => 'Aktif',
            self::Nonaktif => 'Tidak Aktif',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Aktif => 'green',
            self::Nonaktif => 'gray',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Aktif => 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400',
            self::Nonaktif => 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
