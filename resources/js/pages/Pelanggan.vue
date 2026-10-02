<template>
    <RoleDenied v-if="!auth.can('/pelanggan')" page="Pelanggan" :needed="['Kasir']" />
    <div v-else class="space-y-4">
        <div class="grid grid-cols-3 gap-4">
            <div v-for="s in stats" :key="s.label" class="rounded-xl border border-slate-200 dark:border-white/[0.06] bg-white dark:bg-white/[0.02] p-4">
                <p class="text-[11px] text-slate-500 dark:text-white/40">{{ s.label }}</p><p class="mt-1 text-xl font-black">{{ s.value }}</p>
            </div>
        </div>
        <div class="flex flex-col gap-2 sm:flex-row">
            <input v-model="q" placeholder="Cari nama / telepon…" class="h-10 flex-1 rounded-lg border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-black/40 px-3 text-sm outline-none placeholder:text-slate-400 dark:placeholder:text-white/25" />
            <select v-model="fKel" class="h-10 rounded-lg border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-black/40 px-3 text-sm outline-none">
                <option :value="0">Semua kelompok</option>
                <option v-for="k in kelompokList" :key="k.id_kelompok_pelanggan" :value="k.id_kelompok_pelanggan">{{ k.nama_kelompok }}</option>
            </select>
            <button @click="baru" class="h-10 cursor-pointer rounded-lg bg-pink-500 px-4 text-sm font-black text-white hover:bg-pink-400">+ Pelanggan</button>
        </div>
        <p v-if="listErr" class="rounded-lg bg-rose-500/10 px-3 py-2 text-[11px] font-bold text-rose-700 dark:text-rose-300">{{ listErr }}</p>
        <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-3">
            <div v-for="p in paged" :key="p.id_pelanggan" class="rounded-xl border border-slate-200 dark:border-white/[0.06] bg-white dark:bg-white/[0.02] p-4">
                <div class="flex items-start justify-between gap-2">
                    <div class="flex items-center gap-2.5">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-pink-500/15 text-sm font-black text-pink-700 dark:text-pink-300">{{ (p.nama_pelanggan || '?').slice(0, 1).toUpperCase() }}</div>
                        <div><p class="text-sm font-bold">{{ p.nama_pelanggan }}</p>
                        <p class="text-[11px] text-slate-500 dark:text-white/40">{{ store.namaKelompokPelanggan(p.id_kelompok_pelanggan) }}</p></div>
                    </div>
                    <div class="flex gap-1">
                        <button @click="edit(p)" class="cursor-pointer rounded-md bg-slate-900/[0.04] dark:bg-white/5 px-2 py-1 text-[11px] font-bold hover:bg-slate-900/5 dark:hover:bg-white/10">Edit</button>
                        <button @click="hapus(p)" class="cursor-pointer rounded-md bg-slate-900/[0.04] dark:bg-white/5 px-2 py-1 text-[11px] font-bold text-rose-700 dark:text-rose-300 hover:bg-rose-500/20">✕</button>
                    </div>
                </div>
                <p class="mt-3 text-xs text-slate-500 dark:text-white/50">📞 {{ p.telepon || '—' }}</p>
                <p class="mt-0.5 line-clamp-1 text-xs text-slate-400 dark:text-white/30">📍 {{ p.alamat || '—' }}</p>
                <div class="mt-3 flex items-center justify-between border-t border-slate-200 dark:border-white/[0.06] pt-2.5 text-[11px]">
                    <span class="text-slate-500 dark:text-white/40">{{trxCount(p.id_pelanggan)}} transaksi</span>
                    <span class="font-black text-emerald-700 dark:text-emerald-400">{{ formatRupiahShort(belanja(p.id_pelanggan)) }}</span>
                </div>
            </div>
        </div>
        <p v-if="!filtered.length" class="rounded-xl border border-dashed border-slate-200 dark:border-white/10 py-10 text-center text-sm text-slate-400 dark:text-white/30">Tidak ada pelanggan.</p>
        <Pagination :page="page" :total-pages="totalPages" @update:page="page = $event" />
    </div>

    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4 backdrop-blur-sm">
        <div class="w-full max-w-md rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-[#111] p-5">
            <h3 class="text-sm font-black">{{ form.id_pelanggan ? 'Edit Pelanggan' : 'Pelanggan Baru' }}</h3>
            <div class="mt-4 space-y-2 text-xs">
                <label class="block text-slate-500 dark:text-white/50">Nama<input v-model="form.nama_pelanggan" class="mt-1 h-10 w-full rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-black/50 px-3 text-sm text-slate-900 dark:text-white outline-none" /></label>
                <div class="grid grid-cols-2 gap-2">
                    <label class="block text-slate-500 dark:text-white/50">Kelompok
                        <select v-model="form.id_kelompok_pelanggan" class="mt-1 h-10 w-full rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-black/50 px-2 text-sm text-slate-900 dark:text-white outline-none"><option v-for="k in kelompokList" :key="k.id_kelompok_pelanggan" :value="k.id_kelompok_pelanggan">{{ k.nama_kelompok }}</option></select></label>
                    <label class="block text-slate-500 dark:text-white/50">Telepon<input v-model="form.telepon" class="mt-1 h-10 w-full rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-black/50 px-3 text-sm text-slate-900 dark:text-white outline-none" /></label>
                </div>
                <label class="block text-slate-500 dark:text-white/50">Alamat<textarea v-model="form.alamat" rows="2" class="mt-1 w-full rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-black/50 px-3 py-2 text-sm text-slate-900 dark:text-white outline-none"></textarea></label>
            </div>
            <p v-if="ferr" class="mt-2 rounded-lg bg-rose-500/10 px-3 py-2 text-[11px] font-bold text-rose-700 dark:text-rose-300">{{ ferr }}</p>
            <div class="mt-4 flex gap-2"><button @click="show = false" class="h-10 flex-1 cursor-pointer rounded-lg bg-slate-900/[0.04] dark:bg-white/5 text-sm font-bold">Batal</button>
            <button @click="simpan" :disabled="saving" class="h-10 flex-1 cursor-pointer rounded-lg bg-pink-500 text-sm font-black text-white hover:bg-pink-400 disabled:opacity-50">{{ saving ? 'Menyimpan…' : 'Simpan' }}</button></div>
        </div>
    </div>

    <ConfirmModal :show="cf.show" :title="cf.title" :message="cf.message" :confirm-label="cf.confirmLabel" :danger="cf.danger" :loading="cf.loading" @cancel="cf.show = false" @confirm="cfRun" />
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import RoleDenied from '@/components/RoleDenied.vue';
import ConfirmModal from '@/components/ConfirmModal.vue';
import Pagination from '@/components/Pagination.vue';
import { usePagination } from '@/composables/usePagination';
import { hydrate, replaceCollection, usePosStore } from '@/composables/usePosStore';
import { useAuthMock } from '@/composables/useAuthMock';
import { formatRupiahShort } from '@/lib/format';
import { apiFetch } from '@/lib/api';

defineOptions({ layout: DashboardLayout });
const props = defineProps({ pelanggan: Array, kelompokPelanggan: Array });
onMounted(() => {
    hydrate(props);
    loadKelompok();
    loadPelanggan();
});
const store = usePosStore();
const auth = useAuthMock();
const q = ref(''), fKel = ref(0), show = ref(false), form = ref({});
const ferr = ref(''), saving = ref(false), listErr = ref('');

const kelompokList = computed(() => store.state.kelompokPelanggan.length ? store.state.kelompokPelanggan : []);
const { page, totalPages, paged, filtered } = usePagination(() => store.pelangganAktif.value.filter((p) => {
    const okQ = !q.value || p.nama_pelanggan.toLowerCase().includes(q.value.toLowerCase()) || (p.telepon || '').includes(q.value);
    return okQ && (!fKel.value || Number(p.id_kelompok_pelanggan) === Number(fKel.value));
}), 9, [q, fKel]);
const trxCount = (id) => store.penjualanAktif.value.filter((t) => Number(t.id_pelanggan) === Number(id)).length;
const belanja = (id) => store.penjualanAktif.value.filter((t) => Number(t.id_pelanggan) === Number(id)).reduce((s, t) => s + Number(t.total_faktur), 0);
const stats = computed(() => [
    { label: 'Total Pelanggan', value: store.pelangganAktif.value.length },
    ...kelompokList.value.map((k) => ({ label: k.nama_kelompok, value: store.pelangganAktif.value.filter((p) => Number(p.id_kelompok_pelanggan) === Number(k.id_kelompok_pelanggan)).length })),
]);
function extractRows(json) {
    if (Array.isArray(json)) return json;
    if (Array.isArray(json?.data)) return json.data;
    if (Array.isArray(json?.data?.data)) return json.data.data;
    return [];
}
async function loadPelanggan() {
    try {
        const json = await apiFetch('/api/pelanggan?per_page=200');
        const rows = extractRows(json);
        if (rows.length || json?.data) replaceCollection('pelanggan', rows);
    } catch { /* pakai hydrate props */ }
}
async function loadKelompok() {
    try {
        const json = await apiFetch('/api/pelanggan/kelompok');
        const rows = json?.data ?? [];
        if (Array.isArray(rows) && rows.length) replaceCollection('kelompokPelanggan', rows);
    } catch { /* pakai mock/props */ }
}
function baru() { ferr.value = ''; form.value = { nama_pelanggan: '', id_kelompok_pelanggan: kelompokList.value[0]?.id_kelompok_pelanggan ?? null, telepon: '', alamat: '' }; show.value = true; }
function edit(p) { ferr.value = ''; form.value = { ...p }; show.value = true; }
async function simpan() {
    ferr.value = '';
    if (!form.value.nama_pelanggan?.trim()) { ferr.value = 'Nama pelanggan wajib diisi.'; return; }
    if (!form.value.id_kelompok_pelanggan) { ferr.value = 'Pilih kelompok pelanggan.'; return; }
    saving.value = true;
    const payload = {
        nama_pelanggan: form.value.nama_pelanggan.trim(),
        id_kelompok_pelanggan: Number(form.value.id_kelompok_pelanggan),
        telepon: form.value.telepon?.trim() || null,
        alamat: form.value.alamat?.trim() || null,
    };
    try {
        if (form.value.id_pelanggan) await apiFetch(`/api/pelanggan/${form.value.id_pelanggan}`, { method: 'PUT', body: JSON.stringify(payload) });
        else await apiFetch('/api/pelanggan', { method: 'POST', body: JSON.stringify(payload) });
        await loadPelanggan();
        show.value = false;
    } catch (e) {
        ferr.value = e?.errors ? Object.values(e.errors).flat().join(' ') : (e?.message || 'Gagal menyimpan pelanggan.');
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
function hapus(p) {
    cf.value = {
        show: true, title: 'Hapus pelanggan?', message: `Hapus pelanggan "${p.nama_pelanggan}"? Riwayat transaksinya tetap tersimpan.`,
        confirmLabel: 'Hapus', danger: true, loading: false,
        run: async () => {
            await apiFetch(`/api/pelanggan/${p.id_pelanggan}`, { method: 'DELETE' });
            await loadPelanggan();
        },
    };
}
</script>
