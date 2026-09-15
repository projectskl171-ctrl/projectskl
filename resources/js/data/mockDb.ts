/* =====================================================================
   MOCK DB — mirror 1:1 dari 13 tabel MySQL (lihat HANDOVER_BACKEND.md).
   Nanti tinggal ganti `seed*` dengan response API/Inertia dari Laravel,
   tanpa ubah shape field (snake_case dipertahankan).
   ===================================================================== */

export interface Sekolah {
    id_sekolah: number;
    kode_sekolah: string;
    nama_sekolah: string;
    alamat_sekolah: string;
    website: string;
    is_active: number;
    status_langganan?: 'aktif' | 'terdaftar' | 'menunggak';
    bulan_terakhir_bayar?: string;
    created_at: string;
}
export interface Role { id_role: number; nama_role: 'super admin' | 'admin' | 'kasir' }
export interface UserRow { id_user: number; id_sekolah: number; id_role: number; username: string; password: string; nama_lengkap: string; is_active: number; created_at: string; created_by: number | null }
export interface KelompokKategori { id_kelompok: number; id_sekolah: number; nama_kelompok: string; created_at: string; created_by: number }
export interface Kategori { id_kategori: number; id_kelompok: number; nama: string; created_at: string; created_by: number; is_delete: number }
export interface Supplier { id_supplier: number; id_sekolah: number; nama: string; no_telepon: string; alamat_supplier: string; created_at: string; created_by: number; is_delete: number }
export interface Barang { id_barang: number; id_sekolah: number; barcode: string; nama: string; id_kategori: number; id_kelompok_kategori: number; id_supplier: number; satuan: string; harga_beli: number; harga_jual: number; stok: number; is_active: number; created_at: string; created_by: number; is_delete: number }
export interface Pembelian { id_pembelian: number; id_sekolah: number; id_supplier: number; id_user: number; nomor_faktur: string; tanggal_faktur: string; total_bayar: number; status_pembelian: 'draft' | 'selesai'; jenis_transaksi: 'tunai' | 'kredit'; cara_bayar: string; note: string; created_at: string; created_by: number; is_delete: number }
export interface DetailPembelian { id_detail_pembelian: number; id_pembelian: number; id_barang: number; satuan: string; jumlah: number; harga_beli: number; subtotal: number }
export interface KelompokPelanggan { id_kelompok_pelanggan: number; id_sekolah: number; nama_kelompok: string }
export interface Pelanggan { id_pelanggan: number; id_kelompok_pelanggan: number; nama_pelanggan: string; telepon: string; alamat: string; created_at: string; created_by: number; is_delete: number }
export interface Penjualan { id_penjualan: number; id_sekolah: number; id_user: number; id_pelanggan: number | null; tanggal_penjualan: string; total_faktur: number; total_bayar: number; kembalian: number; status_pembayaran: 'sudah bayar' | 'belum bayar'; jenis_transaksi: 'tunai' | 'kredit'; cara_bayar: string; note: string; created_at: string; created_by: number; is_delete: number }
export interface DetailPenjualan { id_detail_penjualan: number; id_penjualan: number; id_barang: number; jumlah_barang: number; harga_beli: number; harga_jual: number; diskon_tipe: 'persen' | 'nominal'; diskon_nilai: number; diskon_nominal: number; subtotal: number }

const now = new Date();
const iso = (dayOffset: number, h = 9, m = 0): string => {
    const d = new Date(now);
    d.setDate(d.getDate() - dayOffset);
    d.setHours(h, m, 0, 0);
    return d.toISOString();
};
const daysAgo = (n: number): string => iso(n, 8, 30);

/* ---------------- 1. tb_sekolah ---------------- */
export const seedSekolah: Sekolah[] = [
    { id_sekolah: 1, kode_sekolah: 'SKL-001', nama_sekolah: 'SMK Nusantara 1', alamat_sekolah: 'Jl. Merdeka No. 45, Bandung', website: 'https://smknusantara1.sch.id', is_active: 1, status_langganan: 'aktif', bulan_terakhir_bayar: '2026-09-01', created_at: daysAgo(400) },
    { id_sekolah: 2, kode_sekolah: 'SKL-002', nama_sekolah: 'SMA Pelita Bangsa', alamat_sekolah: 'Jl. Asia Afrika No. 12, Bandung', website: 'https://smapelitabangsa.sch.id', is_active: 0, status_langganan: 'menunggak', bulan_terakhir_bayar: '2026-07-15', created_at: daysAgo(210) },
    { id_sekolah: 3, kode_sekolah: 'SKL-003', nama_sekolah: 'SMK Harapan Bangsa', alamat_sekolah: 'Jl. Soekarno Hatta No. 88, Bandung', website: 'https://smkharapanbangsa.sch.id', is_active: 1, status_langganan: 'aktif', bulan_terakhir_bayar: '2026-09-05', created_at: daysAgo(150) },
    { id_sekolah: 4, kode_sekolah: 'SKL-004', nama_sekolah: 'SMA Cahaya Ilmu', alamat_sekolah: 'Jl. Setiabudi No. 200, Bandung', website: 'https://smacahayailmu.sch.id', is_active: 0, status_langganan: 'terdaftar', bulan_terakhir_bayar: '2026-08-20', created_at: daysAgo(90) },
    { id_sekolah: 5, kode_sekolah: 'SKL-005', nama_sekolah: 'SMK Tunas Karya', alamat_sekolah: 'Jl. Kopo No. 55, Bandung', website: 'https://smktunaskarya.sch.id', is_active: 1, status_langganan: 'aktif', bulan_terakhir_bayar: '2026-09-10', created_at: daysAgo(30) },
];

/* ---------------- 2. roles ---------------- */
export const seedRoles: Role[] = [
    { id_role: 1, nama_role: 'super admin' },
    { id_role: 2, nama_role: 'admin' },
    { id_role: 3, nama_role: 'kasir' },
];

/* ---------------- 3. tb_user ---------------- */
export const seedUsers: UserRow[] = [
    { id_user: 1, id_sekolah: 1, id_role: 1, username: 'superadmin', password: '— hashed —', nama_lengkap: 'Admin Utama', is_active: 1, created_at: daysAgo(400), created_by: null },
    { id_user: 2, id_sekolah: 1, id_role: 2, username: 'admin.kantin', password: '— hashed —', nama_lengkap: 'Sari Puspita', is_active: 1, created_at: daysAgo(300), created_by: 1 },
    { id_user: 3, id_sekolah: 1, id_role: 3, username: 'kasir.01', password: '— hashed —', nama_lengkap: 'Dedi Kurniawan', is_active: 1, created_at: daysAgo(200), created_by: 1 },
    { id_user: 4, id_sekolah: 1, id_role: 3, username: 'kasir.02', password: '— hashed —', nama_lengkap: 'Rina Marlina', is_active: 1, created_at: daysAgo(120), created_by: 1 },
    { id_user: 5, id_sekolah: 1, id_role: 3, username: 'kasir.03', password: '— hashed —', nama_lengkap: 'Budi Santoso', is_active: 0, created_at: daysAgo(60), created_by: 1 },
    { id_user: 6, id_sekolah: 2, id_role: 2, username: 'admin.pelita', password: '— hashed —', nama_lengkap: 'Agus Wijaya', is_active: 1, created_at: daysAgo(200), created_by: 1 },
    { id_user: 7, id_sekolah: 2, id_role: 3, username: 'kasir.pelita', password: '— hashed —', nama_lengkap: 'Dewi Lestari', is_active: 1, created_at: daysAgo(150), created_by: 1 },
];

/* ---------------- 4. tb_kelompok_kategori ---------------- */
export const seedKelompok: KelompokKategori[] = [
    { id_kelompok: 1, id_sekolah: 1, nama_kelompok: 'Makanan', created_at: daysAgo(390), created_by: 1 },
    { id_kelompok: 2, id_sekolah: 1, nama_kelompok: 'Minuman', created_at: daysAgo(390), created_by: 1 },
    { id_kelompok: 3, id_sekolah: 1, nama_kelompok: 'ATK & Seragam', created_at: daysAgo(390), created_by: 1 },
];

/* ---------------- 5. tb_kategori ---------------- */
export const seedKategori: Kategori[] = [
    { id_kategori: 1, id_kelompok: 1, nama: 'Snack', created_at: daysAgo(390), created_by: 1, is_delete: 0 },
    { id_kategori: 2, id_kelompok: 1, nama: 'Makanan Berat', created_at: daysAgo(390), created_by: 1, is_delete: 0 },
    { id_kategori: 3, id_kelompok: 2, nama: 'Minuman Dingin', created_at: daysAgo(390), created_by: 1, is_delete: 0 },
    { id_kategori: 4, id_kelompok: 2, nama: 'Minuman Hangat', created_at: daysAgo(390), created_by: 1, is_delete: 0 },
    { id_kategori: 5, id_kelompok: 3, nama: 'Alat Tulis', created_at: daysAgo(390), created_by: 1, is_delete: 0 },
    { id_kategori: 6, id_kelompok: 3, nama: 'Seragam', created_at: daysAgo(390), created_by: 1, is_delete: 0 },
];

/* ---------------- 6. tb_supplier ---------------- */
export const seedSupplier: Supplier[] = [
    { id_supplier: 1, id_sekolah: 1, nama: 'PT Sumber Makmur', no_telepon: '0812-2200-1100', alamat_supplier: 'Jl. Cibaduyut No. 88, Bandung', created_at: daysAgo(380), created_by: 1, is_delete: 0 },
    { id_supplier: 2, id_sekolah: 1, nama: 'CV Berkah Jaya', no_telepon: '0813-3344-5566', alamat_supplier: 'Jl. Kopo No. 12, Bandung', created_at: daysAgo(350), created_by: 1, is_delete: 0 },
    { id_supplier: 3, id_sekolah: 1, nama: 'Toko ATK Sentosa', no_telepon: '022-5208899', alamat_supplier: 'Jl. ABC No. 5, Bandung', created_at: daysAgo(300), created_by: 1, is_delete: 0 },
    { id_supplier: 4, id_sekolah: 1, nama: 'Aqua Galon Dago', no_telepon: '0815-7788-9900', alamat_supplier: 'Jl. Dago No. 200, Bandung', created_at: daysAgo(200), created_by: 2, is_delete: 0 },
];

/* ---------------- 7. tb_barang ---------------- */
export const seedBarang: Barang[] = [
    { id_barang: 1, id_sekolah: 1, barcode: '8991001000011', nama: 'Chitato Sapi Panggang 68g', id_kategori: 1, id_kelompok_kategori: 1, id_supplier: 1, satuan: 'pcs', harga_beli: 8500, harga_jual: 11000, stok: 120, is_active: 1, created_at: daysAgo(300), created_by: 1, is_delete: 0 },
    { id_barang: 2, id_sekolah: 1, barcode: '8991001000028', nama: 'Indomie Goreng', id_kategori: 2, id_kelompok_kategori: 1, id_supplier: 1, satuan: 'pcs', harga_beli: 2800, harga_jual: 4000, stok: 250, is_active: 1, created_at: daysAgo(300), created_by: 1, is_delete: 0 },
    { id_barang: 3, id_sekolah: 1, barcode: '8991001000035', nama: 'Nasi Ayam Geprek', id_kategori: 2, id_kelompok_kategori: 1, id_supplier: 1, satuan: 'porsi', harga_beli: 8000, harga_jual: 12000, stok: 40, is_active: 1, created_at: daysAgo(250), created_by: 2, is_delete: 0 },
    { id_barang: 4, id_sekolah: 1, barcode: '8991001000042', nama: 'Teh Botol Sosro 450ml', id_kategori: 3, id_kelompok_kategori: 2, id_supplier: 2, satuan: 'botol', harga_beli: 3200, harga_jual: 5000, stok: 180, is_active: 1, created_at: daysAgo(300), created_by: 1, is_delete: 0 },
    { id_barang: 5, id_sekolah: 1, barcode: '8991001000059', nama: 'Aqua 600ml', id_kategori: 3, id_kelompok_kategori: 2, id_supplier: 4, satuan: 'botol', harga_beli: 2500, harga_jual: 4000, stok: 300, is_active: 1, created_at: daysAgo(300), created_by: 1, is_delete: 0 },
    { id_barang: 6, id_sekolah: 1, barcode: '8991001000066', nama: 'Kopi Kapal Api Sachet', id_kategori: 4, id_kelompok_kategori: 2, id_supplier: 2, satuan: 'pcs', harga_beli: 1500, harga_jual: 3000, stok: 200, is_active: 1, created_at: daysAgo(280), created_by: 2, is_delete: 0 },
    { id_barang: 7, id_sekolah: 1, barcode: '8991001000073', nama: 'Pop Mie Ayam Bawang', id_kategori: 2, id_kelompok_kategori: 1, id_supplier: 1, satuan: 'cup', harga_beli: 7500, harga_jual: 10000, stok: 90, is_active: 1, created_at: daysAgo(250), created_by: 2, is_delete: 0 },
    { id_barang: 8, id_sekolah: 1, barcode: '8991001000080', nama: 'Buku Tulis Sinar Dunia 38 lbr', id_kategori: 5, id_kelompok_kategori: 3, id_supplier: 3, satuan: 'pcs', harga_beli: 4500, harga_jual: 6500, stok: 150, is_active: 1, created_at: daysAgo(280), created_by: 1, is_delete: 0 },
    { id_barang: 9, id_sekolah: 1, barcode: '8991001000097', nama: 'Pulpen Pilot Hitam', id_kategori: 5, id_kelompok_kategori: 3, id_supplier: 3, satuan: 'pcs', harga_beli: 2500, harga_jual: 4000, stok: 8, is_active: 1, created_at: daysAgo(280), created_by: 1, is_delete: 0 },
    { id_barang: 10, id_sekolah: 1, barcode: '8991001000103', nama: 'Kemeja Seragam Putih (S)', id_kategori: 6, id_kelompok_kategori: 3, id_supplier: 3, satuan: 'pcs', harga_beli: 55000, harga_jual: 75000, stok: 25, is_active: 1, created_at: daysAgo(200), created_by: 1, is_delete: 0 },
    { id_barang: 11, id_sekolah: 1, barcode: '8991001000110', nama: 'Roti Aoka Cokelat', id_kategori: 1, id_kelompok_kategori: 1, id_supplier: 1, satuan: 'pcs', harga_beli: 2200, harga_jual: 3500, stok: 5, is_active: 1, created_at: daysAgo(150), created_by: 2, is_delete: 0 },
    { id_barang: 12, id_sekolah: 1, barcode: '8991001000127', nama: 'Susu Ultra Milk 250ml', id_kategori: 3, id_kelompok_kategori: 2, id_supplier: 2, satuan: 'kotak', harga_beli: 5000, harga_jual: 7000, stok: 110, is_active: 1, created_at: daysAgo(150), created_by: 2, is_delete: 0 },
    { id_barang: 13, id_sekolah: 1, barcode: '8991001000134', nama: 'Pensil 2B Faber Castell', id_kategori: 5, id_kelompok_kategori: 3, id_supplier: 3, satuan: 'pcs', harga_beli: 3000, harga_jual: 5000, stok: 0, is_active: 1, created_at: daysAgo(150), created_by: 2, is_delete: 0 },
    { id_barang: 14, id_sekolah: 1, barcode: '8991001000141', nama: 'Es Teh Manis Jumbo', id_kategori: 3, id_kelompok_kategori: 2, id_supplier: 2, satuan: 'cup', harga_beli: 2000, harga_jual: 5000, stok: 60, is_active: 0, created_at: daysAgo(100), created_by: 2, is_delete: 0 },
];

/* ---------------- 10. tb_kelompok_pelanggan ---------------- */
export const seedKelompokPelanggan: KelompokPelanggan[] = [
    { id_kelompok_pelanggan: 1, id_sekolah: 1, nama_kelompok: 'Siswa' },
    { id_kelompok_pelanggan: 2, id_sekolah: 1, nama_kelompok: 'Guru & Staff' },
    { id_kelompok_pelanggan: 3, id_sekolah: 1, nama_kelompok: 'Umum' },
];

/* ---------------- 11. tb_pelanggan ---------------- */
export const seedPelanggan: Pelanggan[] = [
    { id_pelanggan: 1, id_kelompok_pelanggan: 1, nama_pelanggan: 'Andi Pratama (X-RPL-1)', telepon: '0812-1111-2222', alamat: 'Jl. Sukajadi No. 10', created_at: daysAgo(200), created_by: 1, is_delete: 0 },
    { id_pelanggan: 2, id_kelompok_pelanggan: 1, nama_pelanggan: 'Sinta Dewi (XI-TKJ-2)', telepon: '0812-3333-4444', alamat: 'Jl. Cihanjuang No. 5', created_at: daysAgo(180), created_by: 1, is_delete: 0 },
    { id_pelanggan: 3, id_kelompok_pelanggan: 2, nama_pelanggan: 'Pak Hidayat (Guru)', telepon: '0813-5555-6666', alamat: 'Komplek Guru Blok A', created_at: daysAgo(170), created_by: 1, is_delete: 0 },
    { id_pelanggan: 4, id_kelompok_pelanggan: 2, nama_pelanggan: 'Bu Ratna (TU)', telepon: '0813-7777-8888', alamat: 'Komplek Guru Blok B', created_at: daysAgo(160), created_by: 2, is_delete: 0 },
    { id_pelanggan: 5, id_kelompok_pelanggan: 3, nama_pelanggan: 'Budi (Warung Sebelah)', telepon: '0821-9999-0000', alamat: 'Jl. Merdeka No. 50', created_at: daysAgo(100), created_by: 2, is_delete: 0 },
    { id_pelanggan: 6, id_kelompok_pelanggan: 1, nama_pelanggan: 'Rizky Ramadhan (XII-MM-1)', telepon: '0812-1212-3434', alamat: 'Jl. Setiabudi No. 77', created_at: daysAgo(40), created_by: 3, is_delete: 0 },
    { id_pelanggan: 7, id_kelompok_pelanggan: 3, nama_pelanggan: 'Koperasi Unit 2', telepon: '022-2011122', alamat: 'Gedung B Lantai 1', created_at: daysAgo(20), created_by: 2, is_delete: 0 },
    { id_pelanggan: 8, id_kelompok_pelanggan: 1, nama_pelanggan: 'Nadia Putri (X-AK-3)', telepon: '0819-5656-7878', alamat: 'Jl. Gegerkalong No. 3', created_at: daysAgo(10), created_by: 3, is_delete: 1 },
];

/* ---------------- 8+9. tb_pembelian + detail ---------------- */
export const seedPembelian: Pembelian[] = [
    { id_pembelian: 1, id_sekolah: 1, id_supplier: 1, id_user: 2, nomor_faktur: 'PO-2026-0001', tanggal_faktur: iso(6, 10), total_bayar: 1250000, status_pembelian: 'selesai', jenis_transaksi: 'tunai', cara_bayar: 'Transfer', note: 'Restock snack + mie', created_at: iso(6, 10), created_by: 2, is_delete: 0 },
    { id_pembelian: 2, id_sekolah: 1, id_supplier: 2, id_user: 2, nomor_faktur: 'PO-2026-0002', tanggal_faktur: iso(4, 11), total_bayar: 840000, status_pembelian: 'selesai', jenis_transaksi: 'tunai', cara_bayar: 'Tunai', note: 'Minuman dingin', created_at: iso(4, 11), created_by: 2, is_delete: 0 },
    { id_pembelian: 3, id_sekolah: 1, id_supplier: 3, id_user: 2, nomor_faktur: 'PO-2026-0003', tanggal_faktur: iso(2, 9), total_bayar: 975000, status_pembelian: 'selesai', jenis_transaksi: 'kredit', cara_bayar: 'Tempo 14 hari', note: 'ATK semester genap', created_at: iso(2, 9), created_by: 2, is_delete: 0 },
    { id_pembelian: 4, id_sekolah: 1, id_supplier: 1, id_user: 2, nomor_faktur: 'PO-2026-0004', tanggal_faktur: iso(0, 8), total_bayar: 640000, status_pembelian: 'draft', jenis_transaksi: 'tunai', cara_bayar: 'Transfer', note: 'Draft — menunggu approve', created_at: iso(0, 8), created_by: 2, is_delete: 0 },
];

export const seedDetailPembelian: DetailPembelian[] = [
    { id_detail_pembelian: 1, id_pembelian: 1, id_barang: 1, satuan: 'pcs', jumlah: 60, harga_beli: 8500, subtotal: 510000 },
    { id_detail_pembelian: 2, id_pembelian: 1, id_barang: 2, satuan: 'pcs', jumlah: 200, harga_beli: 2800, subtotal: 560000 },
    { id_detail_pembelian: 3, id_pembelian: 1, id_barang: 7, satuan: 'cup', jumlah: 24, harga_beli: 7500, subtotal: 180000 },
    { id_detail_pembelian: 4, id_pembelian: 2, id_barang: 4, satuan: 'botol', jumlah: 120, harga_beli: 3200, subtotal: 384000 },
    { id_detail_pembelian: 5, id_pembelian: 2, id_barang: 12, satuan: 'kotak', jumlah: 60, harga_beli: 5000, subtotal: 300000 },
    { id_detail_pembelian: 6, id_pembelian: 2, id_barang: 6, satuan: 'pcs', jumlah: 104, harga_beli: 1500, subtotal: 156000 },
    { id_detail_pembelian: 7, id_pembelian: 3, id_barang: 8, satuan: 'pcs', jumlah: 100, harga_beli: 4500, subtotal: 450000 },
    { id_detail_pembelian: 8, id_pembelian: 3, id_barang: 9, satuan: 'pcs', jumlah: 120, harga_beli: 2500, subtotal: 300000 },
    { id_detail_pembelian: 9, id_pembelian: 3, id_barang: 13, satuan: 'pcs', jumlah: 75, harga_beli: 3000, subtotal: 225000 },
    { id_detail_pembelian: 10, id_pembelian: 4, id_barang: 11, satuan: 'pcs', jumlah: 200, harga_beli: 2200, subtotal: 440000 },
    { id_detail_pembelian: 11, id_pembelian: 4, id_barang: 3, satuan: 'porsi', jumlah: 25, harga_beli: 8000, subtotal: 200000 },
];

/* ---------------- 12+13. tb_penjualan + detail (7 hari, utk dashboard & laporan) ---------------- */
type JualSeed = [dayOffset: number, h: number, m: number, id_user: number, id_pelanggan: number | null, cara: string, jenis: 'tunai' | 'kredit', status: 'sudah bayar' | 'belum bayar', items: [id_barang: number, qty: number, discPct: number][]];
const JUAL: JualSeed[] = [
    [6, 9, 12, 3, 1, 'Tunai', 'tunai', 'sudah bayar', [[2, 3, 0], [4, 2, 0]]],
    [6, 12, 5, 3, 3, 'QRIS', 'tunai', 'sudah bayar', [[3, 2, 0], [5, 2, 0]]],
    [5, 10, 20, 4, 2, 'Tunai', 'tunai', 'sudah bayar', [[1, 4, 10], [4, 4, 0]]],
    [5, 15, 40, 3, null, 'Tunai', 'tunai', 'sudah bayar', [[7, 2, 0], [12, 2, 0]]],
    [4, 9, 5, 3, 6, 'QRIS', 'tunai', 'sudah bayar', [[2, 5, 0], [11, 6, 0]]],
    [4, 13, 30, 4, 4, 'Transfer', 'tunai', 'sudah bayar', [[10, 2, 5]]],
    [3, 10, 11, 3, 1, 'Tunai', 'tunai', 'sudah bayar', [[3, 3, 0], [4, 3, 0], [1, 2, 0]]],
    [3, 16, 2, 4, 5, 'Tunai', 'tunai', 'sudah bayar', [[8, 10, 0], [9, 10, 0]]],
    [2, 9, 44, 3, 2, 'QRIS', 'tunai', 'sudah bayar', [[12, 6, 0], [11, 10, 0]]],
    [2, 14, 15, 3, 7, 'Transfer', 'kredit', 'belum bayar', [[8, 50, 5], [9, 40, 5]]],
    [1, 10, 9, 4, 6, 'Tunai', 'tunai', 'sudah bayar', [[2, 4, 0], [5, 4, 0], [1, 3, 0]]],
    [1, 12, 50, 3, 3, 'QRIS', 'tunai', 'sudah bayar', [[3, 5, 0], [12, 5, 10]]],
    [0, 8, 30, 3, null, 'Tunai', 'tunai', 'sudah bayar', [[2, 2, 0], [4, 2, 0]]],
    [0, 9, 45, 3, 1, 'QRIS', 'tunai', 'sudah bayar', [[7, 3, 0], [5, 3, 0], [1, 2, 5]]],
    [0, 11, 20, 4, 2, 'Tunai', 'tunai', 'sudah bayar', [[3, 4, 0], [12, 4, 0]]],
    [0, 13, 5, 3, 5, 'Transfer', 'kredit', 'belum bayar', [[10, 3, 0]]],
];

export const seedPenjualan: Penjualan[] = [];
export const seedDetailPenjualan: DetailPenjualan[] = [];

{
    let pid = 1, did = 1;
    for (const [off, h, m, idu, idp, cara, jenis, status, items] of JUAL) {
        let total = 0;
        const tgl = iso(off, h, m);
        const det: DetailPenjualan[] = [];
        for (const [idb, qty, discPct] of items) {
            const b = seedBarang.find((x) => x.id_barang === idb)!;
            const discNom = Math.round(b.harga_jual * qty * (discPct / 100));
            const sub = b.harga_jual * qty - discNom;
            total += sub;
            det.push({
                id_detail_penjualan: did++, id_penjualan: pid, id_barang: idb,
                jumlah_barang: qty, harga_beli: b.harga_beli, harga_jual: b.harga_jual,
                diskon_tipe: 'persen', diskon_nilai: discPct, diskon_nominal: discNom, subtotal: sub,
            });
        }
        const lunas = status === 'sudah bayar';
        seedPenjualan.push({
            id_penjualan: pid, id_sekolah: 1, id_user: idu, id_pelanggan: idp,
            tanggal_penjualan: tgl, total_faktur: total,
            total_bayar: lunas ? (cara === 'Tunai' ? Math.ceil(total / 5000) * 5000 : total) : 0,
            kembalian: lunas ? Math.max(0, (cara === 'Tunai' ? Math.ceil(total / 5000) * 5000 : total) - total) : 0,
            status_pembayaran: status, jenis_transaksi: jenis, cara_bayar: cara,
            note: '', created_at: tgl, created_by: idu, is_delete: 0,
        });
        seedDetailPenjualan.push(...det);
        pid++;
    }
}
