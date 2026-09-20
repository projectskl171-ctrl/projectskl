<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed database POS: roles/master lalu dummy SQL (3 sekolah x 100 produk).
     * Jalankan: php artisan migrate:fresh --seed
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            DummyDataSeeder::class,
        ]);
    }
}
