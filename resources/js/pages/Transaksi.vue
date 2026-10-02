<template>
    <RoleDenied v-if="!auth.can('/transaksi')" page="Transaksi" :needed="['Kasir']" />
    <div v-else class="space-y-4">
    <div class="grid grid-cols-1 items-start gap-4 lg:grid-cols-12">
        <!-- KATALOG (tengah — klik-klik produk) -->
        <div class="space-y-4 lg:col-span-8">
            <div class="flex flex-col gap-2 rounded-xl border border-slate-200 dark:border-white/[0.06] bg-white dark:bg-white/[0.02] p-4 sm:flex-row">
                <div class="flex h-10 flex-1 items-center gap-2 rounded-lg border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-black/40 px-3">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4 text-slate-400 dark:text-white/30"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
                    <input v-model="q" placeholder="Cari nama / barcode…" class="w-full bg-transparent text-sm outline-none placeholder:text-slate-400 dark:placeholder:text-white/25" />
                </div>
                <select v-model="katFilter" class="h-10 rounded-lg border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-black/40 px-3 text-sm text-slate-700 dark:text-white/80 outline-none">
                    <option :value="0">Semua kategori</option>
                    <option v-for="k in store.state.kategori" :key="k.id_kategori" :value="k.id_kategori">{{ k.nama }}</option>
                </select>
            </div>
            <p v-if="realtimeErr" class="rounded-lg bg-amber-500/10 px-3 py-2 text-[11px] font-bold text-amber-700 dark:text-amber-300">{{ realtimeErr }}</p>
            <div class="grid max-h-[560px] grid-cols-2 gap-3 overflow-y-auto pr-0.5 sm:grid-cols-3 lg:grid-cols-2 2xl:grid-cols-3">
                <button v-for="b in paged" :key="b.id_barang" @click="add(b)"
                    :disabled="Number(b.stok) <= 0 || !Number(b.is_active)"
                    class="group cursor-pointer rounded-xl border border-slate-200 dark:border-white/[0.06] bg-white dark:bg-white/[0.02] p-3.5 text-left transition-all hover:border-emerald-500/40 hover:bg-emerald-500/[0.06] disabled:cursor-not-allowed disabled:opacity-40">
                    <div class="flex items-start justify-between gap-2">
                        <p class="line-clamp-2 min-h-8 text-xs leading-snug font-bold">{{ b.nama }}</p>
                        <span class="shrink-0 rounded px-1.5 py-0.5 text-[9px] font-black tabular-nums"
                            :class="Number(b.stok) === 0 ? 'bg-rose-500/20 text-rose-700 dark:text-rose-300' : Number(b.stok) <= 10 ? 'bg-amber-500/20 text-amber-700 dark:text-amber-300' : 'bg-emerald-500/15 text-emerald-700 dark:text-emerald-300'">{{ Number(b.stok) === 0 ? 'STOK HABIS' : Number(b.stok) <= 10 ? `Sisa ${b.stok}` : `Stok ${b.stok}` }}</span>
                    </div>
                    <p class="mt-1 font-mono text-[10px] text-slate-400 dark:text-white/25">{{ b.barcode }}</p>
                    <div class="mt-2 flex items-center justify-between">
                        <p class="text-sm font-black text-emerald-700 dark:text-emerald-400">{{ formatRupiah(Number(b.harga_jual)) }}</p>
                        <span class="text-[10px] text-slate-400 dark:text-white/30">{{ store.namaKategori(b.id_kategori) }}</span>
                    </div>
                    <p v-if="Number(b.stok) === 0" class="mt-1.5 text-[10px] font-bold text-rose-600 dark:text-rose-400">stock habis — restock via admin</p>
                </button>
            </div>
            <p v-if="!filtered.length" class="rounded-xl border border-dashed border-slate-200 dark:border-white/10 py-10 text-center text-sm text-slate-400 dark:text-white/30">Produk tidak ditemukan.</p>
            <Pagination :page="page" :total-pages="totalPages" @update:page="page = $event" />
        </div>

        <!-- KERANJANG (kanan — submit di sini, sticky) -->
        <div class="h-fit rounded-xl border border-slate-200 dark:border-white/[0.06] bg-white dark:bg-white/[0.02] p-4 lg:sticky lg:top-20 lg:col-span-4">
            <h2 class="text-sm font-bold">🛒 Keranjang <span class="text-slate-400 dark:text-white/30">({{ cart.length }})</span></h2>
            <div class="mt-3 grid grid-cols-2 gap-2">
                <select v-model="idPelanggan" class="h-9 rounded-lg border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-black/40 px-2 text-xs outline-none">
                    <option :value="null">Umum / Walk-in</option>
                    <option v-for="p in store.pelangganAktif.value" :key="p.id_pelanggan" :value="p.id_pelanggan">{{ p.nama_pelanggan }}</option>
                </select>
                <select v-model="caraBayar" class="h-9 rounded-lg border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-black/40 px-2 text-xs outline-none">
                    <option>Tunai</option><option>QRIS</option><option>Transfer</option><option>Tempo</option>
                </select>
            </div>
            <div class="mt-2 flex gap-2 text-[11px] font-bold">
                <button @click="jenis = 'tunai'" :class="jenis === 'tunai' ? 'bg-emerald-500 text-white' : 'bg-slate-900/[0.04] dark:bg-white/5 text-slate-500 dark:text-white/50'" class="h-8 flex-1 cursor-pointer rounded-lg">Tunai</button>
                <button @click="jenis = 'kredit'" :class="jenis === 'kredit' ? 'bg-amber-500 text-black' : 'bg-slate-900/[0.04] dark:bg-white/5 text-slate-500 dark:text-white/50'" class="h-8 flex-1 cursor-pointer rounded-lg">Kredit</button>
            </div>

            <div class="mt-3 max-h-80 space-y-2 overflow-y-auto pr-0.5">
                <div v-for="l in cart" :key="l.id_barang" class="rounded-lg border border-slate-200 dark:border-white/[0.06] bg-white dark:bg-black/30 p-2.5">
                    <div class="flex items-start justify-between gap-2">
                        <p class="text-xs font-bold">{{ store.namaBarang(l.id_barang) }}</p>
                        <button @click="remove(l.id_barang)" class="cursor-pointer text-[11px] text-rose-600 dark:text-rose-400 hover:text-rose-500 dark:hover:text-rose-300">✕</button>
                    </div>
                    <div class="mt-2 flex items-center gap-1.5">
                        <button @click="dec(l)" class="h-7 w-7 shrink-0 cursor-pointer rounded-md bg-slate-900/[0.06] dark:bg-white/10 text-sm font-black">−</button>
                        <input :value="l.qty" @change="setQty(l, $event.target.value)" type="number" min="1" :max="stokOf(l.id_barang)" title="Jumlah (ketik untuk borong)"
                            class="h-7 w-14 rounded-md border border-slate-200 dark:border-white/10 bg-white dark:bg-black/50 px-1 text-center text-sm font-black tabular-nums text-slate-900 dark:text-white outline-none focus:border-emerald-500/50" />
                        <button @click="inc(l)" class="h-7 w-7 shrink-0 cursor-pointer rounded-md bg-slate-900/[0.06] dark:bg-white/10 text-sm font-black">+</button>
                        <span class="text-[9px] text-slate-400 dark:text-white/30">/ {{ stokOf(l.id_barang) }}</span>
                        <div class="ml-auto flex items-center gap-1 text-[10px] text-slate-500 dark:text-white/40">disc <input v-model.number="l.diskon_persen" type="number" min="0" max="100" class="h-7 w-12 rounded-md border border-slate-200 dark:border-white/10 bg-white dark:bg-black/50 px-1 text-right text-xs text-slate-900 dark:text-white outline-none" />%</div>
                    </div>
                    <p class="mt-1 text-right text-xs font-black text-emerald-700 dark:text-emerald-400">{{ formatRupiah(lineSub(l)) }}</p>
                </div>
                <p v-if="!cart.length" class="rounded-lg border border-dashed border-slate-200 dark:border-white/10 py-8 text-center text-xs text-slate-400 dark:text-white/30">Klik produk untuk menambah.</p>
            </div>

            <div class="mt-3 space-y-1 border-t border-slate-200 dark:border-white/10 pt-3 text-xs">
                <div class="flex justify-between text-slate-500 dark:text-white/50"><span>Total item</span><span class="font-bold text-slate-900 dark:text-white">{{ totalQty }}</span></div>
                <div class="flex justify-between text-slate-500 dark:text-white/50"><span>Diskon</span><span class="font-bold text-emerald-700 dark:text-emerald-400">−{{ formatRupiah(totalDisc) }}</span></div>
                <div class="flex justify-between text-base font-black"><span>Total</span><span class="text-emerald-700 dark:text-emerald-400">{{ formatRupiah(total) }}</span></div>
            </div>

            <div class="mt-3 rounded-lg border border-slate-200 dark:border-white/[0.06] bg-white dark:bg-black/30 p-3 text-center">
                <p class="text-[10px] tracking-widest text-slate-500 dark:text-white/40 uppercase">Total Tagihan</p>
                <p class="mt-0.5 text-2xl font-black text-emerald-700 dark:text-emerald-400">{{ formatRupiah(total) }}</p>
            </div>
            <template v-if="jenis === 'tunai'">
                <label class="mt-3 block text-[11px] font-bold text-slate-500 dark:text-white/50">Nominal bayar
                    <input v-model.number="bayar" type="number" min="0" class="mt-1 h-11 w-full rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-black/50 px-3 text-right text-base font-black outline-none focus:border-emerald-500/50" placeholder="0" />
                </label>
                <div class="mt-3 flex items-center justify-between rounded-lg border border-slate-200 dark:border-white/[0.06] bg-white dark:bg-black/30 px-3 py-2.5 text-xs">
                    <span class="text-slate-500 dark:text-white/50">Kembalian</span>
                    <span class="text-sm font-black" :class="kembalian < 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-900 dark:text-white'">{{ formatRupiah(Math.max(0, kembalian)) }}</span>
                </div>
            </template>
            <div v-else class="mt-3 rounded-lg border border-amber-500/30 bg-amber-500/[0.07] px-3 py-2.5 text-[11px] leading-relaxed text-amber-700 dark:text-amber-300">
                💳 <b>Transaksi kredit</b> — tanpa pembayaran sekarang. Otomatis tercatat sebagai <b>piutang (belum bayar)</b>{{ idPelanggan ? ` untuk <b>${store.namaPelanggan(idPelanggan)}</b>` : ' (umum/walk-in)' }}.
            </div>
            <p v-if="err" class="mt-2 rounded-lg bg-rose-500/10 px-3 py-2 text-[11px] font-bold text-rose-700 dark:text-rose-300">{{ err }}</p>
            <button @click="bayarSekarang" :disabled="!cart.length || paying" class="mt-3 h-11 w-full cursor-pointer rounded-lg text-sm font-black text-white transition-colors disabled:cursor-not-allowed disabled:opacity-40" :class="jenis === 'kredit' ? 'bg-amber-500 hover:bg-amber-400 text-black' : 'bg-emerald-500 hover:bg-emerald-400'">{{ paying ? 'MEMPROSES…' : jenis === 'kredit' ? `SIMPAN KREDIT • ${formatRupiah(total)}` : `BAYAR • ${formatRupiah(total)}` }}</button>
            <button v-if="cart.length" @click="cart = []" class="mt-2 h-8 w-full cursor-pointer rounded-lg bg-slate-900/[0.04] dark:bg-white/5 text-[11px] font-bold text-rose-700 dark:text-rose-300 hover:bg-rose-500/20">Batalkan Keranjang</button>
        </div>
    </div>

    <!-- LINK KE RIWAYAT (pisah halaman) -->
    <Link href="/riwayat-transaksi" class="flex items-center justify-between gap-2 rounded-xl border border-slate-200 dark:border-white/[0.06] bg-white dark:bg-white/[0.02] px-4 py-3 text-xs transition-colors hover:border-emerald-500/40 hover:bg-emerald-500/[0.06]">
        <span class="font-bold">🧾 Lihat Riwayat Transaksi <span class="font-normal text-slate-500 dark:text-white/40">— search, filter, cetak ulang & pembatalan ada di halaman tersendiri</span></span>
        <span class="shrink-0 font-black text-emerald-700 dark:text-emerald-400">Buka →</span>
    </Link>
    </div>

    <!-- STRUK (tutup hanya via tombol Tutup) -->
    <div v-if="struk" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4 backdrop-blur-sm">
        <div class="w-full max-w-xs rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-[#111] p-5 font-mono text-xs">
            <p class="text-center font-black">{{ store.namaSekolah() }}</p>
            <p class="text-center text-slate-500 dark:text-white/40">Struk Penjualan</p>
            <p class="mt-1 text-center text-slate-500 dark:text-white/40">#TRX-{{ String(struk.id_penjualan).padStart(4, '0') }} • {{ formatDateTime(struk.tanggal_penjualan) }}</p>
            <p v-if="isKreditStruk" class="mx-auto mt-2 w-fit rounded bg-amber-500/20 px-2 py-0.5 text-[10px] font-black text-amber-700 dark:text-amber-300">💳 KREDIT — BELUM BAYAR</p>
            <p v-else class="mx-auto mt-2 w-fit rounded bg-emerald-500/15 px-2 py-0.5 text-[10px] font-black text-emerald-700 dark:text-emerald-300">TUNAI — LUNAS</p>
            <div class="my-3 border-t border-dashed border-slate-200 dark:border-white/20"></div>
            <p v-for="d in strukLines" :key="d.id_detail_penjualan ?? d.id_barang" class="flex justify-between py-0.5">
                <span>{{ store.namaBarang(d.id_barang) }} ×{{ d.jumlah_barang ?? d.qty }}</span><span>{{ formatRupiah(d.subtotal) }}</span>
            </p>
            <div class="my-3 border-t border-dashed border-slate-200 dark:border-white/20"></div>
            <p class="flex justify-between font-black"><span>TOTAL</span><span>{{ formatRupiah(struk.total_faktur) }}</span></p>
            <template v-if="isKreditStruk">
                <p class="flex justify-between text-slate-600 dark:text-white/60"><span>Pelanggan</span><span>{{ store.namaPelanggan(struk.id_pelanggan) }}</span></p>
                <p class="flex justify-between text-slate-600 dark:text-white/60"><span>Dibayar</span><span>{{ formatRupiah(struk.total_bayar) }}</span></p>
                <p class="flex justify-between font-black text-amber-700 dark:text-amber-300"><span>Sisa piutang</span><span>{{ formatRupiah(Number(struk.total_faktur) - Number(struk.total_bayar)) }}</span></p>
            </template>
            <template v-else>
                <p class="flex justify-between text-slate-600 dark:text-white/60"><span>Bayar ({{ struk.cara_bayar }})</span><span>{{ formatRupiah(struk.total_bayar) }}</span></p>
                <p class="flex justify-between text-slate-600 dark:text-white/60"><span>Kembali</span><span>{{ formatRupiah(struk.kembalian) }}</span></p>
            </template>
            <p class="mt-2 text-center text-slate-400 dark:text-white/30">{{ struk.status_pembayaran }} • Terima kasih 🙏</p>
            <div class="mt-4 flex gap-2 font-sans">
                <button @click="cetakUlang(struk)" class="h-9 flex-1 cursor-pointer rounded-lg bg-slate-900/[0.06] dark:bg-white/10 text-xs font-black text-slate-900 dark:text-white hover:bg-white/15">🖨️ Cetak</button>
                <button @click="struk = null" class="h-9 flex-1 cursor-pointer rounded-lg bg-emerald-500 text-xs font-black text-white hover:bg-emerald-400">Tutup</button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import RoleDenied from '@/components/RoleDenied.vue';
import Pagination from '@/components/Pagination.vue';
import { usePagination } from '@/composables/usePagination';
import { hydrate, mergeBarangRealtime, replaceCollection, usePosStore } from '@/composables/usePosStore';
import { useAuthMock } from '@/composables/useAuthMock';
import { formatDateTime, formatRupiah } from '@/lib/format';
import { apiFetch } from '@/lib/api';

defineOptions({ layout: DashboardLayout });
const props = defineProps({ barang: Array, pelanggan: Array, penjualan: Array, kategori: Array, kelompokPelanggan: Array, sekolah: Array });
let poll = null;
onMounted(() => {
    hydrate(props);
    fetchProduk(true);
    fetchPelanggan();
    poll = setInterval(() => { fetchProduk(); fetchPelanggan(); }, 4000);
});
onUnmounted(() => { if (poll) clearInterval(poll); });
const store = usePosStore();
const auth = useAuthMock();

const q = ref(''), katFilter = ref(0), cart = ref([]), idPelanggan = ref(null);
const caraBayar = ref('Tunai'), jenis = ref('tunai'), bayar = ref(0);
const err = ref(''), struk = ref(null), paying = ref(false);
const realtimeErr = ref('');

const toNum = (v) => {
    const n = Number(v);
    return Number.isFinite(n) ? n : 0;
};
const clampDisc = (v) => {
    const n = toNum(v);
    if (n < 0) return 0;
    if (n > 100) return 100;
    return n;
};

/* Stok 0 selalu paling bawah, lalu sort nama. REALTIME karena computed dari store. */
const { page, totalPages, paged, filtered } = usePagination(() => store.barangAktif.value.filter((b) => {
    const okQ = !q.value || b.nama.toLowerCase().includes(q.value.toLowerCase()) || (b.barcode || '').includes(q.value);
    const okK = !katFilter.value || Number(b.id_kategori) === Number(katFilter.value);
    return okQ && okK;
}).sort((a, b) => {
    const sa = toNum(a.stok) <= 0 ? 1 : 0;
    const sb = toNum(b.stok) <= 0 ? 1 : 0;
    if (sa !== sb) return sa - sb;
    return String(a.nama).localeCompare(String(b.nama));
}), 12, [q, katFilter]);

const price = (id) => toNum(store.state.barang.find((b) => Number(b.id_barang) === Number(id))?.harga_jual ?? 0);
const lineSub = (l) => {
    const p = price(l.id_barang);
    const qty = Math.max(0, Math.floor(toNum(l.qty)));
    const disc = clampDisc(l.diskon_persen);
    return Math.round(p * qty * (1 - disc / 100));
};
const total = computed(() => cart.value.reduce((s, l) => s + lineSub(l), 0));
const totalQty = computed(() => cart.value.reduce((s, l) => s + Math.max(0, Math.floor(toNum(l.qty))), 0));
const totalDisc = computed(() => cart.value.reduce((s, l) => {
    const p = price(l.id_barang);
    const qty = Math.max(0, Math.floor(toNum(l.qty)));
    return s + Math.round(p * qty * (clampDisc(l.diskon_persen) / 100));
}, 0));
const kembalian = computed(() => toNum(bayar.value) - total.value);
const strukLines = computed(() => {
    if (!struk.value) return [];
    if (Array.isArray(struk.value.details) && struk.value.details.length) return struk.value.details;
    if (struk.value.id_penjualan) return store.detailJual(struk.value.id_penjualan);
    return [];
});

function extractRows(json) {
    if (Array.isArray(json)) return json;
    if (Array.isArray(json?.data)) return json.data;
    if (Array.isArray(json?.data?.data)) return json.data.data;
    return [];
}

/* REALTIME: ambil stok terbaru dari DB. Dipanggil tiap 4 detik + setelah transaksi.
   Produk yang dihapus admin (is_delete=1) otomatis hilang dari API -> hilang dari katalog. */
async function fetchProduk(silentFail = false) {
    try {
        const json = await apiFetch('/api/produk?per_page=500');
        const rows = extractRows(json);
        if (rows.length || json?.data) {
            mergeBarangRealtime(rows);
            realtimeErr.value = '';
            pruneCart();
        }
    } catch (e) {
        if (!silentFail && !realtimeErr.value) realtimeErr.value = 'Gagal sinkron stok realtime, menampilkan data terakhir.';
    }
}
async function fetchPelanggan() {
    try {
        const json = await apiFetch('/api/pelanggan?per_page=200');
        const rows = extractRows(json);
        if (rows.length || json?.data) replaceCollection('pelanggan', rows);
    } catch { /* abaikan: pakai hydrate props */ }
}
/* Samakan keranjang dengan stok realtime: hapus barang yang sudah dihapus admin,
   jepit qty ke sisa stok terbaru. */
function pruneCart() {
    const aktif = new Map(store.state.barang.filter((b) => !b.is_delete).map((b) => [Number(b.id_barang), b]));
    cart.value = cart.value.filter((l) => {
        const b = aktif.get(Number(l.id_barang));
        if (!b) return false;
        if (!toNum(b.is_active)) return false;
        const sisa = Math.floor(toNum(b.stok));
        if (sisa <= 0) return false;
        if (toNum(l.qty) > sisa) l.qty = sisa;
        l.diskon_persen = clampDisc(l.diskon_persen);
        return true;
    });
}

function add(b) {
    const stok = Math.floor(toNum(b.stok));
    if (stok <= 0 || !toNum(b.is_active)) return;
    const l = cart.value.find((x) => Number(x.id_barang) === Number(b.id_barang));
    if (l) { if (toNum(l.qty) < stok) l.qty = Math.floor(toNum(l.qty)) + 1; }
    else cart.value.push({ id_barang: b.id_barang, qty: 1, diskon_persen: 0 });
}
function stokOf(id) {
    const b = store.state.barang.find((x) => Number(x.id_barang) === Number(id));
    return b ? Math.max(0, Math.floor(toNum(b.stok))) : 0;
}
function inc(l) {
    const stok = stokOf(l.id_barang);
    if (Math.floor(toNum(l.qty)) < stok) l.qty = Math.floor(toNum(l.qty)) + 1;
}
function dec(l) { l.qty = Math.floor(toNum(l.qty)) - 1; if (l.qty <= 0) remove(l.id_barang); }
function remove(id) { cart.value = cart.value.filter((x) => Number(x.id_barang) !== Number(id)); }
/* Input angka langsung (borong): dijepit 1..stok, 0/huruf dikembalikan ke 1. */
function setQty(l, raw) {
    const stok = stokOf(l.id_barang);
    let v = Math.floor(toNum(raw));
    if (!Number.isFinite(v) || v < 1) v = 1;
    if (stok > 0 && v > stok) v = stok;
    l.qty = v;
}
const isKreditStruk = computed(() => {
    if (!struk.value) return false;
    return struk.value.jenis_transaksi === 'kredit' || struk.value.status_pembayaran === 'belum bayar';
});

async function bayarSekarang() {
    err.value = '';
    if (!cart.value.length || paying.value) return;
    const lines = cart.value.map((l) => ({ id_barang: Number(l.id_barang), qty: Math.max(1, Math.floor(toNum(l.qty))), diskon_persen: clampDisc(l.diskon_persen) }));
    if (jenis.value === 'tunai' && toNum(bayar.value) < total.value) { err.value = 'Nominal bayar kurang dari total.'; return; }
    paying.value = true;
    try {
        const res = await apiFetch('/api/penjualan', {
            method: 'POST',
            body: JSON.stringify({
                id_pelanggan: idPelanggan.value,
                cara_bayar: caraBayar.value,
                jenis_transaksi: jenis.value,
                total_bayar: jenis.value === 'kredit' ? 0 : toNum(bayar.value),
                lines,
            }),
        });
        struk.value = res?.data ?? res;
        cart.value = []; bayar.value = 0;
        await fetchProduk();
    } catch (e) {
        err.value = e?.errors?.lines?.[0] || e?.errors?.total_bayar?.[0] || e?.message || 'Transaksi gagal.';
    } finally {
        paying.value = false;
    }
}

function cetakUlang(t) {
    const kredit = t.jenis_transaksi === 'kredit' || t.status_pembayaran === 'belum bayar';
    const lines = strukLines.value.map((d) =>
        `<div class="row"><span>${store.namaBarang(d.id_barang)} x${d.jumlah_barang ?? d.qty}</span><span>${formatRupiah(d.subtotal)}</span></div>`).join('');
    const payRows = kredit
        ? `<div class="row"><span>Pelanggan</span><span>${store.namaPelanggan(t.id_pelanggan)}</span></div>
<div class="row"><span>Dibayar</span><span>${formatRupiah(t.total_bayar)}</span></div>
<div class="row b"><span>Sisa piutang</span><span>${formatRupiah(Number(t.total_faktur) - Number(t.total_bayar))}</span></div>`
        : `<div class="row"><span>Bayar (${t.cara_bayar})</span><span>${formatRupiah(t.total_bayar)}</span></div>
<div class="row"><span>Kembali</span><span>${formatRupiah(t.kembalian)}</span></div>`;
    const html = `<!DOCTYPE html><html><head><meta charset="utf-8"><title>Struk #TRX-${String(t.id_penjualan).padStart(4, '0')}</title>
<style>body{font-family:monospace;font-size:12px;width:220px;margin:0 auto;padding:8px;color:#000}.c{text-align:center}.row{display:flex;justify-content:space-between}hr{border:none;border-top:1px dashed #000;margin:8px 0}.b{font-weight:bold}</style></head><body>
<div class="c b">${store.namaSekolah()}<br>Struk Penjualan</div>
<div class="c">#TRX-${String(t.id_penjualan).padStart(4, '0')} &bull; ${formatDateTime(t.tanggal_penjualan)}</div>
<div class="c b">${kredit ? 'KREDIT &mdash; BELUM BAYAR' : 'TUNAI &mdash; LUNAS'}</div><hr>${lines}<hr>
<div class="row b"><span>TOTAL</span><span>${formatRupiah(t.total_faktur)}</span></div>
${payRows}
<div class="c">${t.status_pembayaran} &bull; Terima kasih</div>
<script>window.onload=()=>{window.print();};<\/script></body></html>`;
    const w = window.open('', '_blank', 'width=320,height=600');
    if (w) { w.document.write(html); w.document.close(); }
}
</script>
