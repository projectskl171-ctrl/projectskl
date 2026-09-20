<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TbBarang extends Model
{
    protected $table = 'tb_barang';

    protected $primaryKey = 'id_barang';

    public $incrementing = true;

    protected $keyType = 'int';

    const CREATED_AT = 'created_at';

    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'id_sekolah', 'barcode', 'nama', 'id_kategori', 'id_kelompok_kategori',
        'id_supplier', 'satuan', 'harga_beli', 'harga_jual', 'stok', 'is_active',
        'created_at', 'created_by', 'updated_at', 'updated_by',
        'deleted_at', 'deleted_by', 'is_delete',
    ];

    protected function casts(): array
    {
        return [
            'harga_beli' => 'decimal:2', 'harga_jual' => 'decimal:2',
            'stok' => 'integer', 'is_active' => 'integer', 'is_delete' => 'integer',
            'created_at' => 'datetime', 'updated_at' => 'datetime', 'deleted_at' => 'datetime',
        ];
    }

    public function sekolah(): BelongsTo
    {
        return $this->belongsTo(TbSekolah::class, 'id_sekolah', 'id_sekolah');
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(TbKategori::class, 'id_kategori', 'id_kategori');
    }

    public function kelompokKategori(): BelongsTo
    {
        return $this->belongsTo(TbKelompokKategori::class, 'id_kelompok_kategori', 'id_kelompok');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(TbSupplier::class, 'id_supplier', 'id_supplier');
    }

    public function detailPembelian(): HasMany
    {
        return $this->hasMany(TbDetailPembelian::class, 'id_barang', 'id_barang');
    }

    public function detailPenjualan(): HasMany
    {
        return $this->hasMany(TbDetailPenjualan::class, 'id_barang', 'id_barang');
    }

    public function scopeAktif($query)
    {
        return $query->where('is_delete', 0);
    }
}
