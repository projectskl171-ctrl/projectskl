# HANDOVER BACKEND — KasirKu POS (13 tabel)

Frontend sudah **fungsional penuh dengan dummy lokal**. Teman backend tinggal
mengganti sumber data tanpa mengubah komponen Vue.

## 1. File frontend yang perlu diketahui

| File | Isi |
|---|---|
| `resources/js/data/mockDb.ts` | Interface TS 1:1 kolom DB + seed dummy tiap tabel |
| `resources/js/composables/usePosStore.ts` | Store + semua action (CRUD). Tiap action = 1 endpoint |
| `resources/js/lib/format.ts` | `formatRupiah`, `formatDate`, `dayKey` |
| `resources/js/pages/*.vue` | Sudah `hydrate(props)` — otomatis pakai data asli jika dikirim |

## 2. Cara menyambungkan data asli (2 langkah, tanpa ubah Vue)

Setiap page memanggil `hydrate(props)` saat mount. Jika Inertia mengirim array
non-kosong, store langsung menimpa dummy + simpan ke localStorage.

```php
// contoh: kirim data asli, frontend otomatis pakai ini
Route::get('/produk', fn () => Inertia::render('Produk', [
    'title' => 'Produk',
    'barang' => Barang::where('is_delete', 0)->get(),   // -> hydrate ke store.barang
    'kategori' => Kategori::all(),                       // -> store.kategori
    'supplier' => Supplier::where('is_delete', 0)->get(),
]))->name('produk');
```

Key props yang dikenali `hydrate()`: `barang`, `kategori`, `supplier`,
`pelanggan`, `pembelian`, `penjualan`, `users`, `detailPenjualan`, `detailPembelian`.

> Reset dummy di browser: `localStorage.removeItem('kasirku_mock_v3')` lalu refresh.
> (v2: tambah `tb_sekolah` kedua + `saveSekolah`; key lama `kasirku_mock_v1` sudah tidak dipakai.)

## 3. Endpoint yang harus dibuat (mapping action frontend)

| Action frontend (`usePosStore`) | Method & URL saran | Catatan BE |
|---|---|---|
| `saveBarang` (create/update) | `POST /api/barang`, `PUT /api/barang/{id}` | validasi barcode unique per sekolah |
| `deleteBarang` | `DELETE /api/barang/{id}` | soft delete: `is_delete=1` |
| `savePelanggan` / `deletePelanggan` | `POST/PUT/DELETE /api/pelanggan` | sama, soft delete |
| `saveSupplier` / `deleteSupplier` | `POST/PUT/DELETE /api/supplier` | sama |
| `saveUser` / `toggleUser` | `POST/PUT /api/users`, `PATCH /api/users/{id}/toggle` | `password` → `Hash::make`, jangan kembalikan hash |
| `createPenjualan` | `POST /api/penjualan` | **WAJIB `DB::transaction`**: insert header + details[], hitung ulang `subtotal`, cek & kurangi `stok`, tolak jika kurang |
| `createPembelian` | `POST /api/pembelian` | simpan sebagai `draft` |
| `selesaikanPembelian` | `POST /api/pembelian/{id}/selesai` | `DB::transaction`: `status=selesai`, `stok += jumlah`, update `harga_beli` (HPP terakhir) |
| `deletePembelian` | `DELETE /api/pembelian/{id}` | hanya boleh saat `draft` |

Body `POST /api/penjualan` (sesuai yang dikirim frontend):
```json
{
  "id_pelanggan": 1,
  "cara_bayar": "QRIS",
  "jenis_transaksi": "tunai",
  "total_bayar": 50000,
  "lines": [{ "id_barang": 2, "qty": 3, "diskon_persen": 10 }]
}
```

## 4. Aturan bisnis (sudah diterapkan di dummy, tiru di BE)

1. **Diskon per baris**: `diskon_nominal = round(harga_jual × qty × persen/100)`, `subtotal = harga_jual × qty − diskon_nominal`.
2. **Stok**: penjualan mengurangi, penyelesaian pembelian menambah. Jangan pernah minus.
3. **Lunas**: `tunai` → `sudah bayar`; `kredit` → `belum bayar` kecuali `total_bayar >= total_faktur`.
4. **HPP laporan**: `Σ(harga_beli × jumlah)` dari detail saat transaksi (snapshot, bukan harga kini).
5. **Scope sekolah**: semua query filter `id_sekolah = auth()->user()->id_sekolah` (kecuali super admin).
6. **Audit**: isi `created_by/updated_by/deleted_at/deleted_by` dari user login.

## 5. Sistem peran (frontend demo → backend asli)

Frontend punya simulasi login di `composables/useAuthMock.ts` (localStorage
`kasirku_role_v1`). Matriks akses ada di `MATRIX` + field `roles` per item di
`DashboardSidebar.vue`. Aturan main (sesuai gambaran user):

- **kasir** → Dashboard (1 dagang: shift saya), Transaksi, Pelanggan (**CRUD penuh**),
  Notifikasi, Settings (profil + tampilan).
- **admin** → 1 sekolah: + Pembelian, Produk, Supplier, Laporan, User (**khusus kelola kasir**).
- **super admin** → semua sekolah: semua menu + ringkasan jaringan & tabel per-sekolah di Dashboard,
  kelola admin & kasir + panel kendali.

Halaman yang dikunci menampilkan `components/RoleDenied.vue` (tombol pindah
peran). Saat auth backend jadi:

- Hapus `useAuthMock`/`RoleDenied`, ganti dengan `usePage().props.auth.user`.
- Tambah middleware `role:kasir,admin,super admin` per route + 403.
- Filter `navGroups` di sidebar dari prop `auth`, bukan localStorage.
- `AuthPage.vue`: 3 tombol cepat (`quickLogin`) diganti `POST /login` asli.

## 6. Checklist sebelum go-live

- [ ] Ganti `web.php` closure dengan controller + middleware `auth`.
- [ ] Aktifkan kembali GET `/login` & `/register` Fortify (saat ini di-redirect ke `/`
      via `login.disabled`/`register.disabled` karena demo 1-pintu `AuthPage`).
- [ ] Tambah validasi FormRequest per tabel.
- [ ] Tambah pagination (frontend siap: `filtered` tinggal di-slice).
- [ ] Tambah middleware `role:kasir,admin,super admin` + scope `id_sekolah`
      (frontend sudah menyiapkan konsep: kasir = 1 dagang, admin = 1 sekolah,
      super admin = semua sekolah + filter scope di Dashboard).
- [ ] Hapus `resetMockDb`/localStorage jika tidak dipakai (opsional, tidak mengganggu).
