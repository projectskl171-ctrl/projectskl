<template>
    <RoleDenied v-if="!auth.can('/sekolah')" page="Sekolah" :needed="['Super Admin']" />
    <div v-else class="space-y-4">
        <div class="flex flex-col gap-2 sm:flex-row">
            <input v-model="q" placeholder="Cari nama / kode sekolah…" class="h-10 flex-1 rounded-lg border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-black/40 px-3 text-sm outline-none placeholder:text-slate-400 dark:placeholder:text-white/25" />
        </div>

        <div class="space-y-2">
            <div v-for="s in paged" :key="s.id_sekolah" class="flex items-center gap-3 rounded-xl border border-slate-200 dark:border-white/[0.06] bg-white dark:bg-white/[0.02] px-4 py-3">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-sm" :class="s.is_active ? 'bg-emerald-500/15' : 'bg-slate-900/[0.06] dark:bg-white/10'">🏫</span>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-bold">{{ s.nama_sekolah }}
                        <span class="ml-1 rounded px-1.5 py-0.5 text-[10px] font-black" :class="statusClass(s)">{{ labelStatus(s) }}</span>
                    </p>
                    <p class="truncate text-[11px] text-slate-500 dark:text-white/40">{{ s.alamat_sekolah || '—' }}</p>
                </div>
                <div class="flex shrink-0 gap-1.5">
                    <button @click="bukaEdit(s)" class="h-8 rounded-lg bg-slate-900/[0.04] dark:bg-white/5 px-3 text-[11px] font-black hover:bg-slate-900/10 dark:hover:bg-white/10">Edit</button>
                    <button @click="hapusSekolah(s)" class="h-8 rounded-lg bg-slate-900/[0.04] dark:bg-white/5 px-3 text-[11px] font-bold text-rose-700 dark:text-rose-300 hover:bg-rose-500/20">Hapus</button>
                </div>
            </div>
            <p v-if="!filtered.length" class="rounded-xl border border-dashed border-slate-200 dark:border-white/10 py-10 text-center text-sm text-slate-400 dark:text-white/30">Tidak ada sekolah yang cocok.</p>
        </div>
        <Pagination :page="page" :total-pages="totalPages" @update:page="page = $event" />
        <p v-if="smsg" class="rounded-lg px-3 py-2 text-[11px] font-bold" :class="smsgOk ? 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300' : 'bg-rose-500/10 text-rose-700 dark:text-rose-300'">{{ smsg }}</p>
    </div>

    <!-- Popup edit sekolah -->
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4 backdrop-blur-sm" @click.self="show = false">
        <div class="w-full max-w-md rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-[#111] p-5">
            <h3 class="text-sm font-black">Edit Sekolah</h3>
            <p class="mt-0.5 font-mono text-[10px] font-bold text-slate-400 dark:text-white/30">{{ form.kode_sekolah }}</p>
            <div class="mt-4 space-y-2 text-xs">
                <label class="block text-slate-500 dark:text-white/50">Nama sekolah<input v-model="form.nama_sekolah" class="mt-1 h-10 w-full rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-black/50 px-3 text-sm font-bold text-slate-900 dark:text-white outline-none" /></label>
                <label class="block text-slate-500 dark:text-white/50">Alamat<textarea v-model="form.alamat_sekolah" rows="2" class="mt-1 w-full rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-black/50 px-3 py-2 text-sm text-slate-900 dark:text-white outline-none"></textarea></label>
                <label class="block text-slate-500 dark:text-white/50">Website<input v-model="form.website" placeholder="https://…" class="mt-1 h-10 w-full rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-black/50 px-3 text-sm text-slate-900 dark:text-white outline-none" /></label>
                <label class="flex items-center gap-2 rounded-lg border border-slate-200 dark:border-white/[0.06] bg-white dark:bg-black/30 px-3 py-2.5 text-slate-600 dark:text-white/70">
                    <input v-model="form.is_active" type="checkbox" :true-value="1" :false-value="0" class="h-4 w-4 accent-emerald-500" />
                    <span class="text-xs font-bold">Sekolah aktif <span class="font-normal text-slate-400 dark:text-white/35">(nonaktif = semua kasir & admin sekolah ini ikut nonaktif & tidak bisa login)</span></span>
                </label>
            </div>
            <p v-if="ferr" class="mt-2 rounded-lg bg-rose-500/10 px-3 py-2 text-[11px] font-bold text-rose-700 dark:text-rose-300">{{ ferr }}</p>
            <div class="mt-4 flex gap-2">
                <button @click="show = false" class="h-10 flex-1 rounded-lg bg-slate-900/[0.04] dark:bg-white/5 text-sm font-bold">Batal</button>
                <button @click="simpan" class="h-10 flex-1 rounded-lg bg-sky-500 text-sm font-black text-white hover:bg-sky-400">Simpan</button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import RoleDenied from '@/components/RoleDenied.vue';
import Pagination from '@/components/Pagination.vue';
import { usePagination } from '@/composables/usePagination';
import { hydrate, usePosStore } from '@/composables/usePosStore';
import { useAuthMock } from '@/composables/useAuthMock';

defineOptions({ layout: DashboardLayout });
const props = defineProps({ sekolah: Array });
onMounted(() => hydrate(props));
const store = usePosStore();
const auth = useAuthMock();

const q = ref('');
const show = ref(false);
const form = ref({});
const ferr = ref('');
const smsg = ref(''), smsgOk = ref(true);

const { page, totalPages, paged, filtered } = usePagination(() => {
    const s = q.value.trim().toLowerCase();
    return store.state.sekolah
        .filter((x) => !s || x.nama_sekolah.toLowerCase().includes(s) || x.kode_sekolah.toLowerCase().includes(s))
        .sort((a, b) => a.id_sekolah - b.id_sekolah);
}, 8, [q]);

function labelStatus(s) {
    const status = s.status_langganan ?? (s.is_active ? 'aktif' : 'terdaftar');
    if (!s.is_active) return 'NONAKTIF';
    if (status === 'menunggak') return 'MENUNGGAK';
    if (status === 'terdaftar') return 'TERDAFTAR';
    return 'AKTIF';
}
function statusClass(s) {
    if (!s.is_active) return 'bg-slate-900/[0.06] dark:bg-white/10 text-slate-500 dark:text-white/40';
    const status = s.status_langganan ?? 'aktif';
    if (status === 'menunggak') return 'bg-rose-500/15 text-rose-700 dark:text-rose-300';
    if (status === 'terdaftar') return 'bg-amber-500/15 text-amber-700 dark:text-amber-300';
    return 'bg-emerald-500/15 text-emerald-700 dark:text-emerald-300';
}
function bukaEdit(s) {
    ferr.value = '';
    form.value = { ...s };
    show.value = true;
}
function simpan() {
    ferr.value = '';
    if (!form.value.nama_sekolah?.trim()) { ferr.value = 'Nama sekolah tidak boleh kosong.'; return; }
    if (!form.value.alamat_sekolah?.trim()) { ferr.value = 'Alamat sekolah tidak boleh kosong.'; return; }
    if (!form.value.website?.trim()) { ferr.value = 'Website sekolah tidak boleh kosong.'; return; }
    const wasActive = store.state.sekolah.find((x) => x.id_sekolah === form.value.id_sekolah)?.is_active;
    store.saveSekolah({
        id_sekolah: form.value.id_sekolah,
        nama_sekolah: form.value.nama_sekolah.trim(),
        alamat_sekolah: form.value.alamat_sekolah.trim(),
        website: form.value.website?.trim() || '',
        is_active: form.value.is_active ? 1 : 0,
    });
    if (wasActive !== (form.value.is_active ? 1 : 0)) {
        const aktif = form.value.is_active ? 1 : 0;
        for (const u of store.state.users) {
            if (u.id_sekolah === form.value.id_sekolah) u.is_active = aktif;
        }
    }
    show.value = false;
    smsg.value = `${form.value.nama_sekolah} tersimpan.`;
    smsgOk.value = true;
}
function hapusSekolah(s) {
    const jmlUser = store.state.users.filter((u) => u.id_sekolah === s.id_sekolah).length;
    const jmlBarang = store.state.barang.filter((b) => (b.id_sekolah ?? 1) === s.id_sekolah && !b.is_delete).length;
    const aktif = store.state.sekolah.filter((x) => x.is_active).length;
    if (s.is_active && aktif <= 1) { smsg.value = 'Tidak dapat menghapus satu-satunya sekolah aktif.'; smsgOk.value = false; return; }
    if (jmlUser > 0 || jmlBarang > 0) {
        smsg.value = `Tidak dapat menghapus: masih ada ${jmlUser} akun & ${jmlBarang} barang. Nonaktifkan dulu, kosongkan datanya, lalu hapus.`;
        smsgOk.value = false;
        return;
    }
    if (!confirm(`Hapus ${s.nama_sekolah}?`)) return;
    store.deleteSekolah(s.id_sekolah);
    smsg.value = `${s.nama_sekolah} dihapus.`;
    smsgOk.value = true;
}
</script>
