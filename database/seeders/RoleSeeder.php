<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['super admin', 'admin', 'kasir'] as $nama) {
            DB::table('roles')->updateOrInsert(['nama_role' => $nama], ['nama_role' => $nama]);
        }
    }
}
