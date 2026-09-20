<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TbSekolah extends Model
{
    protected $table = 'tb_sekolah';

    protected $primaryKey = 'id_sekolah';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'kode_sekolah', 'nama_sekolah', 'alamat_sekolah',
        'website', 'is_active', 'created_at',
    ];

    protected function casts(): array
    {
        return ['created_at' => 'datetime', 'is_active' => 'integer'];
    }

    public function users(): HasMany
    {
        return $this->hasMany(TbUser::class, 'id_sekolah', 'id_sekolah');
    }

    public function barang(): HasMany
    {
        return $this->hasMany(TbBarang::class, 'id_sekolah', 'id_sekolah');
    }

    public function suppliers(): HasMany
    {
        return $this->hasMany(TbSupplier::class, 'id_sekolah', 'id_sekolah');
    }

    public function kelompokKategori(): HasMany
    {
        return $this->hasMany(TbKelompokKategori::class, 'id_sekolah', 'id_sekolah');
    }

    public function kelompokPelanggan(): HasMany
    {
        return $this->hasMany(TbKelompokPelanggan::class, 'id_sekolah', 'id_sekolah');
    }

    public function penjualan(): HasMany
    {
        return $this->hasMany(TbPenjualan::class, 'id_sekolah', 'id_sekolah');
    }

    public function pembelian(): HasMany
    {
        return $this->hasMany(TbPembelian::class, 'id_sekolah', 'id_sekolah');
    }
}
