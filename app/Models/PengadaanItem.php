<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengadaanItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'pengadaan_id',
        'nama_item',
        'kategori_item',
        'qty',
        'satuan',
        'harga_satuan',
        'subtotal',
    ];

    protected $casts = [
        'qty' => 'integer',
        'harga_satuan' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function pengadaan(): BelongsTo
    {
        return $this->belongsTo(Pengadaan::class);
    }
}
