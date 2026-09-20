<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TbSupplier extends Model
{
    protected $table = 'tb_supplier';

    protected $primaryKey = 'id_supplier';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'id_sekolah', 'nama', 'no_telepon', 'alamat_supplier',
        'created_at', 'created_by', 'deleted_at', 'deleted_by', 'is_delete',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime', 'deleted_at' => 'datetime',
            'is_delete' => 'integer',
        ];
    }

    public function sekolah(): BelongsTo
    {
        return $this->belongsTo(TbSekolah::class, 'id_sekolah', 'id_sekolah');
    }

    public function barang(): HasMany
    {
        return $this->hasMany(TbBarang::class, 'id_supplier', 'id_supplier');
    }

    public function pembelian(): HasMany
    {
        return $this->hasMany(TbPembelian::class, 'id_supplier', 'id_supplier');
    }

    public function scopeAktif($query)
    {
        return $query->where('is_delete', 0);
    }
}
