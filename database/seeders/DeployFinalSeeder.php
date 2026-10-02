<?php

namespace Database\Seeders;

use App\Models\TbUser;
use App\Support\NotifikasiService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Konsolidasi deploy NIXA:
 * - 4 sekolah: SMKN 1-4 Tasikmalaya (SCH001-SCH004)
 * - tiap sekolah >= 10 user (admin + kasir), password SEMUA "123"
 * - timer langganan: SCH002 H-7, SCH003 jatuh tempo
 * - aktivitas ringan SMKN 4 + regenerasi notifikasi
 *
 * Idempoten: aman dijalankan ulang (cek keberadaan dulu).
 * Jalankan: php artisan db:seed --class=DeployFinalSeeder
 */
class DeployFinalSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $this->sekolahTetap();
            $this->userCukup();
            $this->passwordRata();
            $this->aktivitasSch4();
            $this->piutangSch2();
            $this->pembelianSekolah();
            $this->stokKritisSemua();
            $this->notifikasiUlang();
        });

        $this->command->info('DeployFinal: 4 sekolah Tasikmalaya, 10+ user/sekolah, pw 123, notif segar.');
    }

    protected function idSekolah(string $kode): ?int
    {
        return DB::table('tb_sekolah')->where('kode_sekolah', $kode)->value('id_sekolah');
    }

    /** Nama + alamat + website final + timer langganan. */
    protected function sekolahTetap(): void
    {
        $tetap = [
            'SCH001' => ['nama' => 'SMKN 1 Tasikmalaya', 'alamat' => 'Jl. Merdeka No. 1, Tasikmalaya', 'web' => 'https://smkn1tasikmalaya.sch.id'],
            'SCH002' => ['nama' => 'SMKN 2 Tasikmalaya', 'alamat' => 'Jl. Pendidikan No. 25, Tasikmalaya', 'web' => 'https://smkn2tasikmalaya.sch.id'],
            'SCH003' => ['nama' => 'SMKN 3 Tasikmalaya', 'alamat' => 'Jl. Pelajar No. 10, Tasikmalaya', 'web' => 'https://smkn3tasikmalaya.sch.id'],
            'SCH004' => ['nama' => 'SMKN 4 Tasikmalaya', 'alamat' => 'Jl. Raya Timur No. 77, Tasikmalaya', 'web' => 'https://smkn4tasikmalaya.sch.id'],
        ];
        foreach ($tetap as $kode => $d) {
            DB::table('tb_sekolah')->where('kode_sekolah', $kode)->update([
                'nama_sekolah' => $d['nama'], 'alamat_sekolah' => $d['alamat'], 'website' => $d['web'],
            ]);
        }
        DB::table('tb_sekolah')->where('kode_sekolah', 'SCH002')->update(['activated_at' => Carbon::today()->subDays(23)]);
        DB::table('tb_sekolah')->where('kode_sekolah', 'SCH003')->update(['activated_at' => Carbon::today()->subDays(30)]);
    }

    /** Tiap sekolah >= 10 user (ada admin + kasir). */
    protected function userCukup(): void
    {
        $roleAdmin = DB::table('roles')->where('nama_role', 'admin')->value('id_role');
        $roleKasir = DB::table('roles')->where('nama_role', 'kasir')->value('id_role');
        $namaAdmin = ['Wulan Sari', 'Dian Purnama', 'Yoga Saputra', 'Fitri Handayani', 'Hendra Gunawan', 'Nina Kurnia', 'Tono Prasetyo', 'Lilis Lismaya'];
        $namaKasir = ['Asep Saepudin', 'Dadan Ramdan', 'Euiss Susilawati', 'Iwan Setiawan', 'Juju Junaedi', 'Kokom Komariah', 'Maman Suherman', 'Neni Nurhayati', 'Oded Mahmud', 'Pipit Puspita', 'Rian Hidayat', 'Susi Susanti', 'Tatang Sutarman', 'Ujang Suparman', 'Vina Marlina', 'Wawan Hermawan', 'Yuyun Yuningsih', 'Zaenal Arifin', 'Dadan Hidayat', 'Eka Permana'];
        $ia = 0;
        $ik = 0;

        foreach (['SCH001' => 1, 'SCH002' => 2, 'SCH003' => 3, 'SCH004' => 4] as $kode => $n) {
            $sch = $this->idSekolah($kode);
            if (! $sch) {
                continue;
            }
            $jmlAdmin = DB::table('tb_user')->where('id_sekolah', $sch)->where('id_role', $roleAdmin)->whereNull('deleted_at')->count();
            $jmlKasir = DB::table('tb_user')->where('id_sekolah', $sch)->where('id_role', $roleKasir)->whereNull('deleted_at')->count();
            $jmlSemua = DB::table('tb_user')->where('id_sekolah', $sch)->whereNull('deleted_at')->count();

            $butuhAdmin = max(0, min(2 - (int) $jmlAdmin, 10 - (int) $jmlSemua));
            $butuhKasir = max(0, 10 - (int) $jmlSemua - $butuhAdmin);
            // Pastikan minimal 2 admin & 5 kasir bila masih kurang.
            $butuhAdmin = max($butuhAdmin, max(0, 2 - (int) $jmlAdmin));
            $butuhKasir = max($butuhKasir, max(0, 5 - (int) $jmlKasir));

            for ($i = 0; $i < $butuhAdmin; $i++) {
                $this->tambahUser($sch, $roleAdmin, "smkn{$n}_admin".str_pad((string) ($jmlAdmin + $i + 1), 2, '0', STR_PAD_LEFT), $namaAdmin[$ia++ % count($namaAdmin)]);
            }
            for ($i = 0; $i < $butuhKasir; $i++) {
                $this->tambahUser($sch, $roleKasir, "smkn{$n}_kasir".str_pad((string) ($jmlKasir + $i + 1), 2, '0', STR_PAD_LEFT), $namaKasir[$ik++ % count($namaKasir)]);
            }
        }
    }

    protected function tambahUser(int $sch, int $role, string $username, string $nama): void
    {
        if (DB::table('tb_user')->where('username', $username)->exists()) {
            return;
        }
        DB::table('tb_user')->insert([
            'id_sekolah' => $sch, 'id_role' => $role,
            'username' => $username, 'password' => Hash::make('123'),
            'nama_lengkap' => $nama, 'is_active' => 1, 'created_at' => now(),
        ]);
    }

    /** SEMUA akun password "123". */
    protected function passwordRata(): void
    {
        DB::table('tb_user')->update(['password' => Hash::make('123')]);
    }

    /** Aktivitas ringan SMKN 4 (3 hari + hari ini) agar tabel per-sekolah hidup. */
    protected function aktivitasSch4(): void
    {
        $sch = $this->idSekolah('SCH004');
        if (! $sch) {
            return;
        }
        if (DB::table('tb_penjualan')->where('id_sekolah', $sch)->count() > 0) {
            return;
        }
        $kasir = DB::table('tb_user')->where('id_sekolah', $sch)->where('id_role', 3)->whereNull('deleted_at')->orderBy('id_user')->value('id_user');
        if (! $kasir) {
            return;
        }
        mt_srand(404);
        $pool = DB::table('tb_barang')->where('id_sekolah', $sch)->where('is_delete', 0)->where('is_active', 1)->where('stok', '>', 20)->orderByDesc('stok')->limit(20)->get();
        if ($pool->isEmpty()) {
            return;
        }
        $stok = [];
        foreach ($pool as $b) {
            $stok[$b->id_barang] = (int) $b->stok;
        }
        $byId = [];
        foreach ($pool as $b) {
            $byId[$b->id_barang] = $b;
        }
        $buat = function (Carbon $tgl) use ($sch, $kasir, &$stok, $byId, $pool) {
            $pilih = $pool->random(min(mt_rand(2, 3), $pool->count()));
            $total = 0;
            $siap = [];
            foreach ($pilih as $b) {
                $sisa = $stok[$b->id_barang] ?? 0;
                if ($sisa < 5) {
                    continue;
                }
                $qty = mt_rand(1, (int) min($sisa - 2, 5));
                $sub = (float) $b->harga_jual * $qty;
                $total += $sub;
                $siap[] = ['b' => $b, 'qty' => $qty, 'sub' => $sub];
            }
            if (empty($siap)) {
                return;
            }
            $id = DB::table('tb_penjualan')->insertGetId([
                'id_sekolah' => $sch, 'id_user' => $kasir, 'id_pelanggan' => null,
                'tanggal_penjualan' => $tgl, 'total_faktur' => $total,
                'total_bayar' => $total, 'kembalian' => 0,
                'status_pembayaran' => 'sudah bayar', 'jenis_transaksi' => 'tunai',
                'cara_bayar' => 'Tunai', 'note' => 'Showcase NIXA',
                'created_at' => $tgl, 'created_by' => $kasir, 'is_delete' => 0,
            ]);
            foreach ($siap as $l) {
                DB::table('tb_detail_penjualan')->insert([
                    'id_penjualan' => $id, 'id_barang' => $l['b']->id_barang,
                    'jumlah_barang' => $l['qty'], 'harga_beli' => $l['b']->harga_beli,
                    'harga_jual' => $l['b']->harga_jual, 'diskon_tipe' => 'persen',
                    'diskon_nilai' => 0, 'diskon_nominal' => 0, 'subtotal' => $l['sub'],
                ]);
                $stok[$l['b']->id_barang] -= $l['qty'];
                DB::table('tb_barang')->where('id_barang', $l['b']->id_barang)->decrement('stok', $l['qty']);
            }
        };
        for ($h = 3; $h >= 1; $h--) {
            for ($t = 0, $n = mt_rand(2, 4); $t < $n; $t++) {
                $buat(Carbon::today()->subDays($h)->setHour(mt_rand(8, 14))->setMinute(mt_rand(5, 55)));
            }
        }
        for ($t = 0; $t < 6; $t++) {
            $buat(Carbon::today()->setHour(mt_rand(8, 15))->setMinute(mt_rand(5, 55)));
        }
    }

    /** Piutang jatuh tempo SMKN 2 (umur tepat 2 / 7 / 14 hari) untuk kasir sch2. */
    protected function piutangSch2(): void
    {
        if (DB::table('tb_penjualan')->where('note', 'Jatuh tempo SMKN 2')->exists()) {
            return;
        }
        $sch = $this->idSekolah('SCH002');
        if (! $sch) {
            return;
        }
        $kasir = DB::table('tb_user')->where('id_sekolah', $sch)->where('id_role', 3)->whereNull('deleted_at')->where('is_active', 1)->orderBy('id_user')->value('id_user');
        if (! $kasir) {
            return;
        }
        $plgId = function (string $nama) use ($sch) {
            return DB::table('tb_pelanggan as p')
                ->join('tb_kelompok_pelanggan as k', 'k.id_kelompok_pelanggan', '=', 'p.id_kelompok_pelanggan')
                ->where('k.id_sekolah', $sch)->where('p.is_delete', 0)->where('p.nama_pelanggan', $nama)->value('p.id_pelanggan')
                ?? DB::table('tb_pelanggan as p')
                    ->join('tb_kelompok_pelanggan as k', 'k.id_kelompok_pelanggan', '=', 'p.id_kelompok_pelanggan')
                    ->where('k.id_sekolah', $sch)->where('p.is_delete', 0)->orderBy('p.id_pelanggan')->value('p.id_pelanggan');
        };
        $pool = DB::table('tb_barang')->where('id_sekolah', $sch)->where('is_delete', 0)->where('is_active', 1)->where('stok', '>', 25)->orderBy('id_barang')->get();
        if ($pool->count() < 6) {
            return;
        }
        $paket = [
            ['umur' => 2, 'nama' => 'Dimas Prasetyo', 'ambil' => [0, 1], 'qty' => [3, 4]],
            ['umur' => 7, 'nama' => 'Lina Marlina', 'ambil' => [2, 3], 'qty' => [5, 6]],
            ['umur' => 14, 'nama' => 'Yoga Saputra', 'ambil' => [4, 5], 'qty' => [8, 12]],
        ];
        foreach ($paket as $p) {
            $tgl = Carbon::today()->subDays($p['umur'])->setHour(10)->setMinute(30);
            $total = 0;
            $lines = [];
            foreach ($p['ambil'] as $idx => $bi) {
                $b = $pool[$bi];
                $qty = min($p['qty'][$idx], max(1, (int) $b->stok - 2));
                $sub = (float) $b->harga_jual * $qty;
                $total += $sub;
                $lines[] = ['b' => $b, 'qty' => $qty, 'sub' => $sub];
            }
            $id = DB::table('tb_penjualan')->insertGetId([
                'id_sekolah' => $sch, 'id_user' => $kasir, 'id_pelanggan' => $plgId($p['nama']),
                'tanggal_penjualan' => $tgl, 'total_faktur' => $total,
                'total_bayar' => 0, 'kembalian' => 0,
                'status_pembayaran' => 'belum bayar', 'jenis_transaksi' => 'kredit',
                'cara_bayar' => 'Tempo', 'note' => 'Jatuh tempo SMKN 2',
                'created_at' => $tgl, 'created_by' => $kasir, 'is_delete' => 0,
            ]);
            foreach ($lines as $l) {
                DB::table('tb_detail_penjualan')->insert([
                    'id_penjualan' => $id, 'id_barang' => $l['b']->id_barang,
                    'jumlah_barang' => $l['qty'], 'harga_beli' => $l['b']->harga_beli,
                    'harga_jual' => $l['b']->harga_jual, 'diskon_tipe' => 'persen',
                    'diskon_nilai' => 0, 'diskon_nominal' => 0, 'subtotal' => $l['sub'],
                ]);
                DB::table('tb_barang')->where('id_barang', $l['b']->id_barang)->decrement('stok', $l['qty']);
            }
        }
    }

    /** Riwayat pembelian tiap sekolah: 2 selesai (stok +) + 1 draft.
     *  Sekolah 1 sudah punya (showcase) sehingga dilewati. */
    protected function pembelianSekolah(): void
    {
        if (DB::table('tb_pembelian')->whereIn('nomor_faktur', ['PO-2026-0821', 'PO-2026-0831', 'PO-2026-0841'])->exists()) {
            return;
        }
        $seri = ['SCH002' => '082', 'SCH003' => '083', 'SCH004' => '084'];
        $jadwal = [
            ['hr' => 10, 'status' => 'selesai', 'note' => 'Restock mingguan snack & minuman', 'cara' => 'Transfer', 'jenis' => 'tunai'],
            ['hr' => 4, 'status' => 'selesai', 'note' => 'Restock ATK & kebutuhan harian', 'cara' => 'Tunai', 'jenis' => 'tunai', 'offset' => 3],
            ['hr' => 1, 'status' => 'draft', 'note' => 'Pengajuan restock menunggu approve', 'cara' => 'Tempo 14 hari', 'jenis' => 'kredit'],
        ];
        foreach ($seri as $kode => $prefix) {
            $sch = $this->idSekolah($kode);
            if (! $sch) {
                continue;
            }
            $sup = DB::table('tb_supplier')->where('id_sekolah', $sch)->where('is_delete', 0)->orderBy('id_supplier')->pluck('id_supplier')->all();
            $admin = DB::table('tb_user')->where('id_sekolah', $sch)->where('id_role', 2)->whereNull('deleted_at')->orderBy('id_user')->value('id_user');
            $pool = DB::table('tb_barang')->where('id_sekolah', $sch)->where('is_delete', 0)->orderBy('id_barang')->limit(9)->get();
            if (count($sup) < 1 || ! $admin || $pool->count() < 6) {
                continue;
            }
            foreach ($jadwal as $i => $j) {
                $tgl = Carbon::today()->subDays($j['hr'])->setHour(9)->setMinute(20);
                $items = $pool->slice(($j['offset'] ?? 0), 3);
                $total = 0;
                $lines = [];
                foreach ($items as $k => $b) {
                    $qty = 40 + (($k + $i) * 17) % 61; // 40–100 deterministik
                    $total += $qty * (float) $b->harga_beli;
                    $lines[] = ['b' => $b, 'qty' => $qty];
                }
                $id = DB::table('tb_pembelian')->insertGetId([
                    'id_sekolah' => $sch, 'id_supplier' => $sup[$i % count($sup)], 'id_user' => $admin,
                    'nomor_faktur' => 'PO-2026-'.$prefix.($i + 1),
                    'tanggal_faktur' => $tgl, 'total_bayar' => $total,
                    'status_pembelian' => $j['status'], 'jenis_transaksi' => $j['jenis'],
                    'cara_bayar' => $j['cara'], 'note' => $j['note'],
                    'created_at' => $tgl, 'created_by' => $admin, 'is_delete' => 0,
                ]);
                foreach ($lines as $l) {
                    DB::table('tb_detail_pembelian')->insert([
                        'id_pembelian' => $id, 'id_barang' => $l['b']->id_barang,
                        'satuan' => $l['b']->satuan ?? 'pcs', 'jumlah' => $l['qty'],
                        'harga_beli' => $l['b']->harga_beli, 'subtotal' => $l['qty'] * (float) $l['b']->harga_beli,
                    ]);
                    if ($j['status'] === 'selesai') {
                        DB::table('tb_barang')->where('id_barang', $l['b']->id_barang)->increment('stok', $l['qty']);
                        DB::table('tb_barang')->where('id_barang', $l['b']->id_barang)->update(['harga_beli' => $l['b']->harga_beli]);
                    }
                }
            }
        }
    }

    /** Tiap sekolah punya 3 produk kritis (5/8/0) agar notif stok admin terisi.
     *  Dilewati bila sekolah sudah punya >=3 item stok <=10. */
    protected function stokKritisSemua(): void
    {
        foreach (['SCH001', 'SCH002', 'SCH003', 'SCH004'] as $kode) {
            $sch = $this->idSekolah($kode);
            if (! $sch) {
                continue;
            }
            $sudah = DB::table('tb_barang')->where('id_sekolah', $sch)->where('is_delete', 0)->where('stok', '<=', 10)->count();
            if ($sudah >= 3) {
                continue;
            }
            $ids = DB::table('tb_barang')->where('id_sekolah', $sch)->where('is_delete', 0)
                ->where('stok', '>', 60)->orderBy('stok')->limit(3)->pluck('id_barang')->all();
            if (count($ids) < 3) {
                continue;
            }
            DB::table('tb_barang')->where('id_barang', $ids[0])->update(['stok' => 5]);
            DB::table('tb_barang')->where('id_barang', $ids[1])->update(['stok' => 8]);
            DB::table('tb_barang')->where('id_barang', $ids[2])->update(['stok' => 0]);
        }
    }

    /** Buang notif lama, bangkitkan ulang sesuai kondisi final (belum dibaca). */
    protected function notifikasiUlang(): void
    {
        // DELETE (bukan truncate) agar transaksi luar tidak ter-commit diam-diam.
        DB::table('tb_notifikasi')->delete();
        foreach (['SCH001', 'SCH002', 'SCH003', 'SCH004'] as $kode) {
            $sch = $this->idSekolah($kode);
            if (! $sch) {
                continue;
            }
            foreach (['kasir', 'admin'] as $role) {
                $u = TbUser::where('id_sekolah', $sch)->whereNull('deleted_at')->where('is_active', 1)
                    ->whereHas('role', fn ($q) => $q->where('nama_role', $role))->orderBy('id_user')->first();
                if ($u) {
                    NotifikasiService::ensureForUser($u);
                }
            }
        }
        $super = TbUser::whereNull('deleted_at')->where('is_active', 1)
            ->whereHas('role', fn ($q) => $q->where('nama_role', 'super admin'))->orderBy('id_user')->first();
        if ($super) {
            NotifikasiService::ensureForUser($super);
        }
    }
}
