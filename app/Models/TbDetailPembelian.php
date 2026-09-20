<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TbDetailPembelian extends Model
{
    protected $table = 'tb_detail_pembelian';

    protected $primaryKey = 'id_detail_pembelian';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'id_pembelian', 'id_barang', 'satuan', 'jumlah', 'harga_beli', 'subtotal',
    ];

    protected function casts(): array
    {
        return [
            'jumlah' => 'integer', 'harga_beli' => 'decimal:2', 'subtotal' => 'decimal:2',
        ];
    }

    public function pembelian(): BelongsTo
    {
        return $this->belongsTo(TbPembelian::class, 'id_pembelian', 'id_pembelian');
    }

    public function barang(): BelongsTo
    {
        return $this->belongsTo(TbBarang::class, 'id_barang', 'id_barang');
    }
}
