<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TbPembelian extends Model
{
    protected $table = 'tb_pembelian';

    protected $primaryKey = 'id_pembelian';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'id_sekolah', 'id_supplier', 'id_user', 'nomor_faktur', 'tanggal_faktur',
        'total_bayar', 'status_pembelian', 'jenis_transaksi', 'cara_bayar', 'note',
        'created_at', 'created_by', 'deleted_at', 'deleted_by', 'is_delete',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_faktur' => 'datetime', 'total_bayar' => 'decimal:2',
            'created_at' => 'datetime', 'deleted_at' => 'datetime',
            'is_delete' => 'integer',
        ];
    }

    public function sekolah(): BelongsTo
    {
        return $this->belongsTo(TbSekolah::class, 'id_sekolah', 'id_sekolah');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(TbSupplier::class, 'id_supplier', 'id_supplier');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(TbUser::class, 'id_user', 'id_user');
    }

    public function details(): HasMany
    {
        return $this->hasMany(TbDetailPembelian::class, 'id_pembelian', 'id_pembelian');
    }

    public function scopeAktif($query)
    {
        return $query->where('is_delete', 0);
    }
}
