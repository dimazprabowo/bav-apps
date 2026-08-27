<?php

namespace App\Enums;

enum KalibrasiHasil: string
{
    case Lulus = 'lulus';
    case Gagal = 'gagal';
    case PerluPerbaikan = 'perlu_perbaikan';

    public function label(): string
    {
        return match ($this) {
            self::Lulus => 'Lulus',
            self::Gagal => 'Gagal',
            self::PerluPerbaikan => 'Perlu Perbaikan',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Lulus => 'green',
            self::Gagal => 'red',
            self::PerluPerbaikan => 'yellow',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Lulus => 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400',
            self::Gagal => 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400',
            self::PerluPerbaikan => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
