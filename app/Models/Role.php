<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $table = 'roles';

    protected $primaryKey = 'id_role';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = ['nama_role'];

    public const SUPER_ADMIN = 'super admin';

    public const ADMIN = 'admin';

    public const KASIR = 'kasir';

    public function users(): HasMany
    {
        return $this->hasMany(TbUser::class, 'id_role', 'id_role');
    }
}
