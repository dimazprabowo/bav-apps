<?php

namespace App\Enums;

enum AlatStatusKepemilikan: string
{
    case MilikSendiri = 'milik_sendiri';
    case Sewa = 'sewa';
    case Pinjam = 'pinjam';
    case Leasing = 'leasing';

    public function label(): string
    {
        return match ($this) {
            self::MilikSendiri => 'Milik Sendiri',
            self::Sewa => 'Sewa',
            self::Pinjam => 'Pinjam',
            self::Leasing => 'Leasing',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::MilikSendiri => 'blue',
            self::Sewa => 'purple',
            self::Pinjam => 'orange',
            self::Leasing => 'teal',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::MilikSendiri => 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400',
            self::Sewa => 'bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-400',
            self::Pinjam => 'bg-orange-100 text-orange-800 dark:bg-orange-900/20 dark:text-orange-400',
            self::Leasing => 'bg-teal-100 text-teal-800 dark:bg-teal-900/20 dark:text-teal-400',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
