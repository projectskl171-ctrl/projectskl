<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TbKategori extends Model
{
    protected $table = 'tb_kategori';

    protected $primaryKey = 'id_kategori';

    public $incrementing = true;

    protected $keyType = 'int';

    const CREATED_AT = 'created_at';

    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'id_kelompok', 'nama', 'created_at', 'created_by',
        'updated_at', 'updated_by', 'deleted_at', 'deleted_by', 'is_delete',
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
        return $this->belongsTo(TbKelompokKategori::class, 'id_kelompok', 'id_kelompok');
    }

    public function barang(): HasMany
    {
        return $this->hasMany(TbBarang::class, 'id_kategori', 'id_kategori');
    }

    /** Scope default: sembunyikan soft-deleted. */
    public function scopeAktif($query)
    {
        return $query->where('is_delete', 0);
    }
}
