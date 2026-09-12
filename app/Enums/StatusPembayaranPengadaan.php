<?php

namespace App\Enums;

/**
 * Status derived (bukan kolom mentah) untuk status pembayaran sebuah Invoice
 * ataupun agregatnya di level Pengadaan. Hanya payment berstatus `approved`
 * yang dihitung sebagai "sudah dibayar". Lihat Invoice::getStatusPembayaranAttribute()
 * dan Pengadaan::getStatusPembayaranAttribute().
 */
enum StatusPembayaranPengadaan: string
{
    case BelumDibayar = 'belum_dibayar';
    case Sebagian = 'sebagian';
    case Lunas = 'lunas';

    public function label(): string
    {
        return match ($this) {
            self::BelumDibayar => 'Belum Dibayar',
            self::Sebagian => 'Dibayar Sebagian',
            self::Lunas => 'Lunas',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::BelumDibayar => 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400',
            self::Sebagian => 'bg-amber-100 text-amber-800 dark:bg-amber-900/20 dark:text-amber-400',
            self::Lunas => 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400',
        };
    }
}
