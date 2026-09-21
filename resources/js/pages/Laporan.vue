<template>
    <RoleDenied v-if="!auth.can('/laporan')" page="Laporan" :needed="['Admin', 'Super Admin']" />
    <div v-else class="space-y-4">
        <div class="flex flex-col gap-2 rounded-xl border border-slate-200 dark:border-white/[0.06] bg-white dark:bg-white/[0.02] p-4 sm:flex-row sm:items-center">
            <div class="flex gap-1.5">
                <button v-for="r in ranges" :key="r.key" @click="range = r.key"
                    :class="range === r.key ? 'bg-orange-500 text-white' : 'bg-slate-900/[0.04] dark:bg-white/5 text-slate-500 dark:text-white/50 hover:bg-slate-900/5 dark:hover:bg-white/10'"
                    class="h-9 rounded-lg px-3 text-xs font-black">{{ r.label }}</button>
            </div>
            <select v-model="fCara" class="h-9 rounded-lg border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-black/40 px-3 text-xs outline-none">
                <option value="">Semua cara bayar</option><option>Tunai</option><option>QRIS</option><option>Transfer</option><option>Tempo</option>
            </select>
            <select v-model="fJenis" class="h-9 rounded-lg border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-black/40 px-3 text-xs outline-none">
                <option value="">Tunai + Kredit</option><option value="tunai">Tunai saja</option><option value="kredit">Kredit saja</option>
            </select>
            <button @click="exportCsv" class="h-9 rounded-lg bg-slate-900/[0.04] dark:bg-white/5 px-4 text-xs font-black text-slate-600 dark:text-white/70 hover:bg-slate-900/5 dark:hover:bg-white/10 sm:ml-auto">⬇ Export CSV</button>
        </div>

        <div class="grid grid-cols-2 gap-4 xl:grid-cols-5">
            <div v-for="s in summary" :key="s.label" class="rounded-xl border border-slate-200 dark:border-white/[0.06] bg-white dark:bg-white/[0.02] p-4">
                <p class="text-[11px] text-slate-500 dark:text-white/40">{{ s.label }}</p>
                <p class="mt-1 text-lg font-black" :style="s.color ? { color: s.color } : {}">{{ s.value }}</p>
                <p class="mt-0.5 text-[10px] text-slate-400 dark:text-white/30">{{ s.sub }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 xl:grid-cols-3">
            <div class="rounded-xl border border-slate-200 dark:border-white/[0.06] bg-white dark:bg-white/[0.02] p-5 xl:col-span-2">
                <h2 class="text-sm font-bold">Omzet vs Laba Harian</h2>
                <p class="mt-0.5 text-[11px] text-slate-500 dark:text-white/40">laba = total_faktur − Σ(harga_beli × qty) dari tb_detail_penjualan</p>
                <div class="mt-5 flex h-48 items-end gap-2">
                    <div v-for="d in daily" :key="d.key" class="flex flex-1 flex-col items-center gap-1">
                        <div class="flex w-full flex-1 items-end gap-1">
                            <div class="flex-1 rounded-t bg-gradient-to-t from-orange-600/40 to-orange-400/90" :style="{ height: pct(d.omzet, maxOmzet) + '%' }" :title="'Omzet ' + formatRupiah(d.omzet)"></div>
                            <div class="flex-1 rounded-t bg-gradient-to-t from-emerald-600/40 to-emerald-400/90" :style="{ height: pct(d.laba, maxOmzet) + '%' }" :title="'Laba ' + formatRupiah(d.laba)"></div>
                        </div>
                        <span class="text-[9px] font-bold text-slate-400 dark:text-white/30">{{ d.label }}</span>
                    </div>
                </div>
                <div class="mt-3 flex gap-4 text-[11px] text-slate-500 dark:text-white/50">
                    <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-sm bg-orange-400"></span>Omzet</span>
                    <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-sm bg-emerald-400"></span>Laba kotor</span>
                </div>
            </div>
            <div class="rounded-xl border border-slate-200 dark:border-white/[0.06] bg-white dark:bg-white/[0.02] p-5">
                <h2 class="text-sm font-bold">Cara Bayar</h2>
                <div class="mt-4 space-y-3">
                    <div v-for="c in byCara" :key="c.cara">
                        <div class="flex justify-between text-xs"><span class="font-bold">{{ c.cara }}</span><span class="text-slate-500 dark:text-white/50">{{ c.count }} trx • {{ formatRupiahShort(c.total) }}</span></div>
                        <div class="mt-1 h-2 overflow-hidden rounded-full bg-slate-900/[0.04] dark:bg-white/5"><div class="h-full rounded-full bg-gradient-to-r from-orange-500 to-amber-400" :style="{ width: (c.total / Math.max(1, maxCara)) * 100 + '%' }"></div></div>
                    </div>
                    <p v-if="!byCara.length" class="text-xs text-slate-400 dark:text-white/30">Tidak ada data.</p>
                </div>
                <h2 class="mt-6 text-sm font-bold">Top Produk</h2>
                <div class="mt-2 space-y-2">
                    <div v-for="(t, i) in topProduk.slice(0, 5)" :key="t.id" class="flex items-center gap-2.5 text-xs">
                        <span class="w-5 text-center font-black text-slate-400 dark:text-white/25">{{ i + 1 }}</span>
                        <span class="flex-1 truncate font-bold">{{ t.nama }}</span>
                        <span class="text-slate-500 dark:text-white/40">{{ t.qty }} terjual</span>
                        <span class="font-black text-emerald-700 dark:text-emerald-400">{{ formatRupiahShort(t.omzet) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-white/[0.06]">
            <table class="w-full min-w-[760px] text-left text-xs">
                <thead><tr class="bg-white dark:bg-white/[0.03] text-[10px] tracking-wider text-slate-500 dark:text-white/40 uppercase">
                    <th class="px-4 py-2.5">ID / Tanggal</th><th class="px-3 py-2.5">Pelanggan</th><th class="px-3 py-2.5">Bayar</th><th class="px-3 py-2.5 text-right">Omzet</th><th class="px-3 py-2.5 text-right">HPP</th><th class="px-3 py-2.5 text-right">Laba</th><th class="px-3 py-2.5 text-right">Status</th>
                </tr></thead>
                <tbody>
                    <tr v-for="t in filtered" :key="t.id_penjualan" class="border-t border-slate-200 dark:border-white/[0.05] hover:bg-emerald-600/5 dark:hover:bg-white/[0.02]">
                        <td class="px-4 py-2.5"><p class="font-black">#TRX-{{ String(t.id_penjualan).padStart(4, '0') }}</p><p class="text-[10px] text-slate-400 dark:text-white/30">{{ formatDateTime(t.tanggal_penjualan) }}</p></td>
                        <td class="px-3 py-2.5 text-slate-600 dark:text-white/60">{{ store.namaPelanggan(t.id_pelanggan) }}</td>
                        <td class="px-3 py-2.5 text-slate-600 dark:text-white/60">{{ t.cara_bayar }} • {{ t.jenis_transaksi }}</td>
                        <td class="px-3 py-2.5 text-right font-bold">{{ formatRupiah(t.total_faktur) }}</td>
                        <td class="px-3 py-2.5 text-right text-slate-500 dark:text-white/40">{{ formatRupiah(hpp(t.id_penjualan)) }}</td>
                        <td class="px-3 py-2.5 text-right font-bold text-emerald-700 dark:text-emerald-400">{{ formatRupiah(t.total_faktur - hpp(t.id_penjualan)) }}</td>
                        <td class="px-3 py-2.5 text-right"><span class="rounded px-1.5 py-0.5 text-[10px] font-black" :class="t.status_pembayaran === 'sudah bayar' ? 'bg-emerald-500/15 text-emerald-700 dark:text-emerald-300' : 'bg-amber-500/15 text-amber-700 dark:text-amber-300'">{{ t.status_pembayaran }}</span></td>
                    </tr>
                </tbody>
            </table>
            <p v-if="!filtered.length" class="py-10 text-center text-sm text-slate-400 dark:text-white/30">Tidak ada transaksi pada rentang ini.</p>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import { hydrate, usePosStore } from '@/composables/usePosStore';
import RoleDenied from '@/components/RoleDenied.vue';
import { useAuthMock } from '@/composables/useAuthMock';
import { dayKey, formatDateTime, formatRupiah, formatRupiahShort } from '@/lib/format';

defineOptions({ layout: DashboardLayout });
const props = defineProps({ penjualan: Array, detailPenjualan: Array });
onMounted(() => hydrate(props));
const store = usePosStore();
const auth = useAuthMock();

const ranges = [{ key: 'today', label: 'Hari ini' }, { key: '7', label: '7 hari' }, { key: '30', label: '30 hari' }, { key: 'all', label: 'Semua' }];
const range = ref('7'), fCara = ref(''), fJenis = ref('');

const inRange = (iso) => {
    if (range.value === 'all') return true;
    const days = range.value === 'today' ? 0 : Number(range.value);
    const d = new Date(iso), now = new Date();
    const diff = Math.floor((new Date(dayKey(now)) - new Date(dayKey(d))) / 86400000);
    return diff >= 0 && diff < (range.value === 'today' ? 1 : days);
};
const filtered = computed(() => store.penjualanAktif.value.filter((t) =>
    inRange(t.tanggal_penjualan) && (!fCara.value || t.cara_bayar === fCara.value) && (!fJenis.value || t.jenis_transaksi === fJenis.value)
).sort((a, b) => +new Date(b.tanggal_penjualan) - +new Date(a.tanggal_penjualan)));

const hpp = (id) => store.detailJual(id).reduce((s, d) => s + d.harga_beli * d.jumlah_barang, 0);
const sumOmzet = computed(() => filtered.value.reduce((s, t) => s + t.total_faktur, 0));
const sumHpp = computed(() => filtered.value.reduce((s, t) => s + hpp(t.id_penjualan), 0));
const summary = computed(() => [
    { label: 'Omzet', value: formatRupiah(sumOmzet.value), sub: `${filtered.value.length} transaksi`, color: '#fb923c' },
    { label: 'Modal (HPP)', value: formatRupiah(sumHpp.value), sub: 'Σ harga_beli × qty', color: '#f87171' },
    { label: 'Laba Kotor', value: formatRupiah(sumOmzet.value - sumHpp.value), sub: `margin ${sumOmzet.value ? Math.round(((sumOmzet.value - sumHpp.value) / sumOmzet.value) * 100) : 0}%`, color: '#34d399' },
    { label: 'Rata-rata / Trx', value: formatRupiah(filtered.value.length ? Math.round(sumOmzet.value / filtered.value.length) : 0), sub: 'average order', color: '#60a5fa' },
    { label: 'Piutang', value: formatRupiah(filtered.value.filter((t) => t.status_pembayaran === 'belum bayar').reduce((s, t) => s + t.total_faktur, 0)), sub: `${filtered.value.filter((t) => t.status_pembayaran === 'belum bayar').length} belum bayar`, color: '#fbbf24' },
]);
const daily = computed(() => {
    const n = range.value === 'all' ? 14 : range.value === 'today' ? 1 : Number(range.value);
    const out = [];
    for (let i = n - 1; i >= 0; i--) {
        const d = new Date(); d.setDate(d.getDate() - i);
        const key = dayKey(d);
        const rows = filtered.value.filter((t) => dayKey(t.tanggal_penjualan) === key);
        const omzet = rows.reduce((s, t) => s + t.total_faktur, 0);
        out.push({ key, label: d.toLocaleDateString('id-ID', { weekday: 'short' }), omzet, laba: omzet - rows.reduce((s, t) => s + hpp(t.id_penjualan), 0) });
    }
    return out;
});
const maxOmzet = computed(() => Math.max(1, ...daily.value.map((d) => d.omzet)));
const pct = (v, m) => Math.max(3, (v / m) * 100);
const byCara = computed(() => {
    const m = {};
    for (const t of filtered.value) { m[t.cara_bayar] = m[t.cara_bayar] || { cara: t.cara_bayar, total: 0, count: 0 }; m[t.cara_bayar].total += t.total_faktur; m[t.cara_bayar].count++; }
    return Object.values(m).sort((a, b) => b.total - a.total);
});
const maxCara = computed(() => Math.max(1, ...byCara.value.map((c) => c.total)));
const topProduk = computed(() => {
    const m = {};
    for (const t of filtered.value) for (const d of store.detailJual(t.id_penjualan)) {
        m[d.id_barang] = m[d.id_barang] || { id: d.id_barang, nama: store.namaBarang(d.id_barang), qty: 0, omzet: 0 };
        m[d.id_barang].qty += d.jumlah_barang; m[d.id_barang].omzet += d.subtotal;
    }
    return Object.values(m).sort((a, b) => b.qty - a.qty);
});
function exportCsv() {
    const rows = [['id', 'tanggal', 'pelanggan', 'cara_bayar', 'jenis', 'omzet', 'hpp', 'laba', 'status']];
    for (const t of filtered.value) rows.push([t.id_penjualan, t.tanggal_penjualan, store.namaPelanggan(t.id_pelanggan), t.cara_bayar, t.jenis_transaksi, t.total_faktur, hpp(t.id_penjualan), t.total_faktur - hpp(t.id_penjualan), t.status_pembayaran]);
    const blob = new Blob([rows.map((r) => r.join(';')).join('\n')], { type: 'text/csv' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob); a.download = 'laporan-penjualan.csv'; a.click();
}
</script>
