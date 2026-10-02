<?php

namespace Database\Seeders;

use App\Models\TbNotifikasi;
use App\Models\TbUser;
use App\Support\NotifikasiService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Showcase NIXA: menghidupkan database seolah aplikasi sudah dipakai
 * ~14 hari. Menampilkan SELURUH fitur untuk screenshot makalah:
 * - penjualan tunai 14 hari terakhir (Tunai/QRIS/Transfer, ada diskon)
 * - piutang kredit umur 2 / 7 / 14 hari (notif kasir)
 * - omzet kasir >=1jt/hari & >=10jt/minggu (notif congrats)
 * - omzet sekolah >=5jt/hari (notif congrats admin)
 * - pembelian selesai (stok +) + draft
 * - stok menipis (<=10) & habis (0) (notif admin)
 * - timer langganan: 1 sekolah H-7, 1 sekolah jatuh tempo (notif super admin)
 * - nama user/pelanggan/supplier realistis
 *
 * Idempoten: berhenti bila data showcase sudah ada.
 * Jalankan: php artisan db:seed --class=DemoShowcaseSeeder
 */
class DemoShowcaseSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('tb_penjualan')->where('note', 'Showcase NIXA')->exists()) {
            $this->command->warn('Showcase NIXA sudah ada. Lewati.');

            return;
        }
        if (DB::table('tb_penjualan')->where('id_sekolah', 1)->count() >= 30) {
            $this->command->warn('Sekolah 1 sudah punya banyak transaksi. Lewati agar data asli aman.');

            return;
        }

        mt_srand(20261002);

        DB::transaction(function () {
            $this->kritisIds = [];
            $this->rapikanNama();
            $this->timerSekolah();
            $this->pembelianMasuk();
            $this->tampilkanStokKritis();
            $this->riwayatJualan();
            $this->piutangKredit();
            $this->bangunNotifikasi();
        });

        $this->command->info('Showcase NIXA selesai. Database terlihat hidup ~14 hari.');
    }

    /** Nama realistis untuk user/pelanggan/supplier (username login tidak diubah). */
    protected function rapikanNama(): void
    {
        $users = [
            1 => 'Ahmad Hidayat',
            2 => 'Sari Puspita',
            3 => 'Dedi Kurniawan',
            4 => 'Budi Santoso',
            5 => 'Rina Marlina',
            6 => 'Agus Wijaya',
            7 => 'Dewi Lestari',
            8 => 'Rudi Hartono',
            9 => 'Sinta Dewi',
        ];
        foreach ($users as $id => $nama) {
            DB::table('tb_user')->where('id_user', $id)->update(['nama_lengkap' => $nama]);
        }

        $pools = [
            1 => [
                'Siswa' => ['Andi Pratama', 'Sinta Dewi', 'Rizky Ramadhan', 'Nadia Putri', 'Fajar Nugroho', 'Intan Permata', 'Bagas Saputra', 'Citra Ayu'],
                'Guru' => ['Hidayat', 'Ratna Wijaya', 'Eko Saputra', 'Maya Anggraini'],
                'Umum' => ['Koperasi Unit 2', 'Warung Berkah', 'Fotokopi Barokah'],
            ],
            2 => [
                'Siswa' => ['Dimas Prasetyo', 'Lina Marlina', 'Yoga Saputra', 'Putri Anjani', 'Reza Fahlevi', 'Dina Puspita', 'Ilham Ramadhan', 'Wulan Sari'],
                'Guru' => ['Sutrisno', 'Sri Wahyuni', 'Joko Susilo', 'Nina Kurnia'],
                'Umum' => ['Kantin Sehat', 'Toko Berkah Jaya', 'Warung Bu Yati'],
            ],
            3 => [
                'Siswa' => ['Rian Hidayat', 'Tania Putri', 'Galih Prakoso', 'Salsaabila', 'Daffa Alghifari', 'Nabila Zahra', 'Farhan Aziz', 'Aulia Rahman'],
                'Guru' => ['Purwanto', 'Endang Lestari', 'Dedi Supriadi', 'Yuni Astuti'],
                'Umum' => ['Koperasi Siswa', 'Kedai Pojok', 'Warung Pak Dede'],
            ],
        ];
        foreach ([1, 2, 3] as $sch) {
            $rows = DB::table('tb_pelanggan as p')
                ->join('tb_kelompok_pelanggan as k', 'k.id_kelompok_pelanggan', '=', 'p.id_kelompok_pelanggan')
                ->where('k.id_sekolah', $sch)->where('p.is_delete', 0)
                ->orderBy('p.id_pelanggan')->get(['p.id_pelanggan', 'k.nama_kelompok']);
            $pakai = ['Siswa' => 0, 'Guru' => 0, 'Umum' => 0];
            foreach ($rows as $r) {
                $kel = str_contains($r->nama_kelompok, 'Siswa') ? 'Siswa' : (str_contains($r->nama_kelompok, 'Guru') || str_contains($r->nama_kelompok, 'Karyawan') ? 'Guru' : 'Umum');
                $pool = $pools[$sch][$kel];
                $nama = $pool[$pakai[$kel] % count($pool)].($pakai[$kel] >= count($pool) ? ' '.($pakai[$kel] + 1) : '');
                $pakai[$kel]++;
                DB::table('tb_pelanggan')->where('id_pelanggan', $r->id_pelanggan)->update(['nama_pelanggan' => $nama]);
            }
        }

        // Bersihkan akhiran " - T1/T2/T3" pada nama supplier.
        foreach (DB::table('tb_supplier')->where('is_delete', 0)->get(['id_supplier', 'nama']) as $s) {
            $bersih = preg_replace('/\s*-+\s*T\d+\s*$/', '', $s->nama);
            if ($bersih !== $s->nama) {
                DB::table('tb_supplier')->where('id_supplier', $s->id_supplier)->update(['nama' => $bersih]);
            }
        }
    }

    /** Atur umur langganan: SCH002 H-7, SCH003 jatuh tempo. */
    protected function timerSekolah(): void
    {
        DB::table('tb_sekolah')->where('kode_sekolah', 'SCH002')->update(['activated_at' => Carbon::today()->subDays(23)]);
        DB::table('tb_sekolah')->where('kode_sekolah', 'SCH003')->update(['activated_at' => Carbon::today()->subDays(30)]);
    }

    /** Stok kritis showcase (di luar pool jualan). */
    protected array $kritisIds = [];

    /** 2 pembelian selesai BESAR (stok +) + 1 draft kemarin, sekolah 1. */
    protected function pembelianMasuk(): void
    {
        $sup = DB::table('tb_supplier')->where('id_sekolah', 1)->where('is_delete', 0)->orderBy('id_supplier')->pluck('id_supplier')->all();
        if (count($sup) < 2) {
            return;
        }
        $no = 900 + DB::table('tb_pembelian')->count();
        $jadwal = [
            ['hr' => 12, 'sup' => $sup[0], 'note' => 'Restock 2 mingguan snack & mie', 'cara' => 'Transfer', 'jenis' => 'tunai', 'status' => 'selesai', 'n' => 8, 'qty' => [250, 400]],
            ['hr' => 5, 'sup' => $sup[1] ?? $sup[0], 'note' => 'Restock minuman dingin', 'cara' => 'Tunai', 'jenis' => 'tunai', 'status' => 'selesai', 'n' => 8, 'qty' => [200, 320]],
            ['hr' => 1, 'sup' => $sup[0], 'note' => 'Pengajuan restock ATK', 'cara' => 'Tempo 14 hari', 'jenis' => 'kredit', 'status' => 'draft', 'n' => 3, 'qty' => [40, 120]],
        ];
        foreach ($jadwal as $i => $j) {
            $barangs = DB::table('tb_barang')->where('id_sekolah', 1)->where('is_delete', 0)->inRandomOrder()->limit($j['n'])->get();
            if ($barangs->isEmpty()) {
                continue;
            }
            $tgl = Carbon::today()->subDays($j['hr'])->setHour(9)->setMinute(15);
            $total = 0;
            $lines = [];
            foreach ($barangs as $b) {
                $qty = mt_rand($j['qty'][0], $j['qty'][1]);
                $total += $qty * (float) $b->harga_beli;
                $lines[] = ['b' => $b, 'qty' => $qty];
            }
            $id = DB::table('tb_pembelian')->insertGetId([
                'id_sekolah' => 1, 'id_supplier' => $j['sup'], 'id_user' => 2,
                'nomor_faktur' => 'PO-'.date('Y').'-'.str_pad((string) ($no + $i), 4, '0', STR_PAD_LEFT),
                'tanggal_faktur' => $tgl, 'total_bayar' => $total,
                'status_pembelian' => $j['status'], 'jenis_transaksi' => $j['jenis'],
                'cara_bayar' => $j['cara'], 'note' => $j['note'].' | Showcase NIXA',
                'created_at' => $tgl, 'created_by' => 2, 'is_delete' => 0,
            ]);
            foreach ($lines as $l) {
                DB::table('tb_detail_pembelian')->insert([
                    'id_pembelian' => $id, 'id_barang' => $l['b']->id_barang,
                    'satuan' => $l['b']->satuan ?? 'pcs', 'jumlah' => $l['qty'],
                    'harga_beli' => $l['b']->harga_beli, 'subtotal' => $l['qty'] * (float) $l['b']->harga_beli,
                ]);
                if ($j['status'] === 'selesai') {
                    DB::table('tb_barang')->where('id_barang', $l['b']->id_barang)->increment('stok', $l['qty']);
                }
            }
        }
    }

    /** 2 produk menipis + 1 habis (di luar pool barang jualan). */
    protected function tampilkanStokKritis(): void
    {
        $ids = DB::table('tb_barang')->where('id_sekolah', 1)->where('is_delete', 0)
            ->where('stok', '>', 60)->orderBy('stok')->limit(3)->pluck('id_barang')->all();
        if (count($ids) < 3) {
            return;
        }
        $this->kritisIds = $ids;
        DB::table('tb_barang')->where('id_barang', $ids[0])->update(['stok' => 5]);
        DB::table('tb_barang')->where('id_barang', $ids[1])->update(['stok' => 8]);
        DB::table('tb_barang')->where('id_barang', $ids[2])->update(['stok' => 0]);
    }

    /** Riwayat 14 hari: tunai/QRIS/Transfer, kadang diskon. Kasir utama dominan. */
    protected function riwayatJualan(): void
    {
        foreach ([1, 2, 3] as $sch) {
            $this->jualanSekolah($sch, $sch === 1 ? 14 : 4);
        }
    }

    protected function jualanSekolah(int $sch, int $hariMundur): void
    {
        $kasir = DB::table('tb_user')->where('id_sekolah', $sch)->where('id_role', 3)->whereNull('deleted_at')->where('is_active', 1)->orderBy('id_user')->pluck('id_user')->all();
        if (empty($kasir)) {
            return;
        }
        $pelanggan = DB::table('tb_pelanggan as p')
            ->join('tb_kelompok_pelanggan as k', 'k.id_kelompok_pelanggan', '=', 'p.id_kelompok_pelanggan')
            ->where('k.id_sekolah', $sch)->where('p.is_delete', 0)
            ->orderBy('p.id_pelanggan')->pluck('p.id_pelanggan')->all();
        // Pool jualan: stok aman, hindari item kritis.
        $q = DB::table('tb_barang')->where('id_sekolah', $sch)->where('is_delete', 0)->where('is_active', 1)
            ->where('stok', '>', 40)->orderByDesc('stok')->limit(30);
        if ($sch === 1 && ! empty($this->kritisIds)) {
            $q->whereNotIn('id_barang', $this->kritisIds);
        }
        $pool = $q->get();
        if ($pool->isEmpty()) {
            return;
        }
        $stok = [];
        foreach ($pool as $b) {
            $stok[$b->id_barang] = (int) $b->stok;
        }
        $cara = ['Tunai', 'Tunai', 'Tunai', 'QRIS', 'Transfer'];
        $ramai = $sch === 1;

        for ($h = $hariMundur; $h >= 1; $h--) {
            $jml = $ramai ? ($h <= 6 ? mt_rand(38, 52) : mt_rand(10, 16)) : mt_rand(2, 4);
            for ($t = 0; $t < $jml; $t++) {
                $tgl = Carbon::today()->subDays($h)->setHour(mt_rand(7, 15))->setMinute(mt_rand(5, 55));
                // Kasir pertama (utama) menangani ~80% transaksi.
                $idKasir = (mt_rand(1, 100) <= 80 || count($kasir) === 1) ? $kasir[0] : $kasir[array_rand($kasir)];
                $this->buatJual($sch, $idKasir, $pelanggan, $stok, $pool, $cara[array_rand($cara)], $tgl);
            }
        }
        $this->dorongTargetHarian($sch, $kasir, $pelanggan, $stok, $pool, $cara);
        if ($ramai) {
            $this->dorongTargetMingguan($sch, $kasir[0], $pelanggan, $stok, $pool, $cara);
        }
    }

    /** Hari ini: kasir utama >=1,15jt dan (sch 1) sekolah >=5,4jt via borongan event. */
    protected function dorongTargetHarian(int $sch, array $kasir, array $pelanggan, array &$stok, $pool, array $cara): void
    {
        $targetKasir = 1150000;
        $targetSekolah = $sch === 1 ? 5400000 : 150000;
        $guard = 0;
        while ($guard++ < 40 && ($this->omzetKasirHariIni($sch, $kasir[0]) < $targetKasir || $this->omzetSekolahHariIni($sch) < $targetSekolah)) {
            $tgl = Carbon::today()->setHour(mt_rand(8, 15))->setMinute(mt_rand(5, 55));
            if ($this->omzetSekolahHariIni($sch) < $targetSekolah && $guard % 3 === 0) {
                $this->buatBorongan($sch, $kasir[0], $pelanggan, $stok, $pool, $tgl);
            } else {
                $this->buatJual($sch, $kasir[0], $pelanggan, $stok, $pool, $cara[array_rand($cara)], $tgl);
            }
        }
    }

    /** Pastikan omzet 7 hari kasir utama >=10,5jt (tambah borongan backdate bila kurang). */
    protected function dorongTargetMingguan(int $sch, int $idKasir, array $pelanggan, array &$stok, $pool, array $cara): void
    {
        $guard = 0;
        while ($guard++ < 10 && $this->omzetKasirMingguan($sch, $idKasir) < 10500000) {
            $tgl = Carbon::today()->subDays(mt_rand(1, 6))->setHour(mt_rand(8, 15))->setMinute(mt_rand(5, 55));
            $this->buatBorongan($sch, $idKasir, $pelanggan, $stok, $pool, $tgl);
        }
    }

    /** Borongan event (konsumsi rapat): 2-3 line qty besar dari stok teratas. */
    protected function buatBorongan(int $sch, int $idKasir, array $pelanggan, array &$stok, $pool, Carbon $tgl): void
    {
        arsort($stok);
        $top = array_slice(array_keys($stok), 0, 6);
        if (empty($top)) {
            return;
        }
        $byId = [];
        foreach ($pool as $b) {
            $byId[$b->id_barang] = $b;
        }
        $pilih = array_slice($top, 0, mt_rand(2, 3));
        $lines = [];
        foreach ($pilih as $id) {
            $sisa = $stok[$id] ?? 0;
            if ($sisa < 60 || ! isset($byId[$id])) {
                continue;
            }
            $qty = (int) min($sisa - 20, mt_rand(80, 150));
            if ($qty < 40) {
                continue;
            }
            $lines[] = ['id' => $id, 'qty' => $qty, 'disc' => 0];
        }
        if (empty($lines)) {
            return;
        }
        $this->simpanJual($sch, $idKasir, $pelanggan, $stok, $byId, $lines, 'Transfer', $tgl, 'Konsumsi acara sekolah');
    }

    /** Satu transaksi tunai lunas reguler. */
    protected function buatJual(int $sch, int $idKasir, array $pelanggan, array &$stok, $pool, string $cara, Carbon $tgl): void
    {
        $pilih = $pool->random(min(mt_rand(2, 4), $pool->count()));
        $byId = [];
        foreach ($pool as $b) {
            $byId[$b->id_barang] = $b;
        }
        $lines = [];
        foreach ($pilih as $b) {
            $sisa = $stok[$b->id_barang] ?? 0;
            if ($sisa < 5) {
                continue;
            }
            $lines[] = ['id' => $b->id_barang, 'qty' => mt_rand(1, (int) min($sisa - 2, 8)), 'disc' => mt_rand(1, 100) <= 18 ? [5, 10][array_rand([5, 10])] : 0];
        }
        if (empty($lines)) {
            return;
        }
        $this->simpanJual($sch, $idKasir, $pelanggan, $stok, $byId, $lines, $cara, $tgl, 'Showcase NIXA');
    }

    protected function simpanJual(int $sch, int $idKasir, array $pelanggan, array &$stok, array $byId, array $lines, string $cara, Carbon $tgl, string $note): void
    {
        $total = 0;
        $siap = [];
        foreach ($lines as $l) {
            if (! isset($byId[$l['id']])) {
                continue;
            }
            $b = $byId[$l['id']];
            $harga = (float) $b->harga_jual;
            $discNom = (int) round($harga * $l['qty'] * ($l['disc'] / 100));
            $sub = $harga * $l['qty'] - $discNom;
            $total += $sub;
            $siap[] = ['b' => $b, 'qty' => $l['qty'], 'disc' => $l['disc'], 'discNom' => $discNom, 'sub' => $sub];
        }
        if (empty($siap) || $total <= 0) {
            return;
        }
        $bayar = $cara === 'Tunai' ? ceil($total / 5000) * 5000 : $total;
        $id = DB::table('tb_penjualan')->insertGetId([
            'id_sekolah' => $sch, 'id_user' => $idKasir,
            'id_pelanggan' => ! empty($pelanggan) && mt_rand(1, 100) <= 70 ? $pelanggan[array_rand($pelanggan)] : null,
            'tanggal_penjualan' => $tgl, 'total_faktur' => $total,
            'total_bayar' => $bayar, 'kembalian' => max(0, $bayar - $total),
            'status_pembayaran' => 'sudah bayar', 'jenis_transaksi' => 'tunai',
            'cara_bayar' => $cara, 'note' => $note,
            'created_at' => $tgl, 'created_by' => $idKasir, 'is_delete' => 0,
        ]);
        foreach ($siap as $l) {
            DB::table('tb_detail_penjualan')->insert([
                'id_penjualan' => $id, 'id_barang' => $l['b']->id_barang,
                'jumlah_barang' => $l['qty'], 'harga_beli' => $l['b']->harga_beli,
                'harga_jual' => $l['b']->harga_jual, 'diskon_tipe' => 'persen',
                'diskon_nilai' => $l['disc'], 'diskon_nominal' => $l['discNom'], 'subtotal' => $l['sub'],
            ]);
            $stok[$l['b']->id_barang] -= $l['qty'];
            DB::table('tb_barang')->where('id_barang', $l['b']->id_barang)->decrement('stok', $l['qty']);
        }
    }

    /** 3 piutang berumur tepat 2 / 7 / 14 hari untuk pelanggan bernama. */
    protected function piutangKredit(): void
    {
        $kasir = DB::table('tb_user')->where('id_sekolah', 1)->where('id_role', 3)->whereNull('deleted_at')->orderBy('id_user')->value('id_user');
        if (! $kasir) {
            return;
        }
        $plg = function (string $nama) {
            return DB::table('tb_pelanggan as p')
                ->join('tb_kelompok_pelanggan as k', 'k.id_kelompok_pelanggan', '=', 'p.id_kelompok_pelanggan')
                ->where('k.id_sekolah', 1)->where('p.nama_pelanggan', $nama)->value('p.id_pelanggan');
        };
        $qb = DB::table('tb_barang')->where('id_sekolah', 1)->where('is_delete', 0)->where('stok', '>', 25)->orderBy('id_barang');
        if (! empty($this->kritisIds)) {
            $qb->whereNotIn('id_barang', $this->kritisIds);
        }
        $barang = $qb->limit(6)->get();
        if ($barang->count() < 3) {
            return;
        }
        $paket = [
            ['umur' => 2, 'nama' => 'Rizky Ramadhan', 'items' => [[0, 4, 0], [1, 3, 0]]],
            ['umur' => 7, 'nama' => 'Hidayat', 'items' => [[2, 6, 5], [3, 2, 0]]],
            ['umur' => 14, 'nama' => 'Koperasi Unit 2', 'items' => [[4, 20, 10], [5, 10, 0]]],
        ];
        foreach ($paket as $p) {
            $idPlg = $plg($p['nama']);
            $tgl = Carbon::today()->subDays($p['umur'])->setHour(10)->setMinute(30);
            $total = 0;
            $lines = [];
            foreach ($p['items'] as [$bi, $qty, $disc]) {
                $b = $barang[$bi];
                $qty = min($qty, max(1, (int) $b->stok - 2));
                $harga = (float) $b->harga_jual;
                $discNom = (int) round($harga * $qty * ($disc / 100));
                $sub = $harga * $qty - $discNom;
                $total += $sub;
                $lines[] = ['b' => $b, 'qty' => $qty, 'disc' => $disc, 'discNom' => $discNom, 'sub' => $sub];
            }
            $id = DB::table('tb_penjualan')->insertGetId([
                'id_sekolah' => 1, 'id_user' => $kasir, 'id_pelanggan' => $idPlg,
                'tanggal_penjualan' => $tgl, 'total_faktur' => $total,
                'total_bayar' => 0, 'kembalian' => 0,
                'status_pembayaran' => 'belum bayar', 'jenis_transaksi' => 'kredit',
                'cara_bayar' => 'Tempo', 'note' => 'Showcase NIXA',
                'created_at' => $tgl, 'created_by' => $kasir, 'is_delete' => 0,
            ]);
            foreach ($lines as $l) {
                DB::table('tb_detail_penjualan')->insert([
                    'id_penjualan' => $id, 'id_barang' => $l['b']->id_barang,
                    'jumlah_barang' => $l['qty'], 'harga_beli' => $l['b']->harga_beli,
                    'harga_jual' => $l['b']->harga_jual, 'diskon_tipe' => 'persen',
                    'diskon_nilai' => $l['disc'], 'diskon_nominal' => $l['discNom'], 'subtotal' => $l['sub'],
                ]);
                DB::table('tb_barang')->where('id_barang', $l['b']->id_barang)->decrement('stok', $l['qty']);
            }
        }
    }

    /** Bangkitkan baris tb_notifikasi untuk semua role (dibiarkan belum dibaca). */
    protected function bangunNotifikasi(): void
    {
        foreach (['kasir', 'admin', 'super admin'] as $role) {
            $u = TbUser::whereNull('deleted_at')->whereHas('role', fn ($q) => $q->where('nama_role', $role))
                ->where('is_active', 1)->orderBy('id_user')->first();
            if ($u) {
                NotifikasiService::ensureForUser($u);
            }
        }
        // Pastikan badge super admin ikut terisi bila ada timer.
        $super = TbUser::whereNull('deleted_at')->whereHas('role', fn ($q) => $q->where('nama_role', 'super admin'))->orderBy('id_user')->first();
        if ($super) {
            NotifikasiService::ensureForUser($super);
        }
    }

    protected function omzetKasirHariIni(int $sch, int $idKasir): float
    {
        return (float) DB::table('tb_penjualan')->where('is_delete', 0)
            ->where('id_sekolah', $sch)->where('id_user', $idKasir)
            ->whereDate('tanggal_penjualan', Carbon::today()->toDateString())->sum('total_faktur');
    }

    protected function omzetSekolahHariIni(int $sch): float
    {
        return (float) DB::table('tb_penjualan')->where('is_delete', 0)
            ->where('id_sekolah', $sch)
            ->whereDate('tanggal_penjualan', Carbon::today()->toDateString())->sum('total_faktur');
    }

    protected function omzetKasirMingguan(int $sch, int $idKasir): float
    {
        return (float) DB::table('tb_penjualan')->where('is_delete', 0)
            ->where('id_sekolah', $sch)->where('id_user', $idKasir)
            ->where('tanggal_penjualan', '>=', Carbon::today()->subDays(6)->startOfDay())->sum('total_faktur');
    }
}
