<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TbNotifikasi extends Model
{
    protected $table = 'tb_notifikasi';

    protected $primaryKey = 'id_notifikasi';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'id_sekolah', 'id_user', 'role_target',
        'tipe', 'judul', 'pesan',
        'ref_type', 'ref_id', 'ref_key', 'href',
        'is_read', 'read_at', 'created_at',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'integer',
            'read_at' => 'datetime',
            'created_at' => 'datetime',
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

    public function scopeUnread($query)
    {
        return $query->where('is_read', 0);
    }
}
