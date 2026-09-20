<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PembelianRequest;
use App\Models\TbBarang;
use App\Models\TbPembelian;
use App\Models\TbSupplier;
use App\Support\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PembelianController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $q = TbPembelian::query()->aktif()
            ->with(['supplier:id_supplier,nama', 'user:id_user,nama_lengkap'])
            ->withSum(['details as total_item' => fn ($qq) => $qq], 'jumlah');
        Tenant::scopeSchool($q, $request->user());

        if ($request->filled('status_pembelian')) {
            $q->where('status_pembelian', $request->query('status_pembelian'));
        }
        if ($s = $request->query('search')) {
            $q->where('nomor_faktur', 'like', "%{$s}%");
        }
        $q->orderByDesc('id_pembelian');

        return response()->json($q->paginate((int) $request->query('per_page', 15)));
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $pembelian = TbPembelian::aktif()
            ->with(['supplier', 'user:id_user,nama_lengkap,username', 'details.barang:id_barang,nama,barcode,satuan'])
            ->findOrFail($id);
        Tenant::ensureOwnSchool($pembelian, $request->user());

        return response()->json(['data' => $pembelian]);
    }

    /** Buat pembelian baru — selalu sebagai DRAFT (tidak menyentuh stok). */
    public function store(PembelianRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();
        $schoolId = Tenant::resolveSchoolId(null, $user);

        $supplier = TbSupplier::aktif()->findOrFail($data['id_supplier']);
        Tenant::ensureOwnSchool($supplier, $user);

        // Validasi semua barang: milik sekolah, aktif, belum dihapus.
        $barangMap = $this->resolveBarang($data['lines'], $schoolId);

        $pembelian = DB::transaction(function () use ($data, $user, $schoolId, $barangMap) {
            $total = 0;
            foreach ($data['lines'] as $line) {
                $total += $line['jumlah'] * $line['harga_beli'];
            }

            $header = TbPembelian::create([
                'id_sekolah' => $schoolId,
                'id_supplier' => $data['id_supplier'],
                'id_user' => $user->id_user,
                'nomor_faktur' => $data['nomor_faktur'] ?? $this->nomorFaktur(),
                'tanggal_faktur' => $data['tanggal_faktur'] ?? now(),
                'total_bayar' => $total,
                'status_pembelian' => 'draft',
                'jenis_transaksi' => $data['jenis_transaksi'],
                'cara_bayar' => $data['cara_bayar'] ?? null,
                'note' => $data['note'] ?? null,
                'created_at' => now(),
                'created_by' => $user->id_user,
                'is_delete' => 0,
            ]);

            foreach ($data['lines'] as $line) {
                $barang = $barangMap[$line['id_barang']];
                $header->details()->create([
                    'id_barang' => $barang->id_barang,
                    'satuan' => $barang->satuan,
                    'jumlah' => $line['jumlah'],
                    'harga_beli' => $line['harga_beli'],
                    'subtotal' => $line['jumlah'] * $line['harga_beli'],
                ]);
            }

            return $header->load(['supplier', 'details.barang']);
        });

        return response()->json(['message' => 'Pembelian draft berhasil dibuat.', 'data' => $pembelian], 201);
    }

    /**
     * Draft -> selesai: tambah stok + update HPP terakhir.
     * Idempoten: pembelian yang sudah selesai ditolak (mencegah stok ganda).
     */
    public function selesaikan(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $pembelian = DB::transaction(function () use ($id, $user) {
            $header = TbPembelian::aktif()->lockForUpdate()->findOrFail($id);
            Tenant::ensureOwnSchool($header, $user);

            if ($header->status_pembelian === 'selesai') {
                abort(response()->json(['message' => 'Pembelian sudah selesai. Stok tidak ditambah ulang.'], 422));
            }

            $header->update(['status_pembelian' => 'selesai']);

            $details = $header->details()->orderBy('id_detail_pembelian')->get();
            foreach ($details as $d) {
                $barang = TbBarang::aktif()->lockForUpdate()->findOrFail($d->id_barang);
                Tenant::ensureOwnSchool($barang, $user);
                $barang->increment('stok', $d->jumlah);
                $barang->update(['harga_beli' => $d->harga_beli]); // HPP terakhir
            }

            return $header->fresh(['supplier', 'details.barang']);
        });

        return response()->json(['message' => 'Pembelian diselesaikan. Stok bertambah.', 'data' => $pembelian]);
    }

    /** Hapus pembelian — hanya boleh saat masih draft. */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $pembelian = TbPembelian::aktif()->findOrFail($id);
        Tenant::ensureOwnSchool($pembelian, $user);

        if ($pembelian->status_pembelian === 'selesai') {
            return response()->json(['message' => 'Pembelian yang sudah selesai tidak dapat dihapus.'], 422);
        }

        $pembelian->update(['is_delete' => 1, 'deleted_at' => now(), 'deleted_by' => $user->id_user]);

        return response()->json(['message' => 'Draft pembelian berhasil dihapus.']);
    }

    /** @return array<int, TbBarang> */
    protected function resolveBarang(array $lines, int $schoolId): array
    {
        $ids = collect($lines)->pluck('id_barang')->unique()->values();
        $barangs = TbBarang::aktif()
            ->whereIn('id_barang', $ids)
            ->where('id_sekolah', $schoolId)
            ->where('is_active', 1)
            ->get()->keyBy('id_barang');

        foreach ($ids as $id) {
            if (! isset($barangs[$id])) {
                abort(response()->json([
                    'message' => 'Terdapat barang yang tidak valid untuk sekolah anda.',
                    'errors' => ['lines' => ["Barang #{$id} tidak valid."]],
                ], 422));
            }
        }

        return $barangs->all();
    }

    protected function nomorFaktur(): string
    {
        $next = (int) (TbPembelian::max('id_pembelian') ?? 0) + 1;

        return 'PO-'.date('Y').'-'.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}
