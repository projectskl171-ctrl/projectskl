<template>
    <RoleDenied v-if="!auth.can('/sekolah')" page="Sekolah" :needed="['Super Admin']" />
    <div v-else class="space-y-4">
        <div class="flex flex-col gap-2 sm:flex-row">
            <input v-model="q" placeholder="Cari nama / kode sekolah…" class="h-10 flex-1 rounded-lg border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-black/40 px-3 text-sm outline-none placeholder:text-slate-400 dark:placeholder:text-white/25" />
            <button @click="bukaBaru" class="h-10 cursor-pointer rounded-lg bg-sky-500 px-4 text-sm font-black text-white hover:bg-sky-400">+ Sekolah</button>
        </div>

        <div class="space-y-2">
            <div v-for="s in paged" :key="s.id_sekolah" class="flex items-center gap-3 rounded-xl border border-slate-200 dark:border-white/[0.06] bg-white dark:bg-white/[0.02] px-4 py-3">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-sm" :class="s.is_active ? 'bg-emerald-500/15' : 'bg-slate-900/[0.06] dark:bg-white/10'">🏫</span>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-bold">{{ s.nama_sekolah }}
                        <span class="ml-1 rounded px-1.5 py-0.5 font-mono text-[10px] font-black text-slate-500 dark:text-white/40">{{ s.kode_sekolah }}</span>
                        <span class="ml-1 rounded px-1.5 py-0.5 text-[10px] font-black" :class="statusClass(s)">{{ labelStatus(s) }}</span>
                    </p>
                    <p class="truncate text-[11px] text-slate-500 dark:text-white/40">{{ s.alamat_sekolah || '—' }}{{ expiryOf(s) ? ` • s/d ${formatDate(expiryOf(s))}` : '' }}</p>
                    <p class="mt-0.5 text-[10px] font-bold" :class="timerClass(s)">{{ labelTimer(s) }}</p>
                </div>
                <div class="flex shrink-0 flex-wrap justify-end gap-1.5">
                    <button @click="bukaEdit(s)" class="h-8 cursor-pointer rounded-lg bg-slate-900/[0.04] dark:bg-white/5 px-3 text-[11px] font-black hover:bg-slate-900/10 dark:hover:bg-white/10">Edit</button>
                    <button @click="mintaPerpanjang(s)" title="Konfirmasi sudah bayar & perpanjang 30 hari" class="h-8 cursor-pointer rounded-lg bg-emerald-500/15 px-3 text-[11px] font-black text-emerald-700 dark:text-emerald-300 hover:bg-emerald-500/25">Perpanjang</button>
                    <button @click="mintaToggle(s)" class="h-8 cursor-pointer rounded-lg bg-slate-900/[0.04] dark:bg-white/5 px-3 text-[11px] font-bold hover:bg-slate-900/10 dark:hover:bg-white/10">{{ s.is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                    <button @click="mintaHapus(s)" class="h-8 cursor-pointer rounded-lg bg-slate-900/[0.04] dark:bg-white/5 px-3 text-[11px] font-bold text-rose-700 dark:text-rose-300 hover:bg-rose-500/20">Hapus</button>
                </div>
            </div>
            <p v-if="!filtered.length" class="rounded-xl border border-dashed border-slate-200 dark:border-white/10 py-10 text-center text-sm text-slate-400 dark:text-white/30">Tidak ada sekolah yang cocok.</p>
        </div>
        <Pagination :page="page" :total-pages="totalPages" @update:page="page = $event" />
        <p v-if="smsg" class="rounded-lg px-3 py-2 text-[11px] font-bold" :class="smsgOk ? 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300' : 'bg-rose-500/10 text-rose-700 dark:text-rose-300'">{{ smsg }}</p>
    </div>

    <!-- Popup create / edit sekolah (tutup hanya via Batal/Simpan) -->
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4 backdrop-blur-sm">
        <div class="w-full max-w-md rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-[#111] p-5">
            <h3 class="text-sm font-black">{{ form.id_sekolah ? 'Edit Sekolah' : 'Sekolah Baru' }}</h3>
            <p v-if="form.kode_sekolah" class="mt-0.5 font-mono text-[10px] font-bold text-slate-400 dark:text-white/30">{{ form.kode_sekolah }}{{ form.id_sekolah ? '' : '' }}</p>
            <div class="mt-4 space-y-2 text-xs">
                <label class="block text-slate-500 dark:text-white/50">Nama sekolah<input v-model="form.nama_sekolah" class="mt-1 h-10 w-full rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-black/50 px-3 text-sm font-bold text-slate-900 dark:text-white outline-none" /></label>
                <label class="block text-slate-500 dark:text-white/50">Alamat<textarea v-model="form.alamat_sekolah" rows="2" class="mt-1 w-full rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-black/50 px-3 py-2 text-sm text-slate-900 dark:text-white outline-none"></textarea></label>
                <label class="block text-slate-500 dark:text-white/50">Website<input v-model="form.website" placeholder="https://…" class="mt-1 h-10 w-full rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-black/50 px-3 text-sm text-slate-900 dark:text-white outline-none" /></label>
                <label class="flex items-center gap-2 rounded-lg border border-slate-200 dark:border-white/[0.06] bg-white dark:bg-black/30 px-3 py-2.5 text-slate-600 dark:text-white/70">
                    <input v-model="form.is_active" type="checkbox" :true-value="1" :false-value="0" class="h-4 w-4 accent-emerald-500" />
                    <span class="text-xs font-bold">Sekolah aktif</span>
                </label>
            </div>
            <p v-if="ferr" class="mt-2 rounded-lg bg-rose-500/10 px-3 py-2 text-[11px] font-bold text-rose-700 dark:text-rose-300">{{ ferr }}</p>
            <div class="mt-4 flex gap-2">
                <button @click="show = false" class="h-10 flex-1 cursor-pointer rounded-lg bg-slate-900/[0.04] dark:bg-white/5 text-sm font-bold">Batal</button>
                <button @click="simpan" :disabled="saving" class="h-10 flex-1 cursor-pointer rounded-lg bg-sky-500 text-sm font-black text-white hover:bg-sky-400 disabled:opacity-50">{{ saving ? 'Menyimpan…' : 'Simpan' }}</button>
            </div>
        </div>
    </div>

    <ConfirmModal :show="cf.show" :title="cf.title" :message="cf.message" :confirm-label="cf.confirmLabel" :danger="cf.danger" :loading="cf.loading" @cancel="cf.show = false" @confirm="cfRun" />
</template>

<script setup>
import { onMounted, ref } from 'vue';
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import RoleDenied from '@/components/RoleDenied.vue';
import Pagination from '@/components/Pagination.vue';
import ConfirmModal from '@/components/ConfirmModal.vue';
import { usePagination } from '@/composables/usePagination';
import { hydrate, replaceCollection, usePosStore } from '@/composables/usePosStore';
import { useAuthMock } from '@/composables/useAuthMock';
import { formatDate } from '@/lib/format';
import { apiFetch } from '@/lib/api';

defineOptions({ layout: DashboardLayout });
const props = defineProps({ sekolah: Array });
onMounted(() => {
    hydrate(props);
    loadSekolah();
});
const store = usePosStore();
const auth = useAuthMock();

const q = ref('');
const show = ref(false);
const form = ref({});
const ferr = ref('');
const saving = ref(false);
const smsg = ref(''), smsgOk = ref(true);
const cf = ref({ show: false, title: '', message: '', confirmLabel: 'Ya', danger: false, loading: false, run: null });

const { page, totalPages, paged, filtered } = usePagination(() => {
    const s = q.value.trim().toLowerCase();
    return store.state.sekolah
        .filter((x) => !s || (x.nama_sekolah || '').toLowerCase().includes(s) || (x.kode_sekolah || '').toLowerCase().includes(s))
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
async function loadSekolah() {
    try {
        const json = await apiFetch('/api/sekolah');
        const rows = json?.data ?? (Array.isArray(json) ? json : []);
        if (Array.isArray(rows)) replaceCollection('sekolah', rows);
    } catch { /* pakai hydrate */ }
}
function kodeOtomatis() {
    const max = store.state.sekolah.reduce((m, s) => {
        const num = parseInt(String(s.kode_sekolah || '').replace(/\D/g, ''), 10);
        return Number.isFinite(num) ? Math.max(m, num) : m;
    }, 0);
    return 'SCH' + String(max + 1).padStart(3, '0');
}
function bukaBaru() {
    ferr.value = '';
    form.value = { kode_sekolah: kodeOtomatis(), nama_sekolah: '', alamat_sekolah: '', website: '', is_active: 1 };
    show.value = true;
}
function bukaEdit(s) {
    ferr.value = '';
    form.value = { ...s };
    show.value = true;
}
async function simpan() {
    ferr.value = '';
    if (!form.value.nama_sekolah?.trim()) { ferr.value = 'Nama sekolah tidak boleh kosong.'; return; }
    if (!form.value.id_sekolah) form.value.kode_sekolah = kodeOtomatis();
    saving.value = true;
    try {
        if (form.value.id_sekolah) {
            await apiFetch(`/api/sekolah/${form.value.id_sekolah}`, {
                method: 'PUT',
                body: JSON.stringify({
                    kode_sekolah: form.value.kode_sekolah,
                    nama_sekolah: form.value.nama_sekolah.trim(),
                    alamat_sekolah: form.value.alamat_sekolah?.trim() || null,
                    website: form.value.website?.trim() || null,
                    is_active: form.value.is_active ? 1 : 0,
                }),
            });
            if (res?.data) {
                const i = store.state.sekolah.findIndex((x) => Number(x.id_sekolah) === Number(form.value.id_sekolah));
                if (i >= 0) store.state.sekolah[i] = { ...store.state.sekolah[i], ...res.data };
            }
            smsg.value = `${form.value.nama_sekolah} tersimpan.`;
        } else {
            const res = await apiFetch('/api/sekolah', {
                method: 'POST',
                body: JSON.stringify({
                    kode_sekolah: form.value.kode_sekolah.trim().toUpperCase(),
                    nama_sekolah: form.value.nama_sekolah.trim(),
                    alamat_sekolah: form.value.alamat_sekolah?.trim() || null,
                    website: form.value.website?.trim() || null,
                    is_active: form.value.is_active ? 1 : 0,
                }),
            });
            if (res?.data) store.state.sekolah.push(res.data);
            smsg.value = `${form.value.nama_sekolah} berhasil ditambahkan. Langganan 30 hari dimulai hari ini.`;
        }
        smsgOk.value = true;
        show.value = false;
        await loadSekolah();
    } catch (e) {
        ferr.value = e?.errors ? Object.values(e.errors).flat().join(' ') : (e?.message || 'Gagal menyimpan sekolah.');
    } finally {
        saving.value = false;
    }
}
/* Tanggal jatuh tempo = mulai + 30 hari (mendukung stacking perpanjang). */
function expiryOf(s) {
    const awal = s.activated_at || s.created_at;
    if (!awal) return null;
    const d = new Date(awal);
    d.setDate(d.getDate() + 30);
    return d;
}
/* Sisa hari hingga jatuh tempo (negatif = telat). */
function sisaLangganan(s) {
    const exp = expiryOf(s);
    if (!exp) return 30;
    const e = new Date(exp); e.setHours(0, 0, 0, 0);
    const now = new Date(); now.setHours(0, 0, 0, 0);
    return Math.ceil((e - now) / 86400000);
}
function labelTimer(s) {
    const sisa = sisaLangganan(s);
    if (sisa < 0) return `⏰ Telat ${-sisa} hari (1 bulan+) — perlu konfirmasi bayar & perpanjang`;
    if (sisa <= 7) return `⏰ Sisa ${sisa} hari menuju jatuh tempo — siapkan konfirmasi bayar`;
    return `✅ Sisa ${sisa} hari langganan`;
}
function timerClass(s) {
    const sisa = sisaLangganan(s);
    if (sisa < 0 || sisa <= 7) return 'text-rose-600 dark:text-rose-400';
    if (sisa <= 14) return 'text-amber-600 dark:text-amber-400';
    return 'text-emerald-700 dark:text-emerald-400';
}
function tanya(title, message, confirmLabel, danger, run) {
    cf.value = { show: true, title, message, confirmLabel, danger, loading: false, run };
}
async function cfRun() {
    const fn = cf.value.run;
    if (!fn) { cf.value.show = false; return; }
    cf.value.loading = true;
    try {
        await fn();
        cf.value.show = false;
    } catch (e) {
        cf.value.show = false;
        smsg.value = e?.message || 'Aksi gagal.';
        smsgOk.value = false;
    } finally {
        cf.value.loading = false;
    }
}
function mintaToggle(s) {
    tanya(
        s.is_active ? 'Nonaktifkan sekolah?' : 'Aktifkan sekolah?',
        s.is_active
            ? `${s.nama_sekolah} akan dinonaktifkan (kasir & admin ikut tidak bisa login).`
            : `${s.nama_sekolah} akan diaktifkan & langganan 30 hari dimulai ulang dari hari ini.`,
        s.is_active ? 'Nonaktifkan' : 'Aktifkan',
        s.is_active,
        async () => {
            await apiFetch(`/api/sekolah/${s.id_sekolah}/toggle`, { method: 'PATCH' });
            await loadSekolah();
            smsg.value = `${s.nama_sekolah} status diperbarui.`;
            smsgOk.value = true;
        },
    );
}
function mintaPerpanjang(s) {
    const sisa = sisaLangganan(s);
    tanya(
        'Perpanjang langganan?',
        sisa > 0
            ? `Konfirmasi ${s.nama_sekolah} sudah bayar? 30 hari ditambahkan SETELAH masa berjalan habis (sisa ${sisa} hari). Timer & notif merah ikut reset.`
            : `Konfirmasi ${s.nama_sekolah} sudah bayar & perpanjang 30 hari dari sekarang? Timer & notif merah ikut reset.`,
        'Perpanjang',
        false,
        async () => {
            const res = await apiFetch(`/api/sekolah/${s.id_sekolah}/perpanjang`, { method: 'POST' });
            if (res?.data) {
                const i = store.state.sekolah.findIndex((x) => Number(x.id_sekolah) === Number(s.id_sekolah));
                if (i >= 0) store.state.sekolah[i] = { ...store.state.sekolah[i], ...res.data };
            }
            await loadSekolah();
            smsg.value = `${s.nama_sekolah} diperpanjang 30 hari.`;
            smsgOk.value = true;
        },
    );
}
function mintaHapus(s) {
    tanya(
        'Hapus sekolah?',
        `Hapus ${s.nama_sekolah} (${s.kode_sekolah})? Hanya bisa bila sudah tidak punya barang/penjualan/pembelian.`,
        'Hapus',
        true,
        async () => {
            await apiFetch(`/api/sekolah/${s.id_sekolah}`, { method: 'DELETE' });
            await loadSekolah();
            smsg.value = `${s.nama_sekolah} dihapus.`;
            smsgOk.value = true;
        },
    );
}
</script>
