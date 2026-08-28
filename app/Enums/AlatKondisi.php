<?php

namespace App\Enums;

enum AlatKondisi: string
{
    case Baik = 'baik';
    case RusakRingan = 'rusak_ringan';
    case RusakBerat = 'rusak_berat';
    case Hilang = 'hilang';

    public function label(): string
    {
        return match ($this) {
            self::Baik => 'Baik',
            self::RusakRingan => 'Rusak Ringan',
            self::RusakBerat => 'Rusak Berat',
            self::Hilang => 'Hilang',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Baik => 'green',
            self::RusakRingan => 'yellow',
            self::RusakBerat => 'red',
            self::Hilang => 'gray',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Baik => 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400',
            self::RusakRingan => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400',
            self::RusakBerat => 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400',
            self::Hilang => 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
