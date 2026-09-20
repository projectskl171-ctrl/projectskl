<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PenjualanRequest;
use App\Models\TbBarang;
use App\Models\TbPelanggan;
use App\Models\TbPenjualan;
use App\Support\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PenjualanController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $q = TbPenjualan::query()->aktif()
            ->with(['pelanggan:id_pelanggan,nama_pelanggan', 'user:id_user,nama_lengkap']);
        Tenant::scopeSchool($q, $user);

        if ($request->filled('status_pembayaran')) {
            $q->where('status_pembayaran', $request->query('status_pembayaran'));
        }
        if ($request->filled('tanggal')) {
            $q->whereDate('tanggal_penjualan', $request->query('tanggal'));
        }
        if ($s = $request->query('search')) {
            $q->where(function ($w) use ($s) {
                $w->where('id_penjualan', $s)
                    ->orWhere('cara_bayar', 'like', "%{$s}%")
                    ->orWhereHas('pelanggan', fn ($qq) => $qq->where('nama_pelanggan', 'like', "%{$s}%"));
            });
        }
        $q->orderByDesc('id_penjualan');

        return response()->json($q->paginate((int) $request->query('per_page', 15)));
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $penjualan = TbPenjualan::aktif()
            ->with(['pelanggan.kelompok', 'user:id_user,nama_lengkap,username', 'details.barang:id_barang,nama,barcode,satuan'])
            ->findOrFail($id);
        Tenant::ensureOwnSchool($penjualan, $request->user());

        return response()->json(['data' => $penjualan]);
    }

    /**
     * Buat transaksi penjualan:
     * harga & stok diverifikasi dari DB, total dihitung backend,
     * stok berkurang atomik dalam DB transaction.
     */
    public function store(PenjualanRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();
        $schoolId = Tenant::resolveSchoolId(null, $user);

        // Validasi pelanggan se-sekolah (via relasi kelompok).
        $idPelanggan = $data['id_pelanggan'] ?? null;
        if ($idPelanggan) {
            $plg = TbPelanggan::aktif()->with('kelompok')->find($idPelanggan);
            if (! $plg || (Tenant::isSuperAdmin($user) ? false : (int) ($plg->kelompok?->id_sekolah ?? 0) !== (int) $schoolId)) {
                return response()->json(['message' => 'Pelanggan tidak valid untuk sekolah anda.', 'errors' => ['id_pelanggan' => ['Pelanggan tidak valid.']]], 422);
            }
        }

        try {
            $penjualan = DB::transaction(function () use ($data, $user, $schoolId, $idPelanggan) {
                $ids = collect($data['lines'])->pluck('id_barang')->unique()->values();
                $barangs = TbBarang::aktif()
                    ->whereIn('id_barang', $ids)
                    ->where('id_sekolah', $schoolId)
                    ->where('is_active', 1)
                    ->lockForUpdate()
                    ->get()->keyBy('id_barang');

                $total = 0;
                $prepared = [];
                foreach ($data['lines'] as $line) {
                    $barang = $barangs[$line['id_barang']] ?? null;
                    if (! $barang) {
                        abort(response()->json([
                            'message' => "Produk #{$line['id_barang']} tidak tersedia di sekolah anda.",
                            'errors' => ['lines' => ["Produk #{$line['id_barang']} tidak valid."]],
                        ], 422));
                    }
                    if ($barang->stok < $line['qty']) {
                        abort(response()->json([
                            'message' => "Stok \"{$barang->nama}\" tidak cukup (sisa {$barang->stok}).",
                            'errors' => ['lines' => ["Stok {$barang->nama} tidak cukup."]],
                        ], 422));
                    }
                    $persen = (float) ($line['diskon_persen'] ?? 0);
                    $diskonNominal = (int) round((float) $barang->harga_jual * $line['qty'] * ($persen / 100));
                    $subtotal = (float) $barang->harga_jual * $line['qty'] - $diskonNominal;
                    $total += $subtotal;
                    $prepared[] = [$barang, $line, $persen, $diskonNominal, $subtotal];
                }

                $totalBayar = (float) $data['total_bayar'];

                // Tunai wajib lunas; kredit boleh kurang (jadi piutang).
                if ($data['jenis_transaksi'] === 'tunai' && $totalBayar < $total) {
                    abort(response()->json([
                        'message' => 'Pembayaran tunai kurang dari total faktur.',
                        'errors' => ['total_bayar' => ['Nominal kurang dari total.']],
                    ], 422));
                }

                $lunas = $data['jenis_transaksi'] === 'tunai' || $totalBayar >= $total;

                $header = TbPenjualan::create([
                    'id_sekolah' => $schoolId,
                    'id_user' => $user->id_user,
                    'id_pelanggan' => $idPelanggan,
                    'tanggal_penjualan' => now(),
                    'total_faktur' => $total,
                    'total_bayar' => $totalBayar,
                    'kembalian' => max(0, $totalBayar - $total),
                    'status_pembayaran' => $lunas ? 'sudah bayar' : 'belum bayar',
                    'jenis_transaksi' => $data['jenis_transaksi'],
                    'cara_bayar' => $data['cara_bayar'] ?? 'Tunai',
                    'note' => $data['note'] ?? null,
                    'created_at' => now(),
                    'created_by' => $user->id_user,
                    'is_delete' => 0,
                ]);

                foreach ($prepared as [$barang, $line, $persen, $diskonNominal, $subtotal]) {
                    $header->details()->create([
                        'id_barang' => $barang->id_barang,
                        'jumlah_barang' => $line['qty'],
                        'harga_beli' => $barang->harga_beli, // snapshot HPP
                        'harga_jual' => $barang->harga_jual, // snapshot harga
                        'diskon_tipe' => 'persen',
                        'diskon_nilai' => $persen,
                        'diskon_nominal' => $diskonNominal,
                        'subtotal' => $subtotal,
                    ]);
                    $barang->decrement('stok', $line['qty']);
                }

                return $header->load(['pelanggan', 'user:id_user,nama_lengkap', 'details.barang']);
            });
        } catch (HttpException $e) {
            throw $e;
        }

        return response()->json(['message' => 'Transaksi berhasil disimpan.', 'data' => $penjualan], 201);
    }

    /**
     * Void/batalkan penjualan: soft-delete + kembalikan stok (atomik).
     * Kasir hanya boleh membatalkan transaksi miliknya pada hari yang sama.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $penjualan = DB::transaction(function () use ($id, $user) {
            $header = TbPenjualan::aktif()->lockForUpdate()->findOrFail($id);
            Tenant::ensureOwnSchool($header, $user);

            if ($user->isKasir()) {
                $milikSendiri = (int) $header->id_user === (int) $user->id_user;
                $hariIni = $header->tanggal_penjualan?->isToday() ?? $header->created_at?->isToday() ?? false;
                if (! $milikSendiri || ! $hariIni) {
                    abort(response()->json(['message' => 'Kasir hanya boleh membatalkan transaksi miliknya pada hari yang sama.'], 403));
                }
            }

            $header->update(['is_delete' => 1, 'deleted_at' => now(), 'deleted_by' => $user->id_user]);

            foreach ($header->details as $d) {
                TbBarang::where('id_barang', $d->id_barang)->lockForUpdate()->first()?->increment('stok', $d->jumlah_barang);
            }

            return $header;
        });

        return response()->json(['message' => 'Transaksi dibatalkan. Stok dikembalikan.', 'data' => ['id_penjualan' => $penjualan->id_penjualan]]);
    }
}
