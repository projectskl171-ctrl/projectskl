<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TbPelanggan extends Model
{
    protected $table = 'tb_pelanggan';

    protected $primaryKey = 'id_pelanggan';

    public $incrementing = true;

    protected $keyType = 'int';

    const CREATED_AT = 'created_at';

    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'id_kelompok_pelanggan', 'nama_pelanggan', 'telepon', 'alamat',
        'created_at', 'created_by', 'updated_at', 'updated_by',
        'deleted_at', 'deleted_by', 'is_delete',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime', 'updated_at' => 'datetime',
            'deleted_at' => 'datetime', 'is_delete' => 'integer',
        ];
    }

    public function kelompok(): BelongsTo
    {
        return $this->belongsTo(TbKelompokPelanggan::class, 'id_kelompok_pelanggan', 'id_kelompok_pelanggan');
    }

    public function penjualan(): HasMany
    {
        return $this->hasMany(TbPenjualan::class, 'id_pelanggan', 'id_pelanggan');
    }

    public function scopeAktif($query)
    {
        return $query->where('is_delete', 0);
    }
}
