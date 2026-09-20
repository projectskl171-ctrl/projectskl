<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TbDetailPenjualan extends Model
{
    protected $table = 'tb_detail_penjualan';

    protected $primaryKey = 'id_detail_penjualan';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'id_penjualan', 'id_barang', 'jumlah_barang', 'harga_beli', 'harga_jual',
        'diskon_tipe', 'diskon_nilai', 'diskon_nominal', 'subtotal',
    ];

    protected function casts(): array
    {
        return [
            'jumlah_barang' => 'integer',
            'harga_beli' => 'decimal:2', 'harga_jual' => 'decimal:2',
            'diskon_nilai' => 'decimal:2', 'diskon_nominal' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    public function penjualan(): BelongsTo
    {
        return $this->belongsTo(TbPenjualan::class, 'id_penjualan', 'id_penjualan');
    }

    public function barang(): BelongsTo
    {
        return $this->belongsTo(TbBarang::class, 'id_barang', 'id_barang');
    }
}
