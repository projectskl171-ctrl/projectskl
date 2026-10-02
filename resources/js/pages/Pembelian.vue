<template>
    <RoleDenied v-if="!auth.can('/pembelian')" page="Pembelian" :needed="['Admin']" />
    <div v-else class="space-y-4">
        <div class="grid grid-cols-2 gap-4 xl:grid-cols-4">
            <div v-for="s in stats" :key="s.label" class="rounded-xl border border-slate-200 dark:border-white/[0.06] bg-white dark:bg-white/[0.02] p-4 shadow-sm dark:shadow-none">
                <p class="text-[11px] text-slate-500 dark:text-white/40">{{ s.label }}</p>
                <p class="mt-1 text-xl font-black">{{ s.value }}</p>
            </div>
        </div>

        <div class="flex flex-col gap-2 sm:flex-row">
            <input v-model="q" placeholder="Cari no. faktur / supplier…" class="h-10 flex-1 rounded-lg border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-black/40 px-3 text-sm outline-none placeholder:text-slate-400 dark:placeholder:text-white/25" />
            <select v-model="status" class="h-10 rounded-lg border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-black/40 px-3 text-sm outline-none">
                <option value="">Semua status</option><option value="draft">Draft</option><option value="selesai">Selesai</option>
            </select>
            <button @click="showForm = true" class="h-10 cursor-pointer rounded-lg bg-violet-500 px-4 text-sm font-black text-white hover:bg-violet-400">+ Pembelian Baru</button>
        </div>
        <p v-if="listErr" class="rounded-lg bg-rose-500/10 px-3 py-2 text-[11px] font-bold text-rose-700 dark:text-rose-300">{{ listErr }}</p>

        <div class="space-y-2">
            <div v-for="p in paged" :key="p.id_pembelian" class="rounded-xl border border-slate-200 dark:border-white/[0.06] bg-white dark:bg-white/[0.02] shadow-sm dark:shadow-none">
                <button @click="openId = openId === p.id_pembelian ? 0 : p.id_pembelian" class="flex w-full cursor-pointer flex-wrap items-center gap-2 px-4 py-3 text-left text-xs">
                    <span class="font-mono font-black">{{ p.nomor_faktur }}</span>
                    <span class="text-slate-500 dark:text-white/40">{{ store.namaSupplier(p.id_supplier) }} • {{ formatDate(p.tanggal_faktur) }}</span>
                    <span class="rounded px-1.5 py-0.5 text-[10px] font-black" :class="p.status_pembelian === 'selesai' ? 'bg-emerald-500/15 text-emerald-700 dark:text-emerald-300' : 'bg-amber-500/15 text-amber-700 dark:text-amber-300'">{{ p.status_pembelian }}</span>
                    <span class="rounded bg-slate-900/[0.04] dark:bg-white/5 px-1.5 py-0.5 text-[10px] font-bold text-slate-500 dark:text-white/50">{{ p.jenis_transaksi }} • {{ p.cara_bayar }}</span>
                    <span class="ml-auto font-black">{{ formatRupiah(Number(p.total_bayar)) }}</span>
                </button>
                <div v-if="openId === p.id_pembelian" class="border-t border-slate-200 dark:border-white/[0.06] px-4 py-3 text-xs">
                    <p v-if="p.note" class="mb-2 text-slate-500 dark:text-white/40">📝 {{ p.note }}</p>
                    <table class="w-full text-left">
                        <thead><tr class="text-[10px] tracking-wider text-slate-400 dark:text-white/30 uppercase"><th class="py-1">Barang</th><th class="py-1 text-right">Qty</th><th class="py-1 text-right">Harga</th><th class="py-1 text-right">Subtotal</th></tr></thead>
                        <tbody>
                            <tr v-for="d in store.detailBeli(p.id_pembelian)" :key="d.id_detail_pembelian" class="border-t border-slate-200 dark:border-white/5">
                                <td class="py-1.5 font-bold">{{ store.namaBarang(d.id_barang) }}</td>
                                <td class="py-1.5 text-right">{{ d.jumlah }}</td>
                                <td class="py-1.5 text-right text-slate-500 dark:text-white/50">{{ formatRupiah(Number(d.harga_beli)) }}</td>
                                <td class="py-1.5 text-right font-bold">{{ formatRupiah(Number(d.subtotal)) }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="mt-3 flex gap-2">
                        <button v-if="p.status_pembelian === 'draft'" @click="selesaikan(p)" class="h-9 cursor-pointer rounded-lg bg-emerald-500 px-4 text-xs font-black text-white hover:bg-emerald-400">✓ Tandai Selesai (stok +)</button>
                        <button v-if="p.status_pembelian === 'draft'" @click="hapusDraft(p)" class="h-9 cursor-pointer rounded-lg bg-slate-900/[0.04] dark:bg-white/5 px-4 text-xs font-bold text-rose-700 dark:text-rose-300 hover:bg-rose-500/20">Hapus draft</button>
                        <span v-else class="text-[11px] text-slate-400 dark:text-white/30">Stok sudah masuk saat pembelian diselesaikan.</span>
                    </div>
                </div>
            </div>
            <p v-if="!filtered.length" class="rounded-xl border border-dashed border-slate-200 dark:border-white/10 py-10 text-center text-sm text-slate-400 dark:text-white/30">Tidak ada pembelian.</p>
        </div>
        <Pagination :page="page" :total-pages="totalPages" @update:page="page = $event" />
    </div>

    <!-- FORM -->
    <div v-if="showForm && auth.can('/pembelian')" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4 backdrop-blur-sm">
        <div class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-[#111] p-5 shadow-sm dark:shadow-none">
            <h3 class="text-sm font-black">Pembelian Baru</h3>
            <div class="mt-4 grid grid-cols-2 gap-2">
                <label class="col-span-2 text-xs text-slate-500 dark:text-white/50">Supplier
                    <select v-model="f.supplier" class="mt-1 h-10 w-full rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-black/50 px-3 text-sm text-slate-900 dark:text-white outline-none">
                        <option v-for="s in store.supplierAktif.value" :key="s.id_supplier" :value="s.id_supplier">{{ s.nama }}</option>
                    </select></label>
                <label class="text-xs text-slate-500 dark:text-white/50">Jenis
                    <select v-model="f.jenis" class="mt-1 h-10 w-full rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-black/50 px-2 text-sm text-slate-900 dark:text-white outline-none"><option value="tunai">Tunai</option><option value="kredit">Kredit</option></select></label>
                <label class="text-xs text-slate-500 dark:text-white/50">Cara bayar
                    <select v-model="f.cara" class="mt-1 h-10 w-full rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-black/50 px-2 text-sm text-slate-900 dark:text-white outline-none"><option>Transfer</option><option>Tunai</option><option>Tempo 14 hari</option></select></label>
                <label class="col-span-2 text-xs text-slate-500 dark:text-white/50">Catatan
                    <input v-model="f.note" class="mt-1 h-10 w-full rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-black/50 px-3 text-sm outline-none" placeholder="Keterangan pembelian" /></label>
            </div>
            <div class="mt-3 space-y-2">
                <div v-for="(l, i) in f.lines" :key="i" class="flex gap-2">
                    <select v-model="l.barang" class="h-10 flex-1 rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-black/50 px-2 text-xs outline-none">
                        <option v-for="b in store.barangAktif.value" :key="b.id_barang" :value="b.id_barang">{{ b.nama }} (Rp{{ b.harga_beli }}) • stok {{ b.stok }}</option>
                    </select>
                    <input v-model.number="l.jumlah" type="number" min="1" class="h-10 w-16 rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-black/50 px-2 text-right text-sm outline-none" />
                    <input v-model.number="l.harga" type="number" min="0" class="h-10 w-28 rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-black/50 px-2 text-right text-sm outline-none" />
                    <button @click="f.lines.splice(i, 1)" class="cursor-pointer text-rose-600 dark:text-rose-400">✕</button>
                </div>
                <button @click="tambahBaris" class="h-9 w-full cursor-pointer rounded-lg bg-slate-900/[0.04] dark:bg-white/5 text-xs font-bold text-slate-600 dark:text-white/60 hover:bg-slate-900/5 dark:hover:bg-white/10">+ Tambah baris</button>
            </div>
            <p class="mt-3 text-right text-sm font-black">Total: <span class="text-violet-700 dark:text-violet-300">{{ formatRupiah(formTotal) }}</span></p>
            <p v-if="ferr" class="mt-2 rounded-lg bg-rose-500/10 px-3 py-2 text-[11px] font-bold text-rose-700 dark:text-rose-300">{{ ferr }}</p>
            <div class="mt-4 flex flex-col gap-2 sm:flex-row">
                <button @click="showForm = false" class="h-10 flex-1 cursor-pointer rounded-lg bg-slate-900/[0.04] dark:bg-white/5 text-sm font-bold">Batal</button>
                <button @click="simpan(false)" :disabled="saving" class="h-10 flex-1 cursor-pointer rounded-lg bg-slate-900/[0.06] dark:bg-white/10 text-sm font-black text-slate-900 dark:text-white hover:bg-slate-900/10 dark:hover:bg-white/15 disabled:opacity-50">{{ saving ? 'Menyimpan…' : 'Simpan Draft' }}</button>
                <button @click="simpan(true)" :disabled="saving" class="h-10 flex-1 cursor-pointer rounded-lg bg-violet-500 text-sm font-black text-white hover:bg-violet-400 disabled:opacity-50">{{ saving ? 'Menyimpan…' : 'Simpan & Selesaikan' }}</button>
            </div>
            <p class="mt-2 text-[10px] leading-relaxed text-slate-400 dark:text-white/30">Draft = stok belum masuk. Selesaikan = stok langsung bertambah.</p>
        </div>
    </div>

    <ConfirmModal :show="cf.show" :title="cf.title" :message="cf.message" :confirm-label="cf.confirmLabel" :danger="cf.danger" :loading="cf.loading" @cancel="cf.show = false" @confirm="cfRun" />
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import ConfirmModal from '@/components/ConfirmModal.vue';
import Pagination from '@/components/Pagination.vue';
import { usePagination } from '@/composables/usePagination';
import { hydrate, mergeBarangRealtime, replaceCollection, usePosStore } from '@/composables/usePosStore';
import RoleDenied from '@/components/RoleDenied.vue';
import { useAuthMock } from '@/composables/useAuthMock';
import { formatDate, formatRupiah } from '@/lib/format';
import { apiFetch } from '@/lib/api';

defineOptions({ layout: DashboardLayout });
const props = defineProps({ pembelian: Array, detailPembelian: Array, supplier: Array, barang: Array });
onMounted(() => {
    hydrate(props);
    loadAll();
});
const store = usePosStore();
const auth = useAuthMock();

const q = ref(''), status = ref(''), openId = ref(0), showForm = ref(false), ferr = ref(''), listErr = ref(''), saving = ref(false);
const f = ref({ supplier: 1, jenis: 'tunai', cara: 'Transfer', note: '', lines: [{ barang: 1, jumlah: 10, harga: 5000 }] });

const { page, totalPages, paged, filtered } = usePagination(() => store.pembelianAktif.value.filter((p) => {
    const okQ = !q.value || (p.nomor_faktur || '').toLowerCase().includes(q.value.toLowerCase()) || store.namaSupplier(p.id_supplier).toLowerCase().includes(q.value.toLowerCase());
    return okQ && (!status.value || p.status_pembelian === status.value);
}).sort((a, b) => b.id_pembelian - a.id_pembelian), 8, [q, status]);

const stats = computed(() => [
    { label: 'Total Pembelian', value: formatRupiah(store.pembelianAktif.value.reduce((s, p) => s + Number(p.total_bayar), 0)) },
    { label: 'Draft', value: String(store.pembelianAktif.value.filter((p) => p.status_pembelian === 'draft').length) },
    { label: 'Selesai', value: String(store.pembelianAktif.value.filter((p) => p.status_pembelian === 'selesai').length) },
    { label: 'Supplier Aktif', value: String(store.supplierAktif.value.length) },
]);
const formTotal = computed(() => f.value.lines.reduce((s, l) => s + (Number(l.jumlah) || 0) * (Number(l.harga) || 0), 0));
function extractRows(json) {
    if (Array.isArray(json)) return json;
    if (Array.isArray(json?.data)) return json.data;
    if (Array.isArray(json?.data?.data)) return json.data.data;
    return [];
}
async function loadAll() {
    try {
        const [pb, sup, brg] = await Promise.all([
            apiFetch('/api/pembelian?per_page=200').catch(() => null),
            apiFetch('/api/supplier?per_page=200').catch(() => null),
            apiFetch('/api/produk?per_page=500').catch(() => null),
        ]);
        if (pb) {
            const rows = extractRows(pb);
            if (rows.length || pb?.data) replaceCollection('pembelian', rows);
            // detail pembelian tidak ada endpoint list-all: ambil per header selesai? cukup pakai hydrate awal + refresh per show
            // Untuk realtime stok kasir, cukup sinkron barang.
        }
        if (sup) {
            const rows = extractRows(sup);
            if (rows.length || sup?.data) replaceCollection('supplier', rows);
        }
        if (brg) {
            const rows = extractRows(brg);
            if (rows.length || brg?.data) mergeBarangRealtime(rows);
        }
        // set default form dari data DB asli
        if (store.supplierAktif.value.length) f.value.supplier = store.supplierAktif.value[0].id_supplier;
        if (store.barangAktif.value.length) {
            const b0 = store.barangAktif.value[0];
            f.value.lines = [{ barang: b0.id_barang, jumlah: 10, harga: Number(b0.harga_beli) || 5000 }];
        }
    } catch { /* abaikan */ }
}
async function loadPembelianDetail(id) {
    try {
        const res = await apiFetch(`/api/pembelian/${id}`);
        const d = res?.data;
        if (d?.details) {
            // sinkron detail ke store (ganti yang lama untuk id ini)
            const others = store.state.detailPembelian.filter((x) => Number(x.id_pembelian) !== Number(id));
            const mapped = d.details.map((x) => ({ id_detail_pembelian: x.id_detail_pembelian, id_pembelian: Number(id), id_barang: x.id_barang, satuan: x.satuan, jumlah: x.jumlah, harga_beli: x.harga_beli, subtotal: x.subtotal }));
            replaceCollection('detailPembelian', [...others, ...mapped]);
            const idx = store.state.pembelian.findIndex((p) => Number(p.id_pembelian) === Number(id));
            if (idx >= 0) store.state.pembelian[idx] = { ...store.state.pembelian[idx], ...d, details: undefined };
        }
    } catch { /* abaikan */ }
}
function tambahBaris() { f.value.lines.push({ barang: store.barangAktif.value[0]?.id_barang ?? 1, jumlah: 10, harga: Number(store.barangAktif.value[0]?.harga_beli) || 5000 }); }
async function simpan(langsungSelesai = false) {
    ferr.value = '';
    if (!f.value.lines.length) { ferr.value = 'Tambahkan minimal 1 baris barang.'; return; }
    saving.value = true;
    try {
        const res = await apiFetch('/api/pembelian', {
            method: 'POST',
            body: JSON.stringify({
                id_supplier: Number(f.value.supplier),
                jenis_transaksi: f.value.jenis,
                cara_bayar: f.value.cara,
                note: f.value.note,
                lines: f.value.lines.map((l) => ({ id_barang: Number(l.barang), jumlah: Math.max(1, Math.floor(Number(l.jumlah) || 1)), harga_beli: Number(l.harga) || 0 })),
            }),
        });
        const idBaru = res?.data?.id_pembelian;
        if (langsungSelesai && idBaru) {
            await apiFetch(`/api/pembelian/${idBaru}/selesai`, { method: 'POST' });
            await loadPembelianDetail(idBaru);
        }
        await loadAll();
        showForm.value = false;
        f.value = { supplier: store.supplierAktif.value[0]?.id_supplier ?? 1, jenis: 'tunai', cara: 'Transfer', note: '', lines: [{ barang: store.barangAktif.value[0]?.id_barang ?? 1, jumlah: 10, harga: 5000 }] };
    } catch (e) {
        ferr.value = e?.errors ? Object.values(e.errors).flat().join(' ') : (e?.message || 'Gagal menyimpan pembelian.');
    } finally {
        saving.value = false;
    }
}
const cf = ref({ show: false, title: '', message: '', confirmLabel: 'Ya', danger: false, loading: false, run: null });
async function cfRun() {
    const fn = cf.value.run;
    if (!fn) { cf.value.show = false; return; }
    cf.value.loading = true;
    try {
        await fn();
        cf.value.show = false;
    } catch (e) {
        cf.value.show = false;
        listErr.value = e?.message || 'Aksi gagal.';
        setTimeout(() => { listErr.value = ''; }, 4000);
    } finally {
        cf.value.loading = false;
    }
}
function selesaikan(p) {
    cf.value = {
        show: true, title: 'Selesaikan pembelian?', message: `Selesaikan ${p.nomor_faktur} (${formatRupiah(Number(p.total_bayar))})? Stok akan bertambah & tidak bisa dibatalkan.`,
        confirmLabel: 'Selesaikan', danger: false, loading: false,
        run: async () => {
            await apiFetch(`/api/pembelian/${p.id_pembelian}/selesai`, { method: 'POST' });
            await loadAll();
            await loadPembelianDetail(p.id_pembelian);
        },
    };
}
function hapusDraft(p) {
    cf.value = {
        show: true, title: 'Hapus draft?', message: `Hapus draft ${p.nomor_faktur}? Stok tidak berubah.`,
        confirmLabel: 'Hapus', danger: true, loading: false,
        run: async () => {
            await apiFetch(`/api/pembelian/${p.id_pembelian}`, { method: 'DELETE' });
            await loadAll();
        },
    };
}
</script>
