<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TbKelompokKategori extends Model
{
    protected $table = 'tb_kelompok_kategori';

    protected $primaryKey = 'id_kelompok';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = ['id_sekolah', 'nama_kelompok', 'created_at', 'created_by'];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }

    public function sekolah(): BelongsTo
    {
        return $this->belongsTo(TbSekolah::class, 'id_sekolah', 'id_sekolah');
    }

    public function kategori(): HasMany
    {
        return $this->hasMany(TbKategori::class, 'id_kelompok', 'id_kelompok');
    }

    public function barang(): HasMany
    {
        return $this->hasMany(TbBarang::class, 'id_kelompok_kategori', 'id_kelompok');
    }
}
