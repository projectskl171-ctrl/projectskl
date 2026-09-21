<template>
    <RoleDenied v-if="!auth.can('/settings')" page="Settings" :needed="['Kasir']" />
    <div v-else class="mx-auto max-w-3xl space-y-4">
        <div class="flex gap-1.5 rounded-xl border border-slate-200 dark:border-white/[0.06] bg-white dark:bg-white/[0.02] p-1.5 shadow-sm dark:shadow-none">
            <button v-for="t in tabs" :key="t.key" @click="tab = t.key"
                :class="tab === t.key ? 'bg-slate-900/[0.06] dark:bg-white/10 text-slate-900 dark:text-white' : 'text-slate-500 dark:text-white/40 hover:text-slate-900 dark:hover:text-white'"
                class="h-9 flex-1 rounded-lg text-xs font-black">{{ t.label }}</button>
        </div>

        <!-- ===== PROFIL ===== -->
        <div v-if="tab === 'profil'" class="rounded-xl border border-slate-200 dark:border-white/[0.06] bg-white dark:bg-white/[0.02] p-5 shadow-sm dark:shadow-none">
            <div class="flex items-center gap-3">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl text-sm font-black text-white" :style="{ background: ROLE_COLOR[auth.role.value] }">{{ auth.user.value?.inisial }}</div>
                <div>
                    <p class="text-sm font-black">{{ auth.user.value?.nama_lengkap }}</p>
                    <p class="text-[11px] text-slate-500 dark:text-white/40">@{{ auth.user.value?.username }} • {{ auth.roleLabel.value }} • {{ store.namaSekolah(mySchool) }}</p>
                </div>
            </div>
            <div class="mt-4 grid grid-cols-1 gap-2 sm:grid-cols-2">
                <label class="block text-xs text-slate-500 dark:text-white/50">Nama lengkap<input v-model="profil.nama" class="mt-1 h-10 w-full rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-black/50 px-3 text-sm text-slate-900 dark:text-white outline-none" /></label>
                <label class="block text-xs text-slate-500 dark:text-white/50">Username<input v-model="profil.username" class="mt-1 h-10 w-full rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-black/50 px-3 font-mono text-sm text-slate-900 dark:text-white outline-none" /></label>
                <label class="block text-xs text-slate-500 dark:text-white/50">Password baru<input v-model="profil.pass" type="password" placeholder="Kosongkan = tidak diubah" class="mt-1 h-10 w-full rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-black/50 px-3 text-sm text-slate-900 dark:text-white outline-none" /></label>
                <label class="block text-xs text-slate-500 dark:text-white/50">Ulangi password<input v-model="profil.pass2" type="password" placeholder="••••••••" class="mt-1 h-10 w-full rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-black/50 px-3 text-sm text-slate-900 dark:text-white outline-none" /></label>
            </div>
            <p v-if="msg" class="mt-3 rounded-lg px-3 py-2 text-[11px] font-bold" :class="msgOk ? 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300' : 'bg-rose-500/10 text-rose-700 dark:text-rose-300'">{{ msg }}</p>
            <button @click="simpanProfil" class="mt-4 h-10 w-full rounded-lg bg-emerald-500 text-sm font-black text-white hover:bg-emerald-400">Simpan Profil</button>
            <p class="mt-2 text-[11px] text-slate-400 dark:text-white/25">tb_user • password disimpan ter-hash di backend (tersimpan ke database).</p>
        </div>

        <!-- ===== SEKOLAH / TOKO (tb_sekolah + is_active) ===== -->
        <div v-if="tab === 'sekolah'" class="space-y-3">
            <div v-for="s in sekolahList" :key="s.id_sekolah" class="rounded-xl border border-slate-200 dark:border-white/[0.06] bg-white dark:bg-white/[0.02] p-5 shadow-sm dark:shadow-none">
                <div class="flex items-center justify-between gap-2">
                    <p class="font-mono text-[10px] font-bold text-slate-400 dark:text-white/30">{{ s.kode_sekolah }}</p>
                    <div class="flex items-center gap-2">
                        <span class="rounded px-1.5 py-0.5 text-[10px] font-black" :class="statusClass(s)">{{ labelStatus(s) }}</span>
                        <button v-if="bisaToggleSekolah" @click="toggleAktif(s)" class="h-7 rounded-md bg-slate-900/[0.04] dark:bg-white/5 px-2.5 text-[11px] font-black text-slate-600 dark:text-white/70 hover:bg-slate-900/5 dark:hover:bg-white/10">{{ s.is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                    </div>
                </div>
                <p class="mt-2 text-[10px] text-slate-500 dark:text-white/40">Status langganan: {{ s.status_langganan ?? (s.is_active ? 'aktif' : 'terdaftar') }} • bayar terakhir: {{ s.bulan_terakhir_bayar || 'belum ada' }}</p>
                <label class="mt-2 block text-xs text-slate-500 dark:text-white/50">Nama sekolah<input v-model="s.nama_sekolah" :disabled="!bisaEditSekolah" class="mt-1 h-10 w-full rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-black/50 px-3 text-sm font-bold text-slate-900 dark:text-white outline-none disabled:opacity-50" /></label>
                <label class="mt-2 block text-xs text-slate-500 dark:text-white/50">Alamat<textarea v-model="s.alamat_sekolah" rows="2" :disabled="!bisaEditSekolah" class="mt-1 w-full rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-black/50 px-3 py-2 text-sm text-slate-900 dark:text-white outline-none disabled:opacity-50"></textarea></label>
                <label class="mt-2 block text-xs text-slate-500 dark:text-white/50">Website<input v-model="s.website" :disabled="!bisaEditSekolah" class="mt-1 h-10 w-full rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-black/50 px-3 text-sm text-slate-900 dark:text-white outline-none disabled:opacity-50" /></label>
                <button v-if="bisaEditSekolah" @click="simpanSekolah(s)" class="mt-3 h-9 rounded-lg bg-slate-900/[0.04] dark:bg-white/5 px-4 text-xs font-black text-slate-600 dark:text-white/70 hover:bg-slate-900/5 dark:hover:bg-white/10">Simpan {{ s.nama_sekolah }}</button>
            </div>
            <p v-if="smsg" class="rounded-lg px-3 py-2 text-[11px] font-bold" :class="smsgOk ? 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300' : 'bg-rose-500/10 text-rose-700 dark:text-rose-300'">{{ smsg }}</p>
            <p class="text-[11px] text-slate-400 dark:text-white/25">tb_sekolah • kasir hanya bisa melihat • admin mengelola sekolahnya • super admin semua sekolah + toggle aktif/nonaktif (sekolah nonaktif hilang dari dashboard & scope).</p>
        </div>

        <!-- ===== TAMPILAN ===== -->
        <div v-if="tab === 'tampil'" class="space-y-3">
            <div class="rounded-xl border border-slate-200 dark:border-white/[0.06] bg-white dark:bg-white/[0.02] p-5 shadow-sm dark:shadow-none">
                <h3 class="text-sm font-black">🎨 Tema</h3>
                <p class="mt-1 text-xs text-slate-500 dark:text-white/40">Pilihan tema tersimpan permanen di browser dan dipakai tiap buka app.</p>
                <div class="mt-3 grid grid-cols-2 gap-2">
                    <button @click="updateAppearance('dark')"
                        class="flex h-11 items-center justify-center gap-2 rounded-lg border text-xs font-black"
                        :class="appearance === 'dark' ? 'border-emerald-500/40 bg-emerald-500/10 text-slate-900 dark:text-white' : 'border-slate-200 dark:border-white/10 bg-white dark:bg-black/30 text-slate-500 dark:text-white/40 hover:text-slate-900 dark:hover:text-white'">
                        <span>🌙</span> Gelap
                        <span v-if="appearance === 'dark'" class="rounded bg-emerald-500/15 px-1.5 py-0.5 text-[10px] font-black text-emerald-700 dark:text-emerald-300">AKTIF</span>
                    </button>
                    <button @click="updateAppearance('light')"
                        class="flex h-11 items-center justify-center gap-2 rounded-lg border text-xs font-black"
                        :class="appearance === 'light' ? 'border-emerald-500/40 bg-emerald-500/10 text-slate-900 dark:text-white' : 'border-slate-200 dark:border-white/10 bg-white dark:bg-black/30 text-slate-500 dark:text-white/40 hover:text-slate-900 dark:hover:text-white'">
                        <span>☀️</span> Terang
                        <span v-if="appearance === 'light'" class="rounded bg-emerald-500/15 px-1.5 py-0.5 text-[10px] font-black text-emerald-700 dark:text-emerald-300">AKTIF</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import RoleDenied from '@/components/RoleDenied.vue';
import { hydrate, usePosStore } from '@/composables/usePosStore';
import { ROLE_COLOR, updateMockProfile, useAuthMock } from '@/composables/useAuthMock';
import { useAppearance } from '@/composables/useAppearance';

defineOptions({ layout: DashboardLayout });
const props = defineProps({ users: Array });
onMounted(() => {
    hydrate(props);
});
const store = usePosStore();
const auth = useAuthMock();
const { appearance, updateAppearance } = useAppearance();

const tabs = [{ key: 'profil', label: '👤 Profil' }, { key: 'sekolah', label: '🏫 Sekolah' }, { key: 'tampil', label: '🎨 Tampilan' }];
const tab = ref('profil');
const myId = auth.user.value?.id_user ?? 0;
const mySchool = auth.role.value === 'super admin' ? 0 : 1;

const me = store.usersAktif.value.find((u) => u.id_user === myId);
const profil = ref({ nama: me?.nama_lengkap ?? auth.user.value?.nama_lengkap ?? '', username: me?.username ?? auth.user.value?.username ?? '', pass: '', pass2: '' });
const msg = ref(''), msgOk = ref(true);

const sekolahList = ref(JSON.parse(JSON.stringify(
    auth.role.value === 'super admin' ? store.state.sekolah : store.state.sekolah.filter((s) => s.id_sekolah === 1),
)));
const bisaEditSekolah = auth.role.value !== 'kasir';
const bisaToggleSekolah = auth.role.value === 'super admin';
const smsg = ref(''), smsgOk = ref(true);

function simpanProfil() {
    if (!profil.value.nama || !profil.value.username) { msg.value = 'Nama & username wajib diisi.'; msgOk.value = false; return; }
    if (profil.value.pass && profil.value.pass !== profil.value.pass2) { msg.value = 'Konfirmasi password tidak sama.'; msgOk.value = false; return; }
    const payload = { id_user: myId, nama_lengkap: profil.value.nama, username: profil.value.username };
    if (profil.value.pass) payload.password = '— hashed —';
    store.saveUser(payload);
    updateMockProfile(profil.value.nama, profil.value.username);
    msg.value = 'Profil tersimpan (demo lokal).'; msgOk.value = true;
    profil.value.pass = ''; profil.value.pass2 = '';
}
function simpanSekolah(s) {
    store.saveSekolah({ id_sekolah: s.id_sekolah, nama_sekolah: s.nama_sekolah, alamat_sekolah: s.alamat_sekolah, website: s.website, is_active: s.is_active });
    smsg.value = `${s.nama_sekolah} tersimpan.`; smsgOk.value = true;
}
function labelStatus(s) {
    const status = s.status_langganan ?? (s.is_active ? 'aktif' : 'terdaftar');
    if (status === 'menunggak') return 'MENUNGGAK';
    if (status === 'terdaftar') return 'TERDAFTAR';
    return 'AKTIF';
}
function statusClass(s) {
    const status = s.status_langganan ?? (s.is_active ? 'aktif' : 'terdaftar');
    if (status === 'menunggak') return 'bg-rose-500/15 text-rose-700 dark:text-rose-300';
    if (status === 'terdaftar') return 'bg-amber-500/15 text-amber-700 dark:text-amber-300';
    return 'bg-emerald-500/15 text-emerald-700 dark:text-emerald-300';
}
function toggleAktif(s) {
    const ok = store.toggleSekolah(s.id_sekolah);
    const real = store.state.sekolah.find((x) => x.id_sekolah === s.id_sekolah);
    if (real) s.is_active = real.is_active;
    smsg.value = ok ? `${s.nama_sekolah} sekarang ${s.is_active ? 'AKTIF' : 'NONAKTIF'}.` : 'Minimal 1 sekolah harus tetap aktif.';
    smsgOk.value = ok;
}
</script>
