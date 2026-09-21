<template>
    <RoleDenied v-if="!auth.can('/riwayat-transaksi')" page="Riwayat Transaksi" :needed="['Kasir']" />
    <div v-else class="space-y-4">
        <!-- Summary -->
        <div class="grid grid-cols-2 gap-4 xl:grid-cols-4">
            <div v-for="s in summary" :key="s.label" class="rounded-xl border border-slate-200 dark:border-white/[0.06] bg-white dark:bg-white/[0.02] p-4">
                <p class="text-[11px] text-slate-500 dark:text-white/40">{{ s.label }}</p>
                <p class="mt-1 text-lg font-black">{{ s.value }}</p>
                <p class="mt-0.5 text-[10px] text-slate-400 dark:text-white/30">{{ s.sub }}</p>
            </div>
        </div>

        <!-- Tab + search + filter -->
        <div class="rounded-xl border border-slate-200 dark:border-white/[0.06] bg-white dark:bg-white/[0.02] p-4">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div class="flex gap-1.5 text-[11px] font-bold">
                    <button @click="tab = 'hari'; page = 1" :class="tab === 'hari' ? 'bg-emerald-500 text-white' : 'bg-slate-900/[0.04] dark:bg-white/5 text-slate-500 dark:text-white/50'" class="h-8 rounded-lg px-3">Hari Ini ({{ countHari }})</button>
                    <button @click="tab = 'semua'; page = 1" :class="tab === 'semua' ? 'bg-emerald-500 text-white' : 'bg-slate-900/[0.04] dark:bg-white/5 text-slate-500 dark:text-white/50'" class="h-8 rounded-lg px-3">Semua ({{ countSemua }})</button>
                    <button v-if="auth.role.value === 'kasir'" @click="tab = 'saya'; page = 1" :class="tab === 'saya' ? 'bg-blue-500 text-white' : 'bg-slate-900/[0.04] dark:bg-white/5 text-slate-500 dark:text-white/50'" class="h-8 rounded-lg px-3">Penjualan Saya ({{ countSaya }})</button>
                </div>
                <Link href="/transaksi" class="h-8 rounded-lg bg-emerald-500 px-3 text-[11px] font-black leading-8 text-white hover:bg-emerald-400">+ Transaksi Baru</Link>
            </div>

            <div class="mt-3 grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-4">
                <div class="flex h-9 items-center gap-2 rounded-lg border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-black/40 px-3 sm:col-span-2 lg:col-span-2">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4 shrink-0 text-slate-400 dark:text-white/30"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
                    <input v-model="q" @input="page = 1" placeholder="Cari no. TRX / pelanggan / kasir…" class="w-full bg-transparent text-xs outline-none placeholder:text-slate-400 dark:placeholder:text-white/25" />
                </div>
                <select v-model="fStatus" @change="page = 1" class="h-9 rounded-lg border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-black/40 px-2 text-xs text-slate-700 dark:text-white/80 outline-none">
                    <option value="">Semua status</option>
                    <option value="sudah bayar">Sudah bayar</option>
                    <option value="belum bayar">Belum bayar (piutang)</option>
                </select>
                <select v-model="fCara" @change="page = 1" class="h-9 rounded-lg border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-black/40 px-2 text-xs text-slate-700 dark:text-white/80 outline-none">
                    <option value="">Semua cara bayar</option>
                    <option>Tunai</option><option>QRIS</option><option>Transfer</option><option>Tempo</option>
                </select>
            </div>
            <p class="mt-2 text-[11px] text-slate-500 dark:text-white/40">Menampilkan {{ paged.length }} dari {{ filtered.length }} transaksi • Omzet tampil: <span class="font-black text-emerald-700 dark:text-emerald-400">{{ formatRupiah(omzetFiltered) }}</span></p>

            <div class="mt-2 space-y-2">
                <div v-for="t in paged" :key="t.id_penjualan" class="rounded-lg border border-slate-200 dark:border-white/[0.05] bg-white dark:bg-black/30">
                    <button @click="openId = openId === t.id_penjualan ? 0 : t.id_penjualan" class="flex w-full items-center justify-between gap-2 px-3 py-2.5 text-xs">
                        <span class="text-left font-bold">#TRX-{{ String(t.id_penjualan).padStart(4, '0') }} • {{ store.namaPelanggan(t.id_pelanggan) }}
                            <span class="mt-0.5 block text-[10px] font-normal text-slate-400 dark:text-white/35">{{ formatDateTime(t.tanggal_penjualan) }} • {{ store.namaUser(t.id_user) }} • {{ t.cara_bayar }}</span>
                        </span>
                        <span class="flex shrink-0 items-center gap-2">
                            <span :class="t.status_pembayaran === 'sudah bayar' ? 'text-emerald-700 dark:text-emerald-400' : 'text-amber-700 dark:text-amber-400'" class="text-[10px] font-bold">{{ t.status_pembayaran }}</span>
                            <span class="font-black">{{ formatRupiah(t.total_faktur) }}</span>
                        </span>
                    </button>
                    <div v-if="openId === t.id_penjualan" class="border-t border-slate-200 dark:border-white/[0.05] px-3 py-2 text-[11px] text-slate-600 dark:text-white/60">
                        <p v-for="d in store.detailJual(t.id_penjualan)" :key="d.id_detail_penjualan" class="flex justify-between py-0.5">
                            <span>{{ store.namaBarang(d.id_barang) }} × {{ d.jumlah_barang }} <span v-if="d.diskon_nilai" class="text-emerald-700 dark:text-emerald-400">(-{{ d.diskon_nilai }}%)</span></span>
                            <span class="font-bold text-slate-900 dark:text-white/90">{{ formatRupiah(d.subtotal) }}</span>
                        </p>
                        <p class="mt-1 flex justify-between border-t border-slate-200 dark:border-white/10 pt-1 text-slate-700 dark:text-white/80"><span>Bayar ({{ t.cara_bayar }}) • Kembali</span><span class="font-bold">{{ formatRupiah(t.total_bayar) }} • {{ formatRupiah(t.kembalian) }}</span></p>
                        <div class="mt-2 flex gap-2">
                            <button @click="cetakUlang(t)" class="h-8 flex-1 rounded-lg bg-slate-900/[0.04] dark:bg-white/5 text-[11px] font-bold text-slate-600 dark:text-white/70 hover:bg-slate-900/5 dark:hover:bg-white/10">🖨️ Cetak Ulang</button>
                            <button v-if="canVoid(t)" @click="batalkan(t)" class="h-8 flex-1 rounded-lg bg-rose-500/10 text-[11px] font-bold text-rose-700 dark:text-rose-300 hover:bg-rose-500/20">Batalkan Transaksi</button>
                        </div>
                    </div>
                </div>
                <p v-if="!filtered.length" class="py-6 text-center text-xs text-slate-400 dark:text-white/30">Tidak ada transaksi yang cocok dengan filter.</p>
            </div>

            <div v-if="totalPages > 1" class="mt-3 flex items-center justify-between text-[11px] text-slate-500 dark:text-white/40">
                <button @click="page = Math.max(1, page - 1)" :disabled="page <= 1" class="h-8 rounded-lg bg-slate-900/[0.04] dark:bg-white/5 px-3 font-bold text-slate-600 dark:text-white/70 disabled:opacity-40">‹ Prev</button>
                <span>Halaman {{ page }} / {{ totalPages }}</span>
                <button @click="page = Math.min(totalPages, page + 1)" :disabled="page >= totalPages" class="h-8 rounded-lg bg-slate-900/[0.04] dark:bg-white/5 px-3 font-bold text-slate-600 dark:text-white/70 disabled:opacity-40">Next ›</button>
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
import { dayKey, formatDateTime, formatRupiah, todayKey } from '@/lib/format';

defineOptions({ layout: DashboardLayout });
const props = defineProps({ penjualan: Array, detailPenjualan: Array, pelanggan: Array, barang: Array });
onMounted(() => hydrate(props));
const store = usePosStore();
const auth = useAuthMock();

const tab = ref('hari'), q = ref(''), fStatus = ref(''), fCara = ref('');
const page = ref(1), openId = ref(0);
const PER_PAGE = 10;

const allSorted = computed(() => [...store.penjualanAktif.value].sort((a, b) => b.id_penjualan - a.id_penjualan));
const countHari = computed(() => store.penjualanAktif.value.filter((p) => dayKey(p.tanggal_penjualan) === todayKey()).length);
const countSemua = computed(() => store.penjualanAktif.value.length);
const countSaya = computed(() => {
    const myId = auth.user.value?.id_user ?? -1;
    return store.penjualanAktif.value.filter((p) => p.id_user === myId).length;
});
const base = computed(() => {
    const myId = auth.user.value?.id_user ?? -1;
    if (tab.value === 'hari') return allSorted.value.filter((p) => dayKey(p.tanggal_penjualan) === todayKey());
    if (tab.value === 'saya') return allSorted.value.filter((p) => p.id_user === myId);
    return allSorted.value;
});
const filtered = computed(() => {
    const s = q.value.trim().toLowerCase();
    return base.value.filter((t) => {
        if (fStatus.value && t.status_pembayaran !== fStatus.value) return false;
        if (fCara.value && t.cara_bayar !== fCara.value) return false;
        if (!s) return true;
        const noTrx = `#trx-${String(t.id_penjualan).padStart(4, '0')}`;
        return (
            noTrx.includes(s) ||
            String(t.id_penjualan).includes(s) ||
            store.namaPelanggan(t.id_pelanggan).toLowerCase().includes(s) ||
            store.namaUser(t.id_user).toLowerCase().includes(s)
        );
    });
});
const omzetFiltered = computed(() => filtered.value.reduce((s, t) => s + t.total_faktur, 0));
const totalPages = computed(() => Math.max(1, Math.ceil(filtered.value.length / PER_PAGE)));
const paged = computed(() => {
    if (page.value > totalPages.value) page.value = totalPages.value;
    const start = (page.value - 1) * PER_PAGE;
    return filtered.value.slice(start, start + PER_PAGE);
});

const summary = computed(() => [
    { label: 'Transaksi tampil', value: String(filtered.value.length), sub: `tab: ${tab.value}` },
    { label: 'Omzet tampil', value: formatRupiah(omzetFiltered.value), sub: 'Σ total_faktur filter aktif' },
    { label: 'Belum bayar', value: String(filtered.value.filter((t) => t.status_pembayaran === 'belum bayar').length), sub: 'piutang pada filter aktif' },
    { label: 'Hari ini (semua)', value: String(countHari.value), sub: `${formatRupiah(store.penjualanAktif.value.filter((p) => dayKey(p.tanggal_penjualan) === todayKey()).reduce((s, t) => s + t.total_faktur, 0))}` },
]);

function canVoid(t) {
    if (auth.role.value !== 'kasir') return true;
    const myId = auth.user.value?.id_user ?? -1;
    return t.id_user === myId && dayKey(t.tanggal_penjualan) === todayKey();
}
function batalkan(t) {
    if (!confirm(`Batalkan #TRX-${String(t.id_penjualan).padStart(4, '0')}? Stok akan dikembalikan.`)) return;
    store.voidPenjualan(t.id_penjualan);
    if (openId.value === t.id_penjualan) openId.value = 0;
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
