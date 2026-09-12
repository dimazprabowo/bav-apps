<?php

namespace App\Enums;

/**
 * Status derived (bukan kolom mentah) yang membandingkan total invoice
 * terhadap total_biaya sebuah Pengadaan. Lihat Pengadaan::getStatusInvoiceAttribute().
 */
enum StatusInvoicePengadaan: string
{
    case BelumDitagih = 'belum_ditagih';
    case DitagihSebagian = 'ditagih_sebagian';
    case SudahDitagihPenuh = 'sudah_ditagih_penuh';

    public function label(): string
    {
        return match ($this) {
            self::BelumDitagih => 'Belum Ditagih',
            self::DitagihSebagian => 'Ditagih Sebagian',
            self::SudahDitagihPenuh => 'Sudah Ditagih Penuh',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::BelumDitagih => 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400',
            self::DitagihSebagian => 'bg-amber-100 text-amber-800 dark:bg-amber-900/20 dark:text-amber-400',
            self::SudahDitagihPenuh => 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400',
        };
    }
}
