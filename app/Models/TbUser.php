<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Model autentikasi utama (tb_user). Dipakai guard `web`.
 */
class TbUser extends Authenticatable
{
    use Notifiable;

    protected $table = 'tb_user';

    protected $primaryKey = 'id_user';

    public $incrementing = true;

    protected $keyType = 'int';

    const CREATED_AT = 'created_at';

    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'id_sekolah', 'id_role', 'username', 'password', 'nama_lengkap',
        'is_active', 'created_at', 'created_by', 'updated_at', 'updated_by',
        'deleted_at', 'deleted_by',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function sekolah(): BelongsTo
    {
        return $this->belongsTo(TbSekolah::class, 'id_sekolah', 'id_sekolah');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'id_role', 'id_role');
    }

    public function penjualan(): HasMany
    {
        return $this->hasMany(TbPenjualan::class, 'id_user', 'id_user');
    }

    public function pembelian(): HasMany
    {
        return $this->hasMany(TbPembelian::class, 'id_user', 'id_user');
    }

    public function roleName(): ?string
    {
        return $this->role?->nama_role;
    }

    public function isSuperAdmin(): bool
    {
        return $this->roleName() === Role::SUPER_ADMIN;
    }

    public function isAdmin(): bool
    {
        return $this->roleName() === Role::ADMIN;
    }

    public function isKasir(): bool
    {
        return $this->roleName() === Role::KASIR;
    }
}
