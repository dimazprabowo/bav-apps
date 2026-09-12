<?php

namespace App\Enums;

enum FileStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Completed = 'completed';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu Proses',
            self::Processing => 'Sedang Diproses',
            self::Completed => 'Selesai',
            self::Failed => 'Gagal',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Pending => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
            self::Processing => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
            self::Completed => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
            self::Failed => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
