<?php

namespace App\Enums;

enum AlatStatusKalibrasi: string
{
    case Terkalibrasi = 'terkalibrasi';
    case Expired = 'expired';
    case Pending = 'pending';
    case TidakPerlu = 'tidak_perlu';

    public function label(): string
    {
        return match ($this) {
            self::Terkalibrasi => 'Ter kalibrasi',
            self::Expired => 'Expired',
            self::Pending => 'Pending',
            self::TidakPerlu => 'Tidak Diperlukan',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Terkalibrasi => 'green',
            self::Expired => 'red',
            self::Pending => 'yellow',
            self::TidakPerlu => 'gray',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Terkalibrasi => 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400',
            self::Expired => 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400',
            self::Pending => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400',
            self::TidakPerlu => 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
