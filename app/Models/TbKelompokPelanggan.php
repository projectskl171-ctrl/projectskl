<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TbKelompokPelanggan extends Model
{
    protected $table = 'tb_kelompok_pelanggan';

    protected $primaryKey = 'id_kelompok_pelanggan';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = ['id_sekolah', 'nama_kelompok'];

    public function sekolah(): BelongsTo
    {
        return $this->belongsTo(TbSekolah::class, 'id_sekolah', 'id_sekolah');
    }

    public function pelanggan(): HasMany
    {
        return $this->hasMany(TbPelanggan::class, 'id_kelompok_pelanggan', 'id_kelompok_pelanggan');
    }
}
