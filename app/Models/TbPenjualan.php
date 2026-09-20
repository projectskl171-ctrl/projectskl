<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TbPenjualan extends Model
{
    protected $table = 'tb_penjualan';

    protected $primaryKey = 'id_penjualan';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'id_sekolah', 'id_user', 'id_pelanggan', 'tanggal_penjualan',
        'total_faktur', 'total_bayar', 'kembalian', 'status_pembayaran',
        'jenis_transaksi', 'cara_bayar', 'note',
        'created_at', 'created_by', 'deleted_at', 'deleted_by', 'is_delete',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_penjualan' => 'datetime',
            'total_faktur' => 'decimal:2', 'total_bayar' => 'decimal:2', 'kembalian' => 'decimal:2',
            'created_at' => 'datetime', 'deleted_at' => 'datetime',
            'is_delete' => 'integer',
        ];
    }

    public function sekolah(): BelongsTo
    {
        return $this->belongsTo(TbSekolah::class, 'id_sekolah', 'id_sekolah');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(TbUser::class, 'id_user', 'id_user');
    }

    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(TbPelanggan::class, 'id_pelanggan', 'id_pelanggan');
    }

    public function details(): HasMany
    {
        return $this->hasMany(TbDetailPenjualan::class, 'id_penjualan', 'id_penjualan');
    }

    public function scopeAktif($query)
    {
        return $query->where('is_delete', 0);
    }
}
