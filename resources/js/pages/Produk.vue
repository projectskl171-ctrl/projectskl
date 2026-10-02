<template>
    <RoleDenied v-if="!auth.can('/produk')" page="Produk" :needed="['Admin']" />
    <div v-else class="space-y-4">
        <div class="grid grid-cols-2 gap-4 xl:grid-cols-4">
            <div v-for="s in stats" :key="s.label" class="rounded-xl border border-slate-200 dark:border-white/[0.06] bg-white dark:bg-white/[0.02] p-4">
                <p class="text-[11px] text-slate-500 dark:text-white/40">{{ s.label }}</p><p class="mt-1 text-xl font-black">{{ s.value }}</p>
            </div>
        </div>

        <div class="flex flex-col gap-2 lg:flex-row">
            <input v-model="q" placeholder="Cari nama / barcode…" class="h-10 flex-1 rounded-lg border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-black/40 px-3 text-sm outline-none placeholder:text-slate-400 dark:placeholder:text-white/25" />
            <select v-model="fKat" class="h-10 rounded-lg border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-black/40 px-3 text-sm outline-none">
                <option :value="0">Semua kategori</option>
                <option v-for="k in store.state.kategori" :key="k.id_kategori" :value="k.id_kategori">{{ k.nama }}</option>
            </select>
            <select v-model="fStok" class="h-10 rounded-lg border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-black/40 px-3 text-sm outline-none">
                <option value="">Semua stok</option><option value="tipis">Menipis ≤10</option><option value="habis">Habis</option><option value="aman">Aman</option>
            </select>
            <button @click="baru" class="h-10 cursor-pointer rounded-lg bg-amber-500 px-4 text-sm font-black text-black hover:bg-amber-400">+ Produk</button>
        </div>
        <p v-if="listErr" class="rounded-lg bg-rose-500/10 px-3 py-2 text-[11px] font-bold text-rose-700 dark:text-rose-300">{{ listErr }}</p>

        <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-white/[0.06]">
            <table class="w-full min-w-[820px] text-left text-xs">
                <thead><tr class="bg-white dark:bg-white/[0.03] text-[10px] tracking-wider text-slate-500 dark:text-white/40 uppercase">
                    <th class="px-4 py-2.5">Barang (barcode)</th><th class="px-3 py-2.5">Kategori</th><th class="px-3 py-2.5 text-right">Beli → Jual</th><th class="px-3 py-2.5 text-right">Margin</th><th class="px-3 py-2.5 text-right">Stok</th><th class="px-3 py-2.5 text-right">Aksi</th>
                </tr></thead>
                <tbody>
                    <tr v-for="b in paged" :key="b.id_barang" class="border-t border-slate-200 dark:border-white/[0.05] hover:bg-emerald-600/5 dark:hover:bg-white/[0.02]">
                        <td class="px-4 py-2.5"><p class="font-bold">{{ b.nama }}</p><p class="font-mono text-[10px] text-slate-400 dark:text-white/30">{{ b.barcode }} • {{ b.satuan }} • {{ store.namaSupplier(b.id_supplier) }}</p></td>
                        <td class="px-3 py-2.5"><span class="rounded bg-slate-900/[0.04] dark:bg-white/5 px-1.5 py-0.5 text-[10px] font-bold text-slate-600 dark:text-white/60">{{ store.namaKategori(b.id_kategori) }}</span><p class="mt-0.5 text-[10px] text-slate-400 dark:text-white/30">{{ store.namaKelompok(b.id_kelompok_kategori) }}</p></td>
                        <td class="px-3 py-2.5 text-right whitespace-nowrap text-slate-500 dark:text-white/50">{{ formatRupiah(Number(b.harga_beli)) }} → <span class="font-bold text-slate-900 dark:text-white">{{ formatRupiah(Number(b.harga_jual)) }}</span></td>
                        <td class="px-3 py-2.5 text-right font-bold text-emerald-700 dark:text-emerald-400">{{ margin(b) }}%</td>
                        <td class="px-3 py-2.5 text-right"><span class="rounded-md px-1.5 py-0.5 font-black tabular-nums" :class="Number(b.stok) === 0 ? 'bg-rose-500/15 text-rose-700 dark:text-rose-300' : Number(b.stok) <= 10 ? 'bg-amber-500/15 text-amber-700 dark:text-amber-300' : 'bg-emerald-500/15 text-emerald-700 dark:text-emerald-300'">{{ b.stok }}</span></td>
                        <td class="px-3 py-2.5 text-right whitespace-nowrap">
                            <button @click="edit(b)" class="cursor-pointer rounded-md bg-slate-900/[0.04] dark:bg-white/5 px-2 py-1 text-[11px] font-bold hover:bg-slate-900/5 dark:hover:bg-white/10">Edit</button>
                            <button @click="mintaHapus(b)" class="ml-1 cursor-pointer rounded-md bg-slate-900/[0.04] dark:bg-white/5 px-2 py-1 text-[11px] font-bold text-rose-700 dark:text-rose-300 hover:bg-rose-500/20">Hapus</button>
                        </td>
                    </tr>
                </tbody>
            </table>
            <p v-if="!filtered.length" class="py-10 text-center text-sm text-slate-400 dark:text-white/30">Tidak ada produk.</p>
        </div>
        <Pagination :page="page" :total-pages="totalPages" @update:page="page = $event" />
    </div>

    <div v-if="show && auth.can('/produk')" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4 backdrop-blur-sm">
        <div class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-[#111] p-5">
            <h3 class="text-sm font-black">{{ form.id_barang ? 'Edit Produk' : 'Produk Baru' }}</h3>
            <div class="mt-4 grid grid-cols-2 gap-2 text-xs">
                <label class="col-span-2 text-slate-500 dark:text-white/50">Nama barang<input v-model="form.nama" class="mt-1 h-10 w-full rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-black/50 px-3 text-sm text-slate-900 dark:text-white outline-none" /></label>
                <label class="text-slate-500 dark:text-white/50">Barcode<input v-model="form.barcode" class="mt-1 h-10 w-full rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-black/50 px-3 font-mono text-sm text-slate-900 dark:text-white outline-none" /></label>
                <label class="text-slate-500 dark:text-white/50">Satuan
                    <select v-model="form.satuan" class="mt-1 h-10 w-full rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-black/50 px-2 text-sm text-slate-900 dark:text-white outline-none"><option>pcs</option><option>porsi</option><option>botol</option><option>cup</option><option>kotak</option><option>pak</option></select></label>
                <label class="text-slate-500 dark:text-white/50">Kategori
                    <select v-model="form.id_kategori" class="mt-1 h-10 w-full rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-black/50 px-2 text-sm text-slate-900 dark:text-white outline-none"><option v-for="k in store.state.kategori" :key="k.id_kategori" :value="k.id_kategori">{{ k.nama }}</option></select></label>
                <label class="text-slate-500 dark:text-white/50">Kelompok
                    <select v-model="form.id_kelompok_kategori" class="mt-1 h-10 w-full rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-black/50 px-2 text-sm text-slate-900 dark:text-white outline-none"><option v-for="k in store.state.kelompok" :key="k.id_kelompok" :value="k.id_kelompok">{{ k.nama_kelompok }}</option></select></label>
                <label class="col-span-2 text-slate-500 dark:text-white/50">Supplier
                    <select v-model="form.id_supplier" class="mt-1 h-10 w-full rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-black/50 px-2 text-sm text-slate-900 dark:text-white outline-none"><option v-for="s in store.supplierAktif.value" :key="s.id_supplier" :value="s.id_supplier">{{ s.nama }}</option></select></label>
                <label class="text-slate-500 dark:text-white/50">Harga beli<input v-model.number="form.harga_beli" type="number" min="0" class="mt-1 h-10 w-full rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-black/50 px-3 text-sm text-slate-900 dark:text-white outline-none" /></label>
                <label class="text-slate-500 dark:text-white/50">Harga jual<input v-model.number="form.harga_jual" type="number" min="0" class="mt-1 h-10 w-full rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-black/50 px-3 text-sm text-slate-900 dark:text-white outline-none" /></label>
                <label class="text-slate-500 dark:text-white/50">Stok<input v-model.number="form.stok" type="number" min="0" class="mt-1 h-10 w-full rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-black/50 px-3 text-sm text-slate-900 dark:text-white outline-none" /></label>
                <label class="flex items-end gap-2 pb-2 text-slate-500 dark:text-white/50"><input v-model="form.is_active" type="checkbox" :true-value="1" :false-value="0" class="h-4 w-4 accent-emerald-500" /> Aktif dijual</label>
            </div>
            <p class="mt-2 text-xs text-slate-500 dark:text-white/40">Margin: <span class="font-black text-emerald-700 dark:text-emerald-400">{{ form.harga_beli ? Math.round(((form.harga_jual - form.harga_beli) / form.harga_beli) * 100) : 0 }}%</span></p>
            <p v-if="ferr" class="mt-2 rounded-lg bg-rose-500/10 px-3 py-2 text-[11px] font-bold text-rose-700 dark:text-rose-300">{{ ferr }}</p>
            <div class="mt-4 flex gap-2"><button @click="show = false" class="h-10 flex-1 cursor-pointer rounded-lg bg-slate-900/[0.04] dark:bg-white/5 text-sm font-bold">Batal</button>
            <button @click="simpan" :disabled="saving" class="h-10 flex-1 cursor-pointer rounded-lg bg-amber-500 text-sm font-black text-black hover:bg-amber-400 disabled:opacity-50">{{ saving ? 'Menyimpan…' : 'Simpan' }}</button></div>
        </div>
    </div>

    <!-- WARNING HAPUS: hanya stok 0, realtime hapus di kasir, riwayat aman -->
    <div v-if="showDel" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4 backdrop-blur-sm">
        <div class="w-full max-w-md rounded-xl border border-rose-500/30 bg-white dark:bg-[#111] p-5">
            <h3 class="text-sm font-black text-rose-700 dark:text-rose-300">⚠️ Hapus produk?</h3>
            <p class="mt-2 text-xs leading-relaxed text-slate-600 dark:text-white/70">
                <b>{{ delTarget?.nama }}</b> (stok: <b>{{ delTarget?.stok }}</b>, barcode: <span class="font-mono">{{ delTarget?.barcode }}</span>) akan dihapus.
            </p>
            <ul class="mt-2 list-disc space-y-1 pl-5 text-[11px] text-slate-500 dark:text-white/50">
                <li>Hanya bisa dihapus karena stok <b>0</b>.</li>
                <li>Secara <b>REALTIME</b> produk hilang dari katalog kasir.</li>
                <li><b>Riwayat transaksi TIDAK terpengaruh</b> (detail lama tetap tersimpan).</li>
            </ul>
            <p v-if="delErr" class="mt-2 rounded-lg bg-rose-500/10 px-3 py-2 text-[11px] font-bold text-rose-700 dark:text-rose-300">{{ delErr }}</p>
            <div class="mt-4 flex gap-2">
                <button @click="showDel = false" class="h-10 flex-1 cursor-pointer rounded-lg bg-slate-900/[0.04] dark:bg-white/5 text-sm font-bold">Batal</button>
                <button @click="hapusFix" :disabled="deleting" class="h-10 flex-1 cursor-pointer rounded-lg bg-rose-500 text-sm font-black text-white hover:bg-rose-400 disabled:opacity-50">{{ deleting ? 'Menghapus…' : 'Ya, Hapus' }}</button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import Pagination from '@/components/Pagination.vue';
import { usePagination } from '@/composables/usePagination';
import { hydrate, mergeBarangRealtime, usePosStore } from '@/composables/usePosStore';
import RoleDenied from '@/components/RoleDenied.vue';
import { useAuthMock } from '@/composables/useAuthMock';
import { formatRupiah, formatRupiahShort } from '@/lib/format';
import { apiFetch } from '@/lib/api';

defineOptions({ layout: DashboardLayout });
const props = defineProps({ barang: Array, kategori: Array, kelompok: Array, supplier: Array, sekolah: Array });
onMounted(() => {
    hydrate(props);
    loadBarang();
});
const store = usePosStore();
const auth = useAuthMock();
const q = ref(''), fKat = ref(0), fStok = ref(''), show = ref(false);
const form = ref({});
const ferr = ref(''), saving = ref(false), listErr = ref('');
const showDel = ref(false), delTarget = ref(null), delErr = ref(''), deleting = ref(false);

const { page, totalPages, paged, filtered } = usePagination(() => store.barangAktif.value.filter((b) => {
    const okQ = !q.value || b.nama.toLowerCase().includes(q.value.toLowerCase()) || (b.barcode || '').includes(q.value);
    const okK = !fKat.value || Number(b.id_kategori) === Number(fKat.value);
    const stok = Number(b.stok);
    const okS = !fStok.value || (fStok.value === 'habis' && stok === 0) || (fStok.value === 'tipis' && stok > 0 && stok <= 10) || (fStok.value === 'aman' && stok > 10);
    return okQ && okK && okS;
}).sort((a, b) => a.nama.localeCompare(b.nama)), 15, [q, fKat, fStok]);
const margin = (b) => Number(b.harga_beli) ? Math.round(((Number(b.harga_jual) - Number(b.harga_beli)) / Number(b.harga_beli)) * 100) : 0;
const stats = computed(() => [
    { label: 'Total SKU', value: store.barangAktif.value.length },
    { label: 'Nilai Stok (HPP)', value: formatRupiahShort(store.barangAktif.value.reduce((s, b) => s + Number(b.stok) * Number(b.harga_beli), 0)) },
    { label: 'Stok Menipis ≤10', value: store.barangAktif.value.filter((b) => Number(b.stok) <= 10).length },
    { label: 'Nonaktif', value: store.barangAktif.value.filter((b) => !Number(b.is_active)).length },
]);

function extractRows(json) {
    if (Array.isArray(json)) return json;
    if (Array.isArray(json?.data)) return json.data;
    if (Array.isArray(json?.data?.data)) return json.data.data;
    return [];
}
async function loadBarang() {
    try {
        const json = await apiFetch('/api/produk?per_page=500');
        const rows = extractRows(json);
        if (rows.length || json?.data) {
            mergeBarangRealtime(rows);
            listErr.value = '';
        }
    } catch (e) {
        listErr.value = '';
    }
}
function baru() {
    ferr.value = '';
    form.value = {
        nama: '', barcode: '', satuan: 'pcs',
        id_kategori: store.state.kategori[0]?.id_kategori ?? null,
        id_kelompok_kategori: store.state.kelompok[0]?.id_kelompok ?? null,
        id_supplier: store.supplierAktif.value[0]?.id_supplier ?? null,
        harga_beli: 0, harga_jual: 0, stok: 0, is_active: 1,
    };
    show.value = true;
}
function edit(b) { ferr.value = ''; form.value = { ...b }; show.value = true; }
async function simpan() {
    ferr.value = '';
    if (!form.value.nama?.trim()) { ferr.value = 'Nama barang wajib diisi.'; return; }
    if (!form.value.barcode?.trim()) { ferr.value = 'Barcode wajib diisi.'; return; }
    saving.value = true;
    const payload = {
        nama: form.value.nama.trim(),
        barcode: form.value.barcode.trim(),
        satuan: form.value.satuan || 'pcs',
        id_kategori: Number(form.value.id_kategori),
        id_kelompok_kategori: Number(form.value.id_kelompok_kategori),
        id_supplier: Number(form.value.id_supplier),
        harga_beli: Number(form.value.harga_beli) || 0,
        harga_jual: Number(form.value.harga_jual) || 0,
        stok: Math.max(0, Math.floor(Number(form.value.stok) || 0)),
        is_active: form.value.is_active ? 1 : 0,
    };
    try {
        let res;
        if (form.value.id_barang) {
            res = await apiFetch(`/api/produk/${form.value.id_barang}`, { method: 'PUT', body: JSON.stringify(payload) });
        } else {
            res = await apiFetch('/api/produk', { method: 'POST', body: JSON.stringify(payload) });
        }
        const row = res?.data ?? null;
        if (row) mergeBarangRealtime([...extractRows({ data: store.barangAktif.value }), row]);
        await loadBarang();
        show.value = false;
    } catch (e) {
        ferr.value = e?.errors ? Object.values(e.errors).flat().join(' ') : (e?.message || 'Gagal menyimpan produk.');
    } finally {
        saving.value = false;
    }
}
function mintaHapus(b) {
    delErr.value = '';
    if (Number(b.stok) > 0) {
        listErr.value = `Tidak bisa hapus "${b.nama}": stok masih ${b.stok}. Hapus hanya boleh saat stok 0.`;
        setTimeout(() => { listErr.value = ''; }, 4000);
        return;
    }
    delTarget.value = b;
    showDel.value = true;
}
async function hapusFix() {
    if (!delTarget.value) return;
    deleting.value = true;
    delErr.value = '';
    try {
        await apiFetch(`/api/produk/${delTarget.value.id_barang}`, { method: 'DELETE' });
        await loadBarang();
        showDel.value = false;
        delTarget.value = null;
    } catch (e) {
        delErr.value = e?.errors?.stok?.[0] || e?.message || 'Gagal menghapus produk.';
    } finally {
        deleting.value = false;
    }
}
</script>
