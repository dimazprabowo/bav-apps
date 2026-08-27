<?php

namespace App\Enums;

enum LogBookStatus: string
{
    case Requested = 'requested';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Borrowed = 'borrowed';
    case Returned = 'returned';
    case Overdue = 'overdue';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Requested => 'Diminta',
            self::Approved => 'Disetujui',
            self::Rejected => 'Ditolak',
            self::Borrowed => 'Dipinjam',
            self::Returned => 'Dikembalikan',
            self::Overdue => 'Terlambat',
            self::Cancelled => 'Dibatalkan',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Requested => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400',
            self::Approved => 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400',
            self::Rejected => 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400',
            self::Borrowed => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/20 dark:text-indigo-400',
            self::Returned => 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400',
            self::Overdue => 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400',
            self::Cancelled => 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
