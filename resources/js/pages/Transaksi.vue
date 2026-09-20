<template>
    <RoleDenied v-if="!auth.can('/transaksi')" page="Transaksi" :needed="['Kasir']" />
    <div v-else class="space-y-4">
    <div class="grid grid-cols-1 items-start gap-4 lg:grid-cols-12">
        <!-- KATALOG (tengah — klik-klik produk) -->
        <div class="space-y-4 lg:col-span-8">
            <div class="flex flex-col gap-2 rounded-xl border border-white/[0.06] bg-white/[0.02] p-4 sm:flex-row">
                <div class="flex h-10 flex-1 items-center gap-2 rounded-lg border border-white/[0.08] bg-black/40 px-3">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4 text-white/30"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
                    <input v-model="q" placeholder="Cari nama / barcode…" class="w-full bg-transparent text-sm outline-none placeholder:text-white/25" />
                </div>
                <select v-model="katFilter" class="h-10 rounded-lg border border-white/[0.08] bg-black/40 px-3 text-sm text-white/80 outline-none">
                    <option :value="0">Semua kategori</option>
                    <option v-for="k in store.state.kategori" :key="k.id_kategori" :value="k.id_kategori">{{ k.nama }}</option>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-2 2xl:grid-cols-3">
                <button v-for="b in filtered" :key="b.id_barang" @click="add(b)"
                    :disabled="b.stok <= 0 || !b.is_active"
                    class="group rounded-xl border border-white/[0.06] bg-white/[0.02] p-3.5 text-left transition-all hover:border-emerald-500/40 hover:bg-emerald-500/[0.06] disabled:cursor-not-allowed disabled:opacity-40">
                    <div class="flex items-start justify-between gap-2">
                        <p class="line-clamp-2 min-h-8 text-xs leading-snug font-bold">{{ b.nama }}</p>
                        <span v-if="b.stok <= 10" class="shrink-0 rounded px-1 py-0.5 text-[9px] font-black" :class="b.stok === 0 ? 'bg-rose-500/20 text-rose-300' : 'bg-amber-500/20 text-amber-300'">{{ b.stok === 0 ? 'HABIS' : `Sisa ${b.stok}` }}</span>
                    </div>
                    <p class="mt-1 font-mono text-[10px] text-white/25">{{ b.barcode }}</p>
                    <div class="mt-2 flex items-center justify-between">
                        <p class="text-sm font-black text-emerald-400">{{ formatRupiahShort(b.harga_jual) }}</p>
                        <span class="text-[10px] text-white/30">{{ store.namaKategori(b.id_kategori) }}</span>
                    </div>
                </button>
            </div>
            <p v-if="!filtered.length" class="rounded-xl border border-dashed border-white/10 py-10 text-center text-sm text-white/30">Produk tidak ditemukan.</p>
        </div>

        <!-- KERANJANG (kanan — submit di sini, sticky) -->
        <div class="h-fit rounded-xl border border-white/[0.06] bg-white/[0.02] p-4 lg:sticky lg:top-20 lg:col-span-4">
            <h2 class="text-sm font-bold">🛒 Keranjang <span class="text-white/30">({{ cart.length }})</span></h2>
            <div class="mt-3 grid grid-cols-2 gap-2">
                <select v-model="idPelanggan" class="h-9 rounded-lg border border-white/[0.08] bg-black/40 px-2 text-xs outline-none">
                    <option :value="null">Umum / Walk-in</option>
                    <option v-for="p in store.pelangganAktif.value" :key="p.id_pelanggan" :value="p.id_pelanggan">{{ p.nama_pelanggan }}</option>
                </select>
                <select v-model="caraBayar" class="h-9 rounded-lg border border-white/[0.08] bg-black/40 px-2 text-xs outline-none">
                    <option>Tunai</option><option>QRIS</option><option>Transfer</option><option>Tempo</option>
                </select>
            </div>
            <div class="mt-2 flex gap-2 text-[11px] font-bold">
                <button @click="jenis = 'tunai'" :class="jenis === 'tunai' ? 'bg-emerald-500 text-white' : 'bg-white/5 text-white/50'" class="h-8 flex-1 rounded-lg">Tunai</button>
                <button @click="jenis = 'kredit'" :class="jenis === 'kredit' ? 'bg-amber-500 text-black' : 'bg-white/5 text-white/50'" class="h-8 flex-1 rounded-lg">Kredit</button>
            </div>

            <div class="mt-3 max-h-80 space-y-2 overflow-y-auto pr-0.5">
                <div v-for="l in cart" :key="l.id_barang" class="rounded-lg border border-white/[0.06] bg-black/30 p-2.5">
                    <div class="flex items-start justify-between gap-2">
                        <p class="text-xs font-bold">{{ store.namaBarang(l.id_barang) }}</p>
                        <button @click="remove(l.id_barang)" class="text-[11px] text-rose-400 hover:text-rose-300">✕</button>
                    </div>
                    <div class="mt-2 flex items-center gap-1.5">
                        <button @click="dec(l)" class="h-7 w-7 rounded-md bg-white/10 text-sm font-black">−</button>
                        <span class="w-8 text-center text-sm font-black">{{ l.qty }}</span>
                        <button @click="inc(l)" class="h-7 w-7 rounded-md bg-white/10 text-sm font-black">+</button>
                        <div class="ml-auto flex items-center gap-1 text-[10px] text-white/40">disc <input v-model.number="l.diskon_persen" type="number" min="0" max="100" class="h-7 w-12 rounded-md border border-white/10 bg-black/50 px-1 text-right text-xs text-white outline-none" />%</div>
                    </div>
                    <p class="mt-1 text-right text-xs font-black text-emerald-400">{{ formatRupiah(lineSub(l)) }}</p>
                </div>
                <p v-if="!cart.length" class="rounded-lg border border-dashed border-white/10 py-8 text-center text-xs text-white/30">Klik produk untuk menambah.</p>
            </div>

            <div class="mt-3 space-y-1 border-t border-white/10 pt-3 text-xs">
                <div class="flex justify-between text-white/50"><span>Total item</span><span class="font-bold text-white">{{ totalQty }}</span></div>
                <div class="flex justify-between text-white/50"><span>Diskon</span><span class="font-bold text-emerald-400">−{{ formatRupiah(totalDisc) }}</span></div>
                <div class="flex justify-between text-base font-black"><span>Total</span><span class="text-emerald-400">{{ formatRupiah(total) }}</span></div>
            </div>

            <div class="mt-3 rounded-lg border border-white/[0.06] bg-black/30 p-3 text-center">
                <p class="text-[10px] tracking-widest text-white/40 uppercase">Total Tagihan</p>
                <p class="mt-0.5 text-2xl font-black text-emerald-400">{{ formatRupiah(total) }}</p>
            </div>
            <label class="mt-3 block text-[11px] font-bold text-white/50">Nominal bayar
                <input v-model.number="bayar" type="number" min="0" class="mt-1 h-11 w-full rounded-lg border border-white/10 bg-black/50 px-3 text-right text-base font-black outline-none focus:border-emerald-500/50" placeholder="0" />
            </label>
            <div class="mt-3 flex items-center justify-between rounded-lg border border-white/[0.06] bg-black/30 px-3 py-2.5 text-xs">
                <span class="text-white/50">Kembalian</span>
                <span class="text-sm font-black" :class="kembalian < 0 ? 'text-rose-400' : 'text-white'">{{ formatRupiah(Math.max(0, kembalian)) }}</span>
            </div>
            <p v-if="err" class="mt-2 rounded-lg bg-rose-500/10 px-3 py-2 text-[11px] font-bold text-rose-300">{{ err }}</p>
            <button @click="bayarSekarang" :disabled="!cart.length" class="mt-3 h-11 w-full rounded-lg bg-emerald-500 text-sm font-black text-white transition-colors hover:bg-emerald-400 disabled:opacity-40">BAYAR • {{ formatRupiah(total) }}</button>
            <button @click="cart = []" class="mt-2 h-8 w-full rounded-lg bg-white/5 text-[11px] font-bold text-rose-300 hover:bg-rose-500/20">Batalkan Keranjang</button>
        </div>
    </div>

    <!-- LINK KE RIWAYAT (pisah halaman) -->
    <Link href="/riwayat-transaksi" class="flex items-center justify-between gap-2 rounded-xl border border-white/[0.06] bg-white/[0.02] px-4 py-3 text-xs transition-colors hover:border-emerald-500/40 hover:bg-emerald-500/[0.06]">
        <span class="font-bold">🧾 Lihat Riwayat Transaksi <span class="font-normal text-white/40">— search, filter, cetak ulang & pembatalan ada di halaman tersendiri</span></span>
        <span class="shrink-0 font-black text-emerald-400">Buka →</span>
    </Link>
    </div>

    <!-- STRUK -->
    <div v-if="struk" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4 backdrop-blur-sm" @click.self="struk = null">
        <div class="w-full max-w-xs rounded-xl border border-white/10 bg-[#111] p-5 font-mono text-xs">
            <p class="text-center font-black">{{ store.namaSekolah() }}</p>
            <p class="text-center text-white/40">Struk Penjualan</p>
            <p class="mt-1 text-center text-white/40">#TRX-{{ String(struk.id_penjualan).padStart(4, '0') }} • {{ formatDateTime(struk.tanggal_penjualan) }}</p>
            <div class="my-3 border-t border-dashed border-white/20"></div>
            <p v-for="d in store.detailJual(struk.id_penjualan)" :key="d.id_detail_penjualan" class="flex justify-between py-0.5">
                <span>{{ store.namaBarang(d.id_barang) }} ×{{ d.jumlah_barang }}</span><span>{{ formatRupiah(d.subtotal) }}</span>
            </p>
            <div class="my-3 border-t border-dashed border-white/20"></div>
            <p class="flex justify-between font-black"><span>TOTAL</span><span>{{ formatRupiah(struk.total_faktur) }}</span></p>
            <p class="flex justify-between text-white/60"><span>Bayar ({{ struk.cara_bayar }})</span><span>{{ formatRupiah(struk.total_bayar) }}</span></p>
            <p class="flex justify-between text-white/60"><span>Kembali</span><span>{{ formatRupiah(struk.kembalian) }}</span></p>
            <p class="mt-2 text-center text-white/30">{{ struk.status_pembayaran }} • Terima kasih 🙏</p>
            <div class="mt-4 flex gap-2 font-sans">
                <button @click="cetakUlang(struk)" class="h-9 flex-1 rounded-lg bg-white/10 text-xs font-black text-white hover:bg-white/15">🖨️ Cetak</button>
                <button @click="struk = null" class="h-9 flex-1 rounded-lg bg-emerald-500 text-xs font-black text-white hover:bg-emerald-400">Tutup</button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import RoleDenied from '@/components/RoleDenied.vue';
import { hydrate, usePosStore } from '@/composables/usePosStore';
import { useAuthMock } from '@/composables/useAuthMock';
import { formatDateTime, formatRupiah, formatRupiahShort } from '@/lib/format';

defineOptions({ layout: DashboardLayout });
const props = defineProps({ barang: Array, pelanggan: Array, penjualan: Array });
onMounted(() => hydrate(props));
const store = usePosStore();
const auth = useAuthMock();

const q = ref(''), katFilter = ref(0), cart = ref([]), idPelanggan = ref(null);
const caraBayar = ref('Tunai'), jenis = ref('tunai'), bayar = ref(0);
const err = ref(''), struk = ref(null);

const filtered = computed(() => store.barangAktif.value.filter((b) => {
    const okQ = !q.value || b.nama.toLowerCase().includes(q.value.toLowerCase()) || b.barcode.includes(q.value);
    const okK = !katFilter.value || b.id_kategori === Number(katFilter.value);
    return okQ && okK;
}));
const price = (id) => store.state.barang.find((b) => b.id_barang === id)?.harga_jual ?? 0;
const lineSub = (l) => price(l.id_barang) * l.qty * (1 - (l.diskon_persen || 0) / 100);
const total = computed(() => cart.value.reduce((s, l) => s + lineSub(l), 0));
const totalQty = computed(() => cart.value.reduce((s, l) => s + l.qty, 0));
const totalDisc = computed(() => cart.value.reduce((s, l) => s + price(l.id_barang) * l.qty * ((l.diskon_persen || 0) / 100), 0));
const kembalian = computed(() => (bayar.value || 0) - total.value);

function add(b) {
    const l = cart.value.find((x) => x.id_barang === b.id_barang);
    if (l) { if (l.qty < b.stok) l.qty++; }
    else cart.value.push({ id_barang: b.id_barang, qty: 1, diskon_persen: 0 });
}
function inc(l) { const b = store.state.barang.find((x) => x.id_barang === l.id_barang); if (b && l.qty < b.stok) l.qty++; }
function dec(l) { l.qty--; if (l.qty <= 0) remove(l.id_barang); }
function remove(id) { cart.value = cart.value.filter((x) => x.id_barang !== id); }

function bayarSekarang() {
    err.value = '';
    if (!cart.value.length) return;
    if (jenis.value === 'tunai' && (bayar.value || 0) < total.value) { err.value = 'Nominal bayar kurang dari total.'; return; }
    try {
        const trx = store.createPenjualan({
            id_pelanggan: idPelanggan.value, cara_bayar: caraBayar.value,
            jenis_transaksi: jenis.value, total_bayar: jenis.value === 'kredit' ? 0 : (bayar.value || 0),
            lines: cart.value.map((l) => ({ id_barang: l.id_barang, qty: l.qty, diskon_persen: l.diskon_persen || 0 })),
        });
        struk.value = trx;
        cart.value = []; bayar.value = 0;
    } catch (e) { err.value = e.message; }
}

function cetakUlang(t) {
    const lines = store.detailJual(t.id_penjualan).map((d) =>
        `<div class="row"><span>${store.namaBarang(d.id_barang)} x${d.jumlah_barang}</span><span>${formatRupiah(d.subtotal)}</span></div>`).join('');
    const html = `<!DOCTYPE html><html><head><meta charset="utf-8"><title>Struk #TRX-${String(t.id_penjualan).padStart(4, '0')}</title>
<style>body{font-family:monospace;font-size:12px;width:220px;margin:0 auto;padding:8px;color:#000}.c{text-align:center}.row{display:flex;justify-content:space-between}hr{border:none;border-top:1px dashed #000;margin:8px 0}.b{font-weight:bold}</style></head><body>
<div class="c b">${store.namaSekolah()}<br>Struk Penjualan</div>
<div class="c">#TRX-${String(t.id_penjualan).padStart(4, '0')} &bull; ${formatDateTime(t.tanggal_penjualan)}</div><hr>${lines}<hr>
<div class="row b"><span>TOTAL</span><span>${formatRupiah(t.total_faktur)}</span></div>
<div class="row"><span>Bayar (${t.cara_bayar})</span><span>${formatRupiah(t.total_bayar)}</span></div>
<div class="row"><span>Kembali</span><span>${formatRupiah(t.kembalian)}</span></div>
<div class="c">${t.status_pembayaran} &bull; Terima kasih</div>
<script>window.onload=()=>{window.print();};<\/script></body></html>`;
    const w = window.open('', '_blank', 'width=320,height=600');
    if (w) { w.document.write(html); w.document.close(); }
}
</script>