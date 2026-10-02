<?php

namespace App\Support;

use App\Models\TbBarang;
use App\Models\TbNotifikasi;
use App\Models\TbPenjualan;
use App\Models\TbSekolah;
use App\Models\TbUser;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Generator notifikasi persisten (tb_notifikasi).
 *
 * - Kasir: HANYA pengingat kredit (2 hari + tiap 7 hari) + congrats omzet
 *   pribadi (1jt/hari, 10jt/minggu).
 * - Admin: HANYA stok menipis/habis + congrats omzet sekolah
 *   (5jt/hari, 30jt/minggu — lebih besar karena mencakup 1 sekolah).
 * - Super admin: timer langganan sekolah 30 hari (H-7 & H+0).
 *
 * Idempoten via kolom ref_key + unique constraint: generate ulang tidak
 * menggandakan notif yang sama.
 */
class NotifikasiService
{
    public const KASIR_HARIAN = 1000000;

    public const KASIR_MINGGUAN = 10000000;

    public const ADMIN_HARIAN = 5000000;

    public const ADMIN_MINGGUAN = 30000000;

    /** Pastikan notif-notif terbaru ada untuk user ini (dipanggil tiap index). */
    public static function ensureForUser(TbUser $user): void
    {
        $user->loadMissing('role');
        $role = $user->role?->nama_role;

        try {
            if ($role === 'kasir') {
                self::ensureKreditForSchool((int) $user->id_sekolah);
                self::ensureOmzetKasir($user);
            } elseif ($role === 'admin') {
                self::ensureStokForSchool((int) $user->id_sekolah);
                self::ensureOmzetAdmin((int) $user->id_sekolah);
            } elseif ($role === 'super admin') {
                self::ensureSekolahTimer();
            }
        } catch (\Throwable $e) {
            // Generator tidak boleh menggagalkan list notifikasi.
            report($e);
        }
    }

    /** Ambil list notif untuk user (scope sekolah + role + personal). */
    public static function listForUser(TbUser $user, int $limit = 100)
    {
        $user->loadMissing('role');
        $role = $user->role?->nama_role;

        $q = TbNotifikasi::query()->orderByDesc('id_notifikasi')->limit($limit);

        if ($role === 'super admin') {
            // HANYA timer langganan sekolah.
            $q->where('role_target', 'super admin');
        } elseif ($role === 'kasir') {
            // HANYA pengingat kredit + congrats pribadi.
            $q->where('id_sekolah', $user->id_sekolah)
                ->where(function ($w) use ($user) {
                    $w->where(function ($ww) use ($user) {
                        $ww->where('role_target', 'kasir')
                            ->where(function ($vx) use ($user) {
                                $vx->whereNull('id_user')->orWhere('id_user', $user->id_user);
                            });
                    })->orWhere(function ($ww) use ($user) {
                        // personal congrats khusus user ini
                        $ww->where('id_user', $user->id_user);
                    });
                });
        } else { // admin: HANYA stok + congrats sekolah.
            $q->where('id_sekolah', $user->id_sekolah)
                ->where('role_target', 'admin');
        }

        return $q->get();
    }

    public static function unreadCount(TbUser $user): int
    {
        $user->loadMissing('role');
        $role = $user->role?->nama_role;

        $q = TbNotifikasi::query()->where('is_read', 0);

        if ($role === 'super admin') {
            $q->where('role_target', 'super admin');
        } elseif ($role === 'kasir') {
            $q->where('id_sekolah', $user->id_sekolah)
                ->where(function ($w) use ($user) {
                    $w->where(function ($ww) use ($user) {
                        $ww->where('role_target', 'kasir')
                            ->where(function ($vx) use ($user) {
                                $vx->whereNull('id_user')->orWhere('id_user', $user->id_user);
                            });
                    })->orWhere(function ($ww) use ($user) {
                        $ww->where('id_user', $user->id_user);
                    });
                });
        } else {
            $q->where('id_sekolah', $user->id_sekolah)
                ->where('role_target', 'admin');
        }

        return (int) $q->count();
    }

    // ---------- KASIR: kredit ----------

    /** Pengingat kredit: hanya saat umur 2 hari & kelipatan 7 hari. */
    protected static function ensureKreditForSchool(int $schoolId): void
    {
        $piutangs = TbPenjualan::aktif()
            ->where('id_sekolah', $schoolId)
            ->where('status_pembayaran', 'belum bayar')
            ->with('pelanggan:id_pelanggan,nama_pelanggan')
            ->orderByDesc('id_penjualan')
            ->limit(200)
            ->get();

        foreach ($piutangs as $jual) {
            $tgl = $jual->tanggal_penjualan ?? $jual->created_at;
            if (! $tgl) {
                continue;
            }
            $hari = (int) Carbon::parse($tgl)->startOfDay()->diffInDays(Carbon::today());
            if ($hari < 2) {
                continue;
            }
            $isDuaHari = $hari === 2;
            $isMingguan = $hari >= 7 && $hari % 7 === 0;
            if (! $isDuaHari && ! $isMingguan) {
                continue;
            }

            $nama = $jual->pelanggan?->nama_pelanggan ?? ('Pelanggan #'.($jual->id_pelanggan ?? 'umum'));
            $tipe = $isDuaHari ? 'kredit_2hari' : 'kredit_mingguan';
            $refKey = "penjualan:{$jual->id_penjualan}:hari-{$hari}";
            $judul = $isDuaHari
                ? "{$nama} belum bayar selama 2 hari"
                : "{$nama} belum bayar selama {$hari} hari";
            $pesan = $isDuaHari
                ? "{$nama} belum bayar ".self::rp((float) $jual->total_faktur)." selama 2 hari. Hubungi via Data Pelanggan."
                : "{$nama} belum bayar ".self::rp((float) $jual->total_faktur)." selama {$hari} hari (" . (int) ($hari / 7) . " minggu). Segera tagih via Data Pelanggan.";

            self::firstOrCreate([
                'id_sekolah' => $schoolId,
                'id_user' => null,
                'role_target' => 'kasir',
                'tipe' => $tipe,
                'judul' => $judul,
                'pesan' => $pesan,
                'ref_type' => 'penjualan',
                'ref_id' => $jual->id_penjualan,
                'ref_key' => $refKey,
                'href' => '/pelanggan',
            ]);
        }
    }

    // ---------- KASIR: congrats pribadi ----------

    protected static function ensureOmzetKasir(TbUser $kasir): void
    {
        $today = Carbon::today();
        $weekStart = Carbon::today()->subDays(6)->startOfDay();

        $harian = (float) TbPenjualan::aktif()
            ->where('id_sekolah', $kasir->id_sekolah)
            ->where('id_user', $kasir->id_user)
            ->whereDate('tanggal_penjualan', $today->toDateString())
            ->sum('total_faktur');

        if ($harian >= self::KASIR_HARIAN) {
            self::firstOrCreate([
                'id_sekolah' => $kasir->id_sekolah,
                'id_user' => $kasir->id_user,
                'role_target' => 'kasir',
                'tipe' => 'omzet_harian',
                'judul' => 'Selamat! Kamu menghasilkan '.self::rp($harian).' dalam 1 hari!',
                'pesan' => 'Kasir anda menghasilkan '.self::rp($harian).' dalam 1 hari! Lihat riwayat transaksi.',
                'ref_type' => null,
                'ref_id' => null,
                'ref_key' => 'kasir:'.$kasir->id_user.':harian:'.$today->format('Y-m-d'),
                'href' => '/riwayat-transaksi',
            ]);
        }

        $mingguan = (float) TbPenjualan::aktif()
            ->where('id_sekolah', $kasir->id_sekolah)
            ->where('id_user', $kasir->id_user)
            ->where('tanggal_penjualan', '>=', $weekStart)
            ->sum('total_faktur');

        if ($mingguan >= self::KASIR_MINGGUAN) {
            $weekKey = $today->format('Y-W');
            self::firstOrCreate([
                'id_sekolah' => $kasir->id_sekolah,
                'id_user' => $kasir->id_user,
                'role_target' => 'kasir',
                'tipe' => 'omzet_mingguan',
                'judul' => 'Luar biasa! Kamu menghasilkan '.self::rp($mingguan).' dalam 1 minggu!',
                'pesan' => 'Kasir anda menghasilkan '.self::rp($mingguan).' dalam 1 minggu! Lihat riwayat transaksi.',
                'ref_type' => null,
                'ref_id' => null,
                'ref_key' => 'kasir:'.$kasir->id_user.':mingguan:'.$weekKey,
                'href' => '/riwayat-transaksi',
            ]);
        }
    }

    // ---------- ADMIN: stok ----------

    protected static function ensureStokForSchool(int $schoolId): void
    {
        $barangs = TbBarang::aktif()
            ->where('id_sekolah', $schoolId)
            ->where('stok', '<=', 10)
            ->orderBy('stok')
            ->limit(50)
            ->get();

        foreach ($barangs as $b) {
            $habis = (int) $b->stok <= 0;
            $tipe = $habis ? 'stok_habis' : 'stok_menipis';

            // Cegah spam: bila sudah ada notif UNREAD untuk produk+tipe ini, lewati.
            $exists = TbNotifikasi::where('id_sekolah', $schoolId)
                ->where('role_target', 'admin')
                ->where('tipe', $tipe)
                ->where('ref_type', 'barang')
                ->where('ref_id', $b->id_barang)
                ->where('is_read', 0)
                ->exists();
            if ($exists) {
                continue;
            }

            self::firstOrCreate([
                'id_sekolah' => $schoolId,
                'id_user' => null,
                'role_target' => 'admin',
                'tipe' => $tipe,
                'judul' => $habis ? "Stok habis: {$b->nama}" : "Stok menipis: {$b->nama} (sisa {$b->stok})",
                'pesan' => $habis
                    ? "{$b->nama} kehabisan stok. Segera restock via Pembelian."
                    : "{$b->nama} tinggal {$b->stok} {$b->satuan}. Segera restock via Pembelian.",
                'ref_type' => 'barang',
                'ref_id' => $b->id_barang,
                'ref_key' => "barang:{$b->id_barang}:{$tipe}:stok-{$b->stok}",
                'href' => '/pembelian',
            ]);
        }
    }

    // ---------- ADMIN: congrats sekolah ----------

    protected static function ensureOmzetAdmin(int $schoolId): void
    {
        $today = Carbon::today();
        $weekStart = Carbon::today()->subDays(6)->startOfDay();

        $harian = (float) TbPenjualan::aktif()
            ->where('id_sekolah', $schoolId)
            ->whereDate('tanggal_penjualan', $today->toDateString())
            ->sum('total_faktur');

        if ($harian >= self::ADMIN_HARIAN) {
            self::firstOrCreate([
                'id_sekolah' => $schoolId,
                'id_user' => null,
                'role_target' => 'admin',
                'tipe' => 'omzet_harian',
                'judul' => 'Sekolahmu menghasilkan '.self::rp($harian).' dalam 1 hari!',
                'pesan' => 'Luar biasa! Toko sekolah menghasilkan '.self::rp($harian).' hari ini. Pantau di laporan.',
                'ref_type' => null,
                'ref_id' => null,
                'ref_key' => "sekolah:{$schoolId}:harian:".$today->format('Y-m-d'),
                'href' => '/laporan',
            ]);
        }

        $mingguan = (float) TbPenjualan::aktif()
            ->where('id_sekolah', $schoolId)
            ->where('tanggal_penjualan', '>=', $weekStart)
            ->sum('total_faktur');

        if ($mingguan >= self::ADMIN_MINGGUAN) {
            $weekKey = $today->format('Y-W');
            self::firstOrCreate([
                'id_sekolah' => $schoolId,
                'id_user' => null,
                'role_target' => 'admin',
                'tipe' => 'omzet_mingguan',
                'judul' => 'Sekolahmu menghasilkan '.self::rp($mingguan).' dalam 1 minggu!',
                'pesan' => 'Mantap! Toko sekolah menghasilkan '.self::rp($mingguan).' minggu ini. Pantau di laporan.',
                'ref_type' => null,
                'ref_id' => null,
                'ref_key' => "sekolah:{$schoolId}:mingguan:".$weekKey,
                'href' => '/laporan',
            ]);
        }
    }

    // ---------- SUPER ADMIN: timer 30 hari ----------

    protected static function ensureSekolahTimer(): void
    {
        $sekolahs = TbSekolah::orderBy('id_sekolah')->get();

        foreach ($sekolahs as $s) {
            $awal = $s->activated_at ?? $s->created_at;
            if (! $awal) {
                continue;
            }
            // Periode hasil stacking (mulai di masa depan) belum berjalan:
            // jangan kirim notif "sudah N hari" yang salah.
            if (Carbon::parse($awal)->startOfDay()->isFuture()) {
                continue;
            }
            $hari = (int) Carbon::parse($awal)->startOfDay()->diffInDays(Carbon::today());
            if ($hari < 0) {
                continue;
            }

            // H-7: 1 minggu sebelum 1 bulan (hari ke-23).
            if ($hari === 23) {
                self::firstOrCreate([
                    'id_sekolah' => $s->id_sekolah,
                    'id_user' => null,
                    'role_target' => 'super admin',
                    'tipe' => 'sekolah_hampir_30hari',
                    'judul' => "{$s->nama_sekolah} hampir 1 bulan",
                    'pesan' => "{$s->nama_sekolah} ({$s->kode_sekolah}) akan 1 bulan dalam 1 minggu (aktif sejak ".Carbon::parse($awal)->format('d M Y')."). Siapkan konfirmasi perpanjangan.",
                    'ref_type' => 'sekolah',
                    'ref_id' => $s->id_sekolah,
                    'ref_key' => "sekolah:{$s->id_sekolah}:hampir-30hari:".Carbon::parse($awal)->format('Y-m-d'),
                    'href' => '/sekolah',
                ]);
            }

            // H+0 dan seterusnya tiap 7 hari: sudah 1 bulan, perlu konfirmasi bayar.
            if ($hari >= 30 && ($hari - 30) % 7 === 0) {
                self::firstOrCreate([
                    'id_sekolah' => $s->id_sekolah,
                    'id_user' => null,
                    'role_target' => 'super admin',
                    'tipe' => 'sekolah_30hari',
                    'judul' => "{$s->nama_sekolah} sudah 1 bulan",
                    'pesan' => "{$s->nama_sekolah} ({$s->kode_sekolah}) sudah {$hari} hari aktif (sejak ".Carbon::parse($awal)->format('d M Y').") dan perlu konfirmasi apakah mereka sudah bayar untuk memperpanjang atau belum.",
                    'ref_type' => 'sekolah',
                    'ref_id' => $s->id_sekolah,
                    'ref_key' => "sekolah:{$s->id_sekolah}:30hari:{$hari}",
                    'href' => '/sekolah',
                ]);
            }
        }
    }

    protected static function firstOrCreate(array $attrs): void
    {
        try {
            TbNotifikasi::firstOrCreate(
                [
                    'id_sekolah' => $attrs['id_sekolah'],
                    'id_user' => $attrs['id_user'],
                    'role_target' => $attrs['role_target'],
                    'tipe' => $attrs['tipe'],
                    'ref_key' => $attrs['ref_key'],
                ],
                $attrs + ['is_read' => 0, 'created_at' => now()]
            );
        } catch (\Throwable $e) {
            // Balapan unique: abaikan duplikat.
            if (! str_contains($e->getMessage(), 'uniq_notif') && ! str_contains($e->getMessage(), 'Duplicate')) {
                throw $e;
            }
        }
    }

    protected static function rp(float $n): string
    {
        return 'Rp '.number_format((int) round($n), 0, ',', '.');
    }
}
