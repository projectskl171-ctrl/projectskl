/* =====================================================================
   POS STORE — single source of truth frontend (dummy, localStorage).
   BACKEND HANDOVER: setiap action di sini = 1 endpoint Laravel.
     barang    -> GET/POST /api/barang, PUT/DELETE /api/barang/{id}
     pelanggan -> GET/POST /api/pelanggan ...
     penjualan -> POST /api/penjualan (kirim header + details[], BE
                  hitung ulang subtotal & kurangi stok di transaction)
     pembelian -> POST /api/pembelian, POST /api/pembelian/{id}/selesai
   Kalau Inertia props datang (BE sudah jadi), panggil hydrate(props).
   ===================================================================== */
import { computed, reactive } from 'vue';
import {
    seedBarang, seedDetailPembelian, seedDetailPenjualan, seedKategori,
    seedKelompok, seedKelompokPelanggan, seedPelanggan, seedPembelian,
    seedPenjualan, seedRoles, seedSekolah, seedSupplier, seedUsers,
    type Barang, type Kategori, type Pelanggan, type Pembelian,
    type DetailPembelian, type Penjualan, type DetailPenjualan,
    type Supplier, type UserRow,
} from '@/data/mockDb';
import { dayKey } from '@/lib/format';

const LS_KEY = 'kasirku_mock_v3';

interface State {
    barang: Barang[];
    kategori: Kategori[];
    kelompok: typeof seedKelompok;
    supplier: Supplier[];
    pelanggan: Pelanggan[];
    kelompokPelanggan: typeof seedKelompokPelanggan;
    pembelian: Pembelian[];
    detailPembelian: DetailPembelian[];
    penjualan: Penjualan[];
    detailPenjualan: DetailPenjualan[];
    users: UserRow[];
    sekolah: typeof seedSekolah;
    loaded: boolean;
}

const clone = <T>(v: T): T => JSON.parse(JSON.stringify(v));

const freshState = (): State => ({
    barang: clone(seedBarang),
    kategori: clone(seedKategori),
    kelompok: clone(seedKelompok),
    supplier: clone(seedSupplier),
    pelanggan: clone(seedPelanggan),
    kelompokPelanggan: clone(seedKelompokPelanggan),
    pembelian: clone(seedPembelian),
    detailPembelian: clone(seedDetailPembelian),
    penjualan: clone(seedPenjualan),
    detailPenjualan: clone(seedDetailPenjualan),
    users: clone(seedUsers),
    sekolah: clone(seedSekolah),
    loaded: false,
});

const state = reactive<State>(freshState());

/* ---------- persistence ---------- */
function persist() {
    try {
        const { loaded, ...rest } = state;
        localStorage.setItem(LS_KEY, JSON.stringify(rest));
    } catch { /* abaikan */ }
}
function load() {
    if (state.loaded) return;
    state.loaded = true;
    try {
        const raw = localStorage.getItem(LS_KEY);
        if (!raw) return;
        const d = JSON.parse(raw);
        for (const k of Object.keys(d)) {
            if (k in state && Array.isArray(d[k])) (state as any)[k] = d[k];
        }
    } catch { /* seed default */ }
}
export function resetMockDb() {
    const f = freshState();
    for (const k of Object.keys(f)) {
        if (k !== 'loaded') (state as any)[k] = (f as any)[k];
    }
    persist();
}

/** Dipanggil saat backend sudah mengirim props Inertia asli. */
export function hydrate(props: Partial<Record<string, any>>) {
    const map: Record<string, keyof State> = {
        barang: 'barang', kategori: 'kategori', supplier: 'supplier',
        pelanggan: 'pelanggan', pembelian: 'pembelian', penjualan: 'penjualan',
        users: 'users', detailPenjualan: 'detailPenjualan', detailPembelian: 'detailPembelian',
    };
    let touched = false;
    for (const [pk, sk] of Object.entries(map)) {
        if (Array.isArray(props[pk]) && props[pk].length) {
            (state as any)[sk] = props[pk];
            touched = true;
        }
    }
    if (touched) persist();
}

const nextId = (rows: { [k: string]: any }[], key: string): number =>
    rows.reduce((m, r) => Math.max(m, r[key] || 0), 0) + 1;

export function usePosStore() {
    load();

    /* ================= LOOKUPS ================= */
    const barangAktif = computed(() => state.barang.filter((b) => !b.is_delete));
    const pelangganAktif = computed(() => state.pelanggan.filter((p) => !p.is_delete));
    const supplierAktif = computed(() => state.supplier.filter((s) => !s.is_delete));
    const usersAktif = computed(() => state.users);
    const penjualanAktif = computed(() => state.penjualan.filter((p) => !p.is_delete));
    const pembelianAktif = computed(() => state.pembelian.filter((p) => !p.is_delete));
    const sekolahAktif = computed(() => state.sekolah.filter((s) => s.is_active));

    const namaBarang = (id: number) => state.barang.find((b) => b.id_barang === id)?.nama ?? `#${id}`;
    const namaPelanggan = (id: number | null) => {
        if (id == null) return 'Umum / Walk-in';
        return state.pelanggan.find((p) => p.id_pelanggan === id)?.nama_pelanggan ?? '—';
    };
    const namaSupplier = (id: number) => state.supplier.find((s) => s.id_supplier === id)?.nama ?? '—';
    const namaUser = (id: number) => state.users.find((u) => u.id_user === id)?.nama_lengkap ?? '—';
    const namaRole = (id_role: number) => seedRoles.find((r) => r.id_role === id_role)?.nama_role ?? '—';
    const namaKategori = (id: number) => state.kategori.find((k) => k.id_kategori === id)?.nama ?? '—';
    const namaKelompok = (id: number) => state.kelompok.find((k) => k.id_kelompok === id)?.nama_kelompok ?? '—';
    const namaKelompokPelanggan = (id: number) =>
        state.kelompokPelanggan.find((k) => k.id_kelompok_pelanggan === id)?.nama_kelompok ?? '—';
    const namaSekolah = (id?: number) => {
        if (id == null) return state.sekolah[0]?.nama_sekolah ?? 'KasirKu';
        return state.sekolah.find((s) => s.id_sekolah === id)?.nama_sekolah ?? '—';
    };
    const statusSekolah = (id?: number) => {
        const sekolah = id == null ? state.sekolah[0] : state.sekolah.find((s) => s.id_sekolah === id);
        if (!sekolah) return 'tidak diketahui';
        return sekolah.status_langganan ?? (sekolah.is_active ? 'aktif' : 'terdaftar');
    };
    const sekolahBelumBayar = computed(() =>
        state.sekolah.filter((s) => s.status_langganan === 'menunggak' || (!s.is_active && s.status_langganan === 'terdaftar')),
    );
    const detailJual = (id_penjualan: number) => state.detailPenjualan.filter((d) => d.id_penjualan === id_penjualan);
    const detailBeli = (id_pembelian: number) => state.detailPembelian.filter((d) => d.id_pembelian === id_pembelian);

    /* ================= DASHBOARD AGREGAT ================= */
    const today = dayKey(new Date());
    const jualHariIni = computed(() => penjualanAktif.value.filter((p) => dayKey(p.tanggal_penjualan) === today));
    const omzetHariIni = computed(() => jualHariIni.value.reduce((s, p) => s + p.total_faktur, 0));
    const stokMenipis = computed(() => barangAktif.value.filter((b) => b.stok <= 10));
    const piutang = computed(() => penjualanAktif.value.filter((p) => p.status_pembayaran === 'belum bayar'));

    const omzet7Hari = computed(() => {
        const out: { label: string; key: string; total: number }[] = [];
        for (let i = 6; i >= 0; i--) {
            const d = new Date();
            d.setDate(d.getDate() - i);
            const key = dayKey(d);
            const label = d.toLocaleDateString('id-ID', { weekday: 'short' });
            const total = penjualanAktif.value
                .filter((p) => dayKey(p.tanggal_penjualan) === key)
                .reduce((s, p) => s + p.total_faktur, 0);
            out.push({ label, key, total });
        }
        return out;
    });

    /* ================= SEKOLAH (tb_sekolah, demo mock) ================= */
    function saveSekolah(payload: Partial<typeof seedSekolah[number]> & { id_sekolah: number }) {
        const i = state.sekolah.findIndex((s) => s.id_sekolah === payload.id_sekolah);
        if (i >= 0) state.sekolah[i] = { ...state.sekolah[i], ...payload };
        persist();
    }
    /** Aktif/nonaktif sekolah (super admin). Minimal 1 sekolah harus tetap aktif. */
    function toggleSekolah(id: number): boolean {
        const s = state.sekolah.find((x) => x.id_sekolah === id);
        if (!s) return false;
        if (s.is_active && state.sekolah.filter((x) => x.is_active).length <= 1) return false;
        s.is_active = s.is_active ? 0 : 1;
        persist();
        return true;
    }

    /* ================= BARANG (tb_barang) ================= */
    function saveBarang(payload: Partial<Barang> & { id_barang?: number }) {
        if (payload.id_barang) {
            const i = state.barang.findIndex((b) => b.id_barang === payload.id_barang);
            if (i >= 0) state.barang[i] = { ...state.barang[i], ...payload } as Barang;
        } else {
            state.barang.unshift({
                id_barang: nextId(state.barang, 'id_barang'),
                id_sekolah: 1, barcode: '', nama: '', id_kategori: state.kategori[0]?.id_kategori ?? 1,
                id_kelompok_kategori: state.kelompok[0]?.id_kelompok ?? 1,
                id_supplier: state.supplier[0]?.id_supplier ?? 1,
                satuan: 'pcs', harga_beli: 0, harga_jual: 0, stok: 0, is_active: 1,
                created_at: new Date().toISOString(), created_by: 1, is_delete: 0,
                ...payload,
            } as Barang);
        }
        persist();
    }
    function deleteBarang(id: number) {
        const b = state.barang.find((x) => x.id_barang === id);
        if (b) b.is_delete = 1;
        persist();
    }

    /* ================= PELANGGAN ================= */
    function savePelanggan(payload: Partial<Pelanggan> & { id_pelanggan?: number }) {
        if (payload.id_pelanggan) {
            const i = state.pelanggan.findIndex((p) => p.id_pelanggan === payload.id_pelanggan);
            if (i >= 0) state.pelanggan[i] = { ...state.pelanggan[i], ...payload } as Pelanggan;
        } else {
            state.pelanggan.unshift({
                id_pelanggan: nextId(state.pelanggan, 'id_pelanggan'),
                id_kelompok_pelanggan: 3, nama_pelanggan: '', telepon: '', alamat: '',
                created_at: new Date().toISOString(), created_by: 1, is_delete: 0, ...payload,
            } as Pelanggan);
        }
        persist();
    }
    function deletePelanggan(id: number) {
        const p = state.pelanggan.find((x) => x.id_pelanggan === id);
        if (p) p.is_delete = 1;
        persist();
    }

    /* ================= SUPPLIER ================= */
    function saveSupplier(payload: Partial<Supplier> & { id_supplier?: number }) {
        if (payload.id_supplier) {
            const i = state.supplier.findIndex((s) => s.id_supplier === payload.id_supplier);
            if (i >= 0) state.supplier[i] = { ...state.supplier[i], ...payload } as Supplier;
        } else {
            state.supplier.unshift({
                id_supplier: nextId(state.supplier, 'id_supplier'), id_sekolah: 1,
                nama: '', no_telepon: '', alamat_supplier: '',
                created_at: new Date().toISOString(), created_by: 1, is_delete: 0, ...payload,
            } as Supplier);
        }
        persist();
    }
    function deleteSupplier(id: number) {
        const s = state.supplier.find((x) => x.id_supplier === id);
        if (s) s.is_delete = 1;
        persist();
    }

    /* ================= USER ================= */
    function saveUser(payload: Partial<UserRow> & { id_user?: number }) {
        if (payload.id_user) {
            const i = state.users.findIndex((u) => u.id_user === payload.id_user);
            if (i >= 0) state.users[i] = { ...state.users[i], ...payload } as UserRow;
        } else {
            state.users.unshift({
                id_user: nextId(state.users, 'id_user'), id_sekolah: 1, id_role: 3,
                username: '', password: '— hashed —', nama_lengkap: '', is_active: 1,
                created_at: new Date().toISOString(), created_by: 1, ...payload,
            } as UserRow);
        }
        persist();
    }
    function toggleUser(id: number) {
        const u = state.users.find((x) => x.id_user === id);
        if (u) u.is_active = u.is_active ? 0 : 1;
        persist();
    }

    /* ================= PENJUALAN (kasir) ================= */
    interface CartLine { id_barang: number; qty: number; diskon_persen: number }
    function createPenjualan(opts: {
        id_user?: number; id_pelanggan?: number | null; cara_bayar: string;
        jenis_transaksi: 'tunai' | 'kredit'; total_bayar: number; note?: string; lines: CartLine[];
    }) {
        const id = nextId(state.penjualan, 'id_penjualan');
        let total = 0;
        const dets: DetailPenjualan[] = [];
        let did = nextId(state.detailPenjualan, 'id_detail_penjualan');
        for (const l of opts.lines) {
            const b = state.barang.find((x) => x.id_barang === l.id_barang);
            if (!b || b.stok < l.qty) throw new Error(`Stok "${b?.nama ?? l.id_barang}" tidak cukup`);
            const discNom = Math.round(b.harga_jual * l.qty * ((l.diskon_persen || 0) / 100));
            const sub = b.harga_jual * l.qty - discNom;
            total += sub;
            dets.push({
                id_detail_penjualan: did++, id_penjualan: id, id_barang: b.id_barang,
                jumlah_barang: l.qty, harga_beli: b.harga_beli, harga_jual: b.harga_jual,
                diskon_tipe: 'persen', diskon_nilai: l.diskon_persen || 0,
                diskon_nominal: discNom, subtotal: sub,
            });
        }
        const lunas = opts.jenis_transaksi === 'tunai' || opts.total_bayar >= total;
        const nowIso = new Date().toISOString();
        state.penjualan.unshift({
            id_penjualan: id, id_sekolah: 1, id_user: opts.id_user ?? 3,
            id_pelanggan: opts.id_pelanggan ?? null, tanggal_penjualan: nowIso,
            total_faktur: total, total_bayar: opts.total_bayar,
            kembalian: Math.max(0, opts.total_bayar - total),
            status_pembayaran: lunas ? 'sudah bayar' : 'belum bayar',
            jenis_transaksi: opts.jenis_transaksi, cara_bayar: opts.cara_bayar,
            note: opts.note ?? '', created_at: nowIso, created_by: opts.id_user ?? 3, is_delete: 0,
        });
        state.detailPenjualan.push(...dets);
        for (const l of opts.lines) {
            const b = state.barang.find((x) => x.id_barang === l.id_barang);
            if (b) b.stok -= l.qty;
        }
        persist();
        return state.penjualan[0];
    }

    /* ================= PEMBELIAN ================= */
    interface BuyLine { id_barang: number; jumlah: number; harga_beli: number }
    function createPembelian(opts: {
        id_supplier: number; id_user?: number; jenis_transaksi: 'tunai' | 'kredit';
        cara_bayar: string; note?: string; lines: BuyLine[];
    }) {
        const id = nextId(state.pembelian, 'id_pembelian');
        const total = opts.lines.reduce((s, l) => s + l.jumlah * l.harga_beli, 0);
        const nowIso = new Date().toISOString();
        const no = `PO-2026-${String(id).padStart(4, '0')}`;
        state.pembelian.unshift({
            id_pembelian: id, id_sekolah: 1, id_supplier: opts.id_supplier, id_user: opts.id_user ?? 2,
            nomor_faktur: no, tanggal_faktur: nowIso, total_bayar: total,
            status_pembelian: 'draft', jenis_transaksi: opts.jenis_transaksi,
            cara_bayar: opts.cara_bayar, note: opts.note ?? '',
            created_at: nowIso, created_by: opts.id_user ?? 2, is_delete: 0,
        });
        let did = nextId(state.detailPembelian, 'id_detail_pembelian');
        for (const l of opts.lines) {
            const b = state.barang.find((x) => x.id_barang === l.id_barang);
            state.detailPembelian.push({
                id_detail_pembelian: did++, id_pembelian: id, id_barang: l.id_barang,
                satuan: b?.satuan ?? 'pcs', jumlah: l.jumlah, harga_beli: l.harga_beli,
                subtotal: l.jumlah * l.harga_beli,
            });
        }
        persist();
        return state.pembelian[0];
    }
    /** Draft -> selesai: stok bertambah. Di BE: DB::transaction + lock. */
    function selesaikanPembelian(id: number) {
        const p = state.pembelian.find((x) => x.id_pembelian === id);
        if (!p || p.status_pembelian === 'selesai') return;
        p.status_pembelian = 'selesai';
        for (const d of state.detailPembelian.filter((x) => x.id_pembelian === id)) {
            const b = state.barang.find((x) => x.id_barang === d.id_barang);
            if (b) {
                b.stok += d.jumlah;
                b.harga_beli = d.harga_beli; // update HPP terakhir
            }
        }
        persist();
    }
    function deletePembelian(id: number) {
        const p = state.pembelian.find((x) => x.id_pembelian === id);
        if (p) p.is_delete = 1;
        persist();
    }

    return {
        state,
        barangAktif, pelangganAktif, supplierAktif, usersAktif, penjualanAktif, pembelianAktif, sekolahAktif,
        namaBarang, namaPelanggan, namaSupplier, namaUser, namaRole,
        namaKategori, namaKelompok, namaKelompokPelanggan, namaSekolah,
        statusSekolah, sekolahBelumBayar, detailJual, detailBeli,
        jualHariIni, omzetHariIni, stokMenipis, piutang, omzet7Hari,
        saveBarang, deleteBarang, savePelanggan, deletePelanggan,
        saveSupplier, deleteSupplier, saveUser, toggleUser, saveSekolah, toggleSekolah,
        createPenjualan, createPembelian, selesaikanPembelian, deletePembelian,
    };
}
