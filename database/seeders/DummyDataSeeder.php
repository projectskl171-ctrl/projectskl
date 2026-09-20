<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder data dummy POS (3 sekolah x 100 produk + master).
 *
 * SUMBER UTAMA: file `database/seeders/sql/dummy.sql` (SQL asli dari guru).
 * Jika file tersebut ada, seeder menjalankannya apa adanya — hanya baris
 * `USE db_pos` yang diabaikan agar memakai database Laravel aktif (db_sekolah).
 *
 * FALLBACK: bila file SQL belum ditaruh, seeder merekonstruksi dataset yang
 * SAMA dari pola SQL yang diberikan (porting format SQL -> PHP, bukan data
 * karangan): 3 sekolah SCH001-003, 3 roles, 9 users (password "123",
 * di-hash bcrypt via Hash::make), 12 kelompok kategori, 36 kategori,
 * 15 supplier, 9 kelompok pelanggan, 45 pelanggan, 300 barang (100/tenant
 * dengan nama, barcode, harga, dan stok sesuai pola dummy SQL).
 */
class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/sql/dummy.sql');

        if (is_file($path) && filesize($path) > 0) {
            $this->runSqlFile($path);

            return;
        }

        $this->seedProcedural();
    }

    /**
     * Jalankan file SQL asli (mendukung variabel @, ON DUPLICATE KEY,
     * INSERT..SELECT, dan multi-statement).
     */
    protected function runSqlFile(string $path): void
    {
        $sql = file_get_contents($path);

        // Abaikan USE `db_pos` agar memakai koneksi/database Laravel aktif.
        $sql = preg_replace('/^\s*USE\s+`?db_pos`?\s*;/mi', '', $sql);
        // Buang statement validasi SELECT akhir (tidakneeded untuk seeding).
        // Tetap biarkan; SELECT via unprepared aman (hasil diabaikan).

        try {
            DB::unprepared($sql);
        } catch (\Throwable $e) {
            // Fallback: eksekusi per statement.
            foreach ($this->splitStatements($sql) as $stmt) {
                $stmt = trim($stmt);
                if ($stmt === '' || str_starts_with(ltrim($stmt), '--')) {
                    continue;
                }
                DB::unprepared($stmt);
            }
        }
    }

    /** Pecah multi-statement pada ";<newline>" di luar string quotes. */
    protected function splitStatements(string $sql): array
    {
        $out = [];
        $buf = '';
        $quote = null;
        $len = strlen($sql);

        for ($i = 0; $i < $len; $i++) {
            $ch = $sql[$i];
            $buf .= $ch;

            if ($quote !== null) {
                if ($ch === $quote && ($sql[$i - 1] ?? '') !== '\\') {
                    $quote = null;
                }

                continue;
            }

            if ($ch === "'" || $ch === '"' || $ch === '`') {
                $quote = $ch;

                continue;
            }

            if ($ch === ';' && ($sql[$i + 1] ?? '') === "\n") {
                $out[] = $buf;
                $buf = '';
            }
        }

        if (trim($buf) !== '') {
            $out[] = $buf;
        }

        return $out;
    }

    // -----------------------------------------------------------------
    // Fallback prosedural — replika pola dummy SQL (nilai identik).
    // -----------------------------------------------------------------

    protected function seedProcedural(): void
    {
        $now = now()->toDateTimeString();
        // Semua password dummy = "123", di-hash aman (bcrypt) saat seeding.
        $pw = Hash::make('123');

        $sekolahIds = [];
        foreach ($this->sekolahRows() as $row) {
            $id = DB::table('tb_sekolah')->where('kode_sekolah', $row['kode_sekolah'])->value('id_sekolah');
            if (! $id) {
                $id = DB::table('tb_sekolah')->insertGetId($row + ['created_at' => $now]);
            }
            $sekolahIds[] = (int) $id;
        }

        $roleIds = [
            'super admin' => (int) DB::table('roles')->where('nama_role', 'super admin')->value('id_role'),
            'admin' => (int) DB::table('roles')->where('nama_role', 'admin')->value('id_role'),
            'kasir' => (int) DB::table('roles')->where('nama_role', 'kasir')->value('id_role'),
        ];

        foreach ($sekolahIds as $t => $idSekolah) {
            $n = $t + 1;

            // --- users: 1 super admin, 1 admin, 1 kasir per sekolah ---
            $users = [
                ["sch00{$n}_superadmin", $roleIds['super admin'], "Super Admin {$this->sekolahRows()[$t]['nama_sekolah']}"],
                ["sch00{$n}_admin", $roleIds['admin'], "Admin {$this->sekolahRows()[$t]['nama_sekolah']}"],
                ["sch00{$n}_kasir", $roleIds['kasir'], "Kasir {$this->sekolahRows()[$t]['nama_sekolah']}"],
            ];
            foreach ($users as [$username, $idRole, $nama]) {
                $exists = DB::table('tb_user')
                    ->where('id_sekolah', $idSekolah)->where('username', $username)->exists();
                if (! $exists) {
                    DB::table('tb_user')->insert([
                        'id_sekolah' => $idSekolah, 'id_role' => $idRole,
                        'username' => $username, 'password' => $pw,
                        'nama_lengkap' => $nama, 'is_active' => 1, 'created_at' => $now,
                    ]);
                }
            }

            // --- kelompok kategori + kategori (4 kelompok x 3) ---
            $kelompokIds = [];
            $kategoriIds = []; // [kelompokIdx][kategorikeIdx]
            foreach (['Makanan', 'Minuman', 'ATK', 'Kebutuhan Harian'] as $namaKelompok) {
                $idKel = DB::table('tb_kelompok_kategori')
                    ->where('id_sekolah', $idSekolah)->where('nama_kelompok', $namaKelompok)
                    ->value('id_kelompok');
                if (! $idKel) {
                    $idKel = DB::table('tb_kelompok_kategori')->insertGetId([
                        'id_sekolah' => $idSekolah, 'nama_kelompok' => $namaKelompok, 'created_at' => $now,
                    ]);
                }
                $kelompokIds[] = (int) $idKel;
            }
            $kategoriNama = [
                ['Snack', 'Roti & Kue', 'Makanan Instan'],
                ['Air Mineral', 'Minuman Botol', 'Minuman Serbuk'],
                ['Alat Tulis', 'Kertas & Buku', 'Perlengkapan Sekolah'],
                ['Kebersihan', 'Aksesori', 'Lain-lain'],
            ];
            foreach ($kategoriNama as $ki => $names) {
                foreach ($names as $nama) {
                    $idKat = DB::table('tb_kategori')
                        ->where('id_kelompok', $kelompokIds[$ki])->where('nama', $nama)
                        ->where('is_delete', 0)->value('id_kategori');
                    if (! $idKat) {
                        $idKat = DB::table('tb_kategori')->insertGetId([
                            'id_kelompok' => $kelompokIds[$ki], 'nama' => $nama,
                            'created_at' => $now, 'is_delete' => 0,
                        ]);
                    }
                    $kategoriIds[$ki][] = (int) $idKat;
                }
            }
            // Flatten: [Snack, Roti, MInstan, AirMin, MinBotol, MinSerbuk, AlatTulis, Kertas, Perlengkapan, Kebersihan, Aksesori, Lain]
            $kat = array_merge(...$kategoriIds);

            // --- supplier (5) ---
            $supplierSeed = [
                ['CV Sumber Makmur - T'.$n, 'Jl. Industri No. 1'],
                ['PT Maju Jaya Distribusi - T'.$n, 'Jl. Niaga No. 2'],
                ['UD Berkah Abadi - T'.$n, 'Jl. Pasar No. 3'],
                ['CV Sentosa Grosir - T'.$n, 'Jl. Raya No. 4'],
                ['PT Nusantara Supply - T'.$n, 'Jl. Logistik No. 5'],
            ];
            $supplierIds = [];
            foreach ($supplierSeed as $k => $s) {
                $idSpl = DB::table('tb_supplier')
                    ->where('id_sekolah', $idSekolah)->where('nama', $s[0])
                    ->where('is_delete', 0)->value('id_supplier');
                if (! $idSpl) {
                    $idSpl = DB::table('tb_supplier')->insertGetId([
                        'id_sekolah' => $idSekolah, 'nama' => $s[0],
                        'no_telepon' => '81211110'.$n.'0'.($k + 1),
                        'alamat_supplier' => $s[1], 'created_at' => $now, 'is_delete' => 0,
                    ]);
                }
                $supplierIds[] = (int) $idSpl;
            }

            // --- kelompok pelanggan (3) + pelanggan (15) ---
            $kpIds = [];
            foreach (['Siswa', 'Guru & Karyawan', 'Umum'] as $namaKp) {
                $idKp = DB::table('tb_kelompok_pelanggan')
                    ->where('id_sekolah', $idSekolah)->where('nama_kelompok', $namaKp)
                    ->value('id_kelompok_pelanggan');
                if (! $idKp) {
                    $idKp = DB::table('tb_kelompok_pelanggan')->insertGetId([
                        'id_sekolah' => $idSekolah, 'nama_kelompok' => $namaKp,
                    ]);
                }
                $kpIds[] = (int) $idKp;
            }
            for ($k = 1; $k <= 15; $k++) {
                $namaPlg = sprintf('Pelanggan T%d %02d', $n, $k);
                $exists = DB::table('tb_pelanggan')
                    ->where('id_kelompok_pelanggan', $kpIds[($k - 1) % 3])
                    ->where('nama_pelanggan', $namaPlg)->where('is_delete', 0)->exists();
                if (! $exists) {
                    DB::table('tb_pelanggan')->insert([
                        'id_kelompok_pelanggan' => $kpIds[($k - 1) % 3],
                        'nama_pelanggan' => $namaPlg,
                        'telepon' => '0813'.$n.'00000'.str_pad((string) $k, 2, '0', STR_PAD_LEFT),
                        'alamat' => "Alamat pelanggan tenant {$n} nomor {$k}",
                        'created_at' => $now, 'is_delete' => 0,
                    ]);
                }
            }

            // --- barang (100) ---
            foreach ($this->produkRows() as $i => $p) {
                $item = $i + 1; // 1..100
                $pos = ($item - 1) % 20; // posisi dalam blok 20
                $barcode = $n.str_pad((string) $item, 11, '0', STR_PAD_LEFT);

                $hargaBeli = $p['beli'] + 100 * $t;
                $hargaJual = $p['jual']
                    + ($t >= 1 && in_array($pos, [7, 8, 9, 17, 18, 19], true) ? 500 : 0)
                    + ($t >= 2 && in_array($pos, [0, 1, 2, 10, 11], true) ? 500 : 0);
                $stok = $p['stok'] + 3 * $t;
                if ($stok > 200) {
                    $stok -= 181;
                }

                // kategori & kelompok sesuai dummy SQL
                $idKat = $kat[$p['kat']];
                $idKelompok = $kelompokIds[$p['kg']];

                DB::table('tb_barang')->updateOrInsert(
                    ['barcode' => $barcode, 'id_sekolah' => $idSekolah],
                    [
                        'nama' => $p['nama'], 'id_kategori' => $idKat,
                        'id_kelompok_kategori' => $idKelompok,
                        'id_supplier' => $supplierIds[($item - 1) % 5],
                        'satuan' => $p['satuan'], 'harga_beli' => $hargaBeli,
                        'harga_jual' => $hargaJual, 'stok' => $stok,
                        'is_active' => 1, 'created_at' => $now, 'is_delete' => 0,
                    ]
                );
            }
        }
    }

    protected function sekolahRows(): array
    {
        return [
            ['kode_sekolah' => 'SCH001', 'nama_sekolah' => 'SMK Negeri 1 Teknologi', 'alamat_sekolah' => 'Jl. Merdeka No. 1', 'website' => 'https://smkn1teknologi.sch.id', 'is_active' => 1],
            ['kode_sekolah' => 'SCH002', 'nama_sekolah' => 'SMK Negeri 2 Informatika', 'alamat_sekolah' => 'Jl. Pendidikan No. 25', 'website' => 'https://smkn2informatika.sch.id', 'is_active' => 1],
            ['kode_sekolah' => 'SCH003', 'nama_sekolah' => 'SMA Negeri 3 Mandiri', 'alamat_sekolah' => 'Jl. Pelajar No. 10', 'website' => 'https://sman3mandiri.sch.id', 'is_active' => 1],
        ];
    }

    /**
     * 100 produk tenant 1 (nama, kategori, satuan, harga, stok) — disalin
     * dari dummy SQL. kat: 0 Snack,1 Roti&Kue,2 MakananInstan,3 AirMineral,
     * 4 MinumanBotol,5 MinumanSerbuk,6 AlatTulis,7 Kertas&Buku,
     * 8 PerlengkapanSekolah,9 Kebersihan,10 Aksesori,11 Lain-lain.
     * kg: 0 Makanan,1 Minuman,2 ATK,3 KebutuhanHarian.
     */
    protected function produkRows(): array
    {
        $beli = [1650, 2000, 2350, 2700, 3050, 3400, 3750, 4100, 4450, 4800, 5150, 5500, 5850, 6200, 6550, 6900, 7250, 7600, 7950, 1300];
        $jual = [2000, 2500, 3000, 3500, 4000, 4500, 5000, 5000, 5500, 6000, 6500, 7000, 7500, 8000, 8500, 9000, 9500, 9500, 10000, 1500];
        $nama = ['Keripik Kentang', 'Keripik Singkong', 'Wafer Cokelat', 'Biskuit Susu', 'Kacang Panggang', 'Stik Keju', 'Popcorn', 'Permen Mint', 'Cokelat Batang', 'Roti Cokelat', 'Roti Keju', 'Roti Sosis', 'Donat Gula', 'Donat Cokelat', 'Bolu Mini', 'Kue Lapis', 'Brownies Mini', 'Mi Goreng', 'Mi Kuah Ayam', 'Mi Soto', 'Bubur Instan', 'Nasi Instan', 'Sereal Cup', 'Oatmeal Cup', 'Makaroni Instan', 'Air Mineral 330ml', 'Air Mineral 600ml', 'Air Mineral 1.5L', 'Air Mineral Cup', 'Air Mineral Botol Sport', 'Air Mineral Premium', 'Teh Botol', 'Teh Lemon', 'Susu Cokelat', 'Susu Stroberi', 'Jus Jeruk', 'Jus Jambu', 'Isotonik', 'Kopi Susu', 'Minuman Cokelat', 'Cokelat Sachet', 'Kopi Sachet', 'Teh Tarik Sachet', 'Susu Sachet', 'Jeruk Serbuk', 'Lemon Serbuk', 'Jahe Instan', 'Pulpen Hitam', 'Pulpen Biru', 'Pensil 2B', 'Pensil HB', 'Penghapus', 'Penggaris 30cm', 'Spidol Hitam', 'Spidol Warna', 'Stabilo', 'Correction Pen', 'Buku Tulis 38 Lembar', 'Buku Tulis 58 Lembar', 'Buku Gambar A4', 'Kertas HVS A4 100lbr', 'Kertas Folio 100lbr', 'Sticky Notes', 'Buku Agenda', 'Buku Kwitansi', 'Map Plastik', 'Map Kertas', 'Tempat Pensil', 'Binder', 'Isi Binder', 'Lem Kertas', 'Gunting', 'Stapler Mini', 'Isi Staples', 'Tisu Saku', 'Tisu Basah', 'Hand Sanitizer', 'Masker 5pcs', 'Sabun Cair Mini', 'Sikat Gigi', 'Pasta Gigi Mini', 'Gantungan Kunci', 'Pin Sekolah', 'Lanyard', 'Name Tag', 'Kaos Kaki Sekolah', 'Tali Sepatu', 'Jepit Kertas Warna', 'Kantong Plastik', 'Baterai AA', 'Baterai AAA', 'Payung Lipat', 'Jas Hujan Sekali Pakai', 'Botol Minum', 'Kotak Makan', 'Kabel Data', 'Earphone Basic', 'Pulpen Gel Variasi 1', 'Pulpen Gel Variasi 2', 'Pulpen Gel Variasi 3'];
        $katIdx = [0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 1, 1, 1, 1, 1, 1, 1, 2, 2, 2, 2, 2, 2, 2, 2, 3, 3, 3, 3, 3, 3, 4, 4, 4, 4, 4, 4, 4, 4, 4, 5, 5, 5, 5, 5, 5, 5, 6, 6, 6, 6, 6, 6, 6, 6, 6, 6, 7, 7, 7, 7, 7, 7, 7, 7, 8, 8, 8, 8, 8, 8, 8, 8, 8, 9, 9, 9, 9, 9, 9, 9, 10, 10, 10, 10, 10, 10, 10, 11, 11, 11, 11, 11, 11, 11, 11, 11, 6, 6, 6];
        $kgIdx = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 2, 2, 2];

        $rows = [];
        for ($i = 0; $i < 100; $i++) {
            $item = $i + 1;
            $pos = $i % 20;
            // Stok T1: blok 1-25 mulai 30, 26-51 mulai 24, 52-77 mulai 25, 78-100 mulai 26; +7/item.
            if ($item <= 25) {
                $stok = 30 + 7 * ($item - 1);
            } elseif ($item <= 51) {
                $stok = 24 + 7 * ($item - 26);
            } elseif ($item <= 77) {
                $stok = 25 + 7 * ($item - 52);
            } else {
                $stok = 26 + 7 * ($item - 78);
            }

            $satuan = 'pcs';
            if (in_array($item, [61, 62], true)) {
                $satuan = 'pack';
            } elseif (in_array($item, [90, 91], true)) {
                $satuan = 'pasang';
            }

            $rows[] = [
                'nama' => $nama[$i], 'kat' => $katIdx[$i], 'kg' => $kgIdx[$i],
                'satuan' => $satuan, 'beli' => $beli[$pos], 'jual' => $jual[$pos], 'stok' => $stok,
            ];
        }

        return $rows;
    }
}
