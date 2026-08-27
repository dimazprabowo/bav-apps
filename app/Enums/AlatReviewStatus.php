<?php

namespace App\Enums;

enum AlatReviewStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu Review',
            self::Approved => 'Disetujui',
            self::Rejected => 'Ditolak',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Pending => 'bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-400',
            self::Approved => 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400',
            self::Rejected => 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
