<template>
    <div class="mx-auto max-w-3xl space-y-4">
        <div class="flex gap-1.5 rounded-xl border border-white/[0.06] bg-white/[0.02] p-1.5">
            <button v-for="t in tabs" :key="t.key" @click="tab = t.key"
                :class="tab === t.key ? 'bg-white/10 text-white' : 'text-white/40 hover:text-white'"
                class="h-9 flex-1 rounded-lg text-xs font-black">{{ t.label }}</button>
        </div>

        <!-- ===== PROFIL ===== -->
        <div v-if="tab === 'profil'" class="rounded-xl border border-white/[0.06] bg-white/[0.02] p-5">
            <div class="flex items-center gap-3">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl text-sm font-black text-white" :style="{ background: ROLE_COLOR[auth.role.value] }">{{ auth.user.value?.inisial }}</div>
                <div>
                    <p class="text-sm font-black">{{ auth.user.value?.nama_lengkap }}</p>
                    <p class="text-[11px] text-white/40">@{{ auth.user.value?.username }} • {{ auth.roleLabel.value }} • {{ store.namaSekolah(mySchool) }}</p>
                </div>
            </div>
            <div class="mt-4 grid grid-cols-1 gap-2 sm:grid-cols-2">
                <label class="block text-xs text-white/50">Nama lengkap<input v-model="profil.nama" class="mt-1 h-10 w-full rounded-lg border border-white/10 bg-black/50 px-3 text-sm text-white outline-none" /></label>
                <label class="block text-xs text-white/50">Username<input v-model="profil.username" class="mt-1 h-10 w-full rounded-lg border border-white/10 bg-black/50 px-3 font-mono text-sm text-white outline-none" /></label>
                <label class="block text-xs text-white/50">Password baru<input v-model="profil.pass" type="password" placeholder="Kosongkan = tidak diubah" class="mt-1 h-10 w-full rounded-lg border border-white/10 bg-black/50 px-3 text-sm text-white outline-none" /></label>
                <label class="block text-xs text-white/50">Ulangi password<input v-model="profil.pass2" type="password" placeholder="••••••••" class="mt-1 h-10 w-full rounded-lg border border-white/10 bg-black/50 px-3 text-sm text-white outline-none" /></label>
            </div>
            <p v-if="msg" class="mt-3 rounded-lg px-3 py-2 text-[11px] font-bold" :class="msgOk ? 'bg-emerald-500/10 text-emerald-300' : 'bg-rose-500/10 text-rose-300'">{{ msg }}</p>
            <button @click="simpanProfil" class="mt-4 h-10 w-full rounded-lg bg-emerald-500 text-sm font-black text-white hover:bg-emerald-400">Simpan Profil</button>
            <p class="mt-2 text-[11px] text-white/25">tb_user • password disimpan ter-hash di backend (demo: mock lokal).</p>
        </div>

        <!-- ===== SEKOLAH / TOKO (tb_sekolah + is_active) ===== -->
        <div v-if="tab === 'sekolah'" class="space-y-3">
            <div v-for="s in sekolahList" :key="s.id_sekolah" class="rounded-xl border border-white/[0.06] bg-white/[0.02] p-5">
                <div class="flex items-center justify-between gap-2">
                    <p class="font-mono text-[10px] font-bold text-white/30">{{ s.kode_sekolah }}</p>
                    <div class="flex items-center gap-2">
                        <span class="rounded px-1.5 py-0.5 text-[10px] font-black" :class="statusClass(s)">{{ labelStatus(s) }}</span>
                        <button v-if="bisaToggleSekolah" @click="toggleAktif(s)" class="h-7 rounded-md bg-white/5 px-2.5 text-[11px] font-black text-white/70 hover:bg-white/10">{{ s.is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                    </div>
                </div>
                <p class="mt-2 text-[10px] text-white/40">Status langganan: {{ s.status_langganan ?? (s.is_active ? 'aktif' : 'terdaftar') }} • bayar terakhir: {{ s.bulan_terakhir_bayar || 'belum ada' }}</p>
                <label class="mt-2 block text-xs text-white/50">Nama sekolah<input v-model="s.nama_sekolah" :disabled="!bisaEditSekolah" class="mt-1 h-10 w-full rounded-lg border border-white/10 bg-black/50 px-3 text-sm font-bold text-white outline-none disabled:opacity-50" /></label>
                <label class="mt-2 block text-xs text-white/50">Alamat<textarea v-model="s.alamat_sekolah" rows="2" :disabled="!bisaEditSekolah" class="mt-1 w-full rounded-lg border border-white/10 bg-black/50 px-3 py-2 text-sm text-white outline-none disabled:opacity-50"></textarea></label>
                <label class="mt-2 block text-xs text-white/50">Website<input v-model="s.website" :disabled="!bisaEditSekolah" class="mt-1 h-10 w-full rounded-lg border border-white/10 bg-black/50 px-3 text-sm text-white outline-none disabled:opacity-50" /></label>
                <button v-if="bisaEditSekolah" @click="simpanSekolah(s)" class="mt-3 h-9 rounded-lg bg-white/5 px-4 text-xs font-black text-white/70 hover:bg-white/10">Simpan {{ s.nama_sekolah }}</button>
            </div>
            <p v-if="smsg" class="rounded-lg px-3 py-2 text-[11px] font-bold" :class="smsgOk ? 'bg-emerald-500/10 text-emerald-300' : 'bg-rose-500/10 text-rose-300'">{{ smsg }}</p>
            <p class="text-[11px] text-white/25">tb_sekolah • kasir hanya bisa melihat • admin mengelola sekolahnya • super admin semua sekolah + toggle aktif/nonaktif (sekolah nonaktif hilang dari dashboard & scope).</p>
        </div>

        <!-- ===== TAMPILAN & DATA DEMO ===== -->
        <div v-if="tab === 'tampil'" class="space-y-3">
            <div class="rounded-xl border border-white/[0.06] bg-white/[0.02] p-5">
                <h3 class="text-sm font-black">🎨 Tema</h3>
                <p class="mt-1 text-xs text-white/40">Pilihan tema tersimpan di browser (localStorage) dan kepakai tiap buka app. Tema terang nyusul nanti.</p>
                <div class="mt-3 grid grid-cols-2 gap-2">
                    <button @click="applyTheme('gelap')"
                        class="flex h-11 items-center justify-center gap-2 rounded-lg border text-xs font-black"
                        :class="theme === 'gelap' ? 'border-emerald-500/40 bg-emerald-500/10 text-white' : 'border-white/10 bg-black/30 text-white/40 hover:text-white'">
                        <span>🌙</span> Gelap
                        <span v-if="theme === 'gelap'" class="rounded bg-emerald-500/15 px-1.5 py-0.5 text-[10px] font-black text-emerald-300">AKTIF</span>
                    </button>
                    <button disabled title="Tema terang segera hadir"
                        class="flex h-11 cursor-not-allowed items-center justify-center gap-2 rounded-lg border border-white/10 bg-black/30 text-xs font-black text-white/25">
                        <span>☀️</span> Terang
                        <span class="rounded bg-white/5 px-1.5 py-0.5 text-[10px] font-black text-white/30">NANTI</span>
                    </button>
                </div>
            </div>
            <div class="rounded-xl border border-white/[0.06] bg-white/[0.02] p-5">
                <h3 class="text-sm font-black">🎭 Ganti Peran (demo)</h3>
                <p class="mt-1 text-xs text-white/40">Rasakan bedanya dashboard kasir (1 dagang), admin (1 sekolah), super admin (semua sekolah).</p>
                <div class="mt-3 grid grid-cols-3 gap-2">
                    <button v-for="r in ['kasir', 'admin', 'super admin']" :key="r" @click="ganti(r)"
                        class="h-10 rounded-lg text-[11px] font-black text-white transition-all hover:brightness-125"
                        :style="{ background: auth.role.value === r ? ROLE_COLOR[r] : 'rgba(255,255,255,0.05)', color: auth.role.value === r ? '#fff' : 'rgba(255,255,255,0.45)' }">
                        {{ ROLE_LABEL[r].toUpperCase() }}
                    </button>
                </div>
            </div>
            <div class="rounded-xl border border-rose-500/20 bg-rose-500/[0.04] p-5">
                <h3 class="text-sm font-black text-rose-300">🗑️ Reset Data Demo</h3>
                <p class="mt-1 text-xs text-white/40">Kembalikan semua tabel mock ke seed awal (termasuk sekolah baru). Harus login ulang peran setelahnya.</p>
                <button @click="reset" class="mt-3 h-10 w-full rounded-lg bg-rose-500/15 text-sm font-black text-rose-300 hover:bg-rose-500/25">Reset ke Seed Awal</button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import { hydrate, resetMockDb, usePosStore } from '@/composables/usePosStore';
import { ROLE_COLOR, ROLE_LABEL, loginAs, updateMockProfile, useAuthMock } from '@/composables/useAuthMock';

defineOptions({ layout: DashboardLayout });
const props = defineProps({ users: Array });
onMounted(() => {
    hydrate(props);
    initTheme();
});
const store = usePosStore();
const auth = useAuthMock();

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
    if (status === 'menunggak') return 'bg-rose-500/15 text-rose-300';
    if (status === 'terdaftar') return 'bg-amber-500/15 text-amber-300';
    return 'bg-emerald-500/15 text-emerald-300';
}
function toggleAktif(s) {
    const ok = store.toggleSekolah(s.id_sekolah);
    const real = store.state.sekolah.find((x) => x.id_sekolah === s.id_sekolah);
    if (real) s.is_active = real.is_active;
    smsg.value = ok ? `${s.nama_sekolah} sekarang ${s.is_active ? 'AKTIF' : 'NONAKTIF'}.` : 'Minimal 1 sekolah harus tetap aktif.';
    smsgOk.value = ok;
}
function ganti(r) { loginAs(r); router.visit('/dashboard'); }

/* ===== TEMA (nyangkut di localStorage; terang = nanti) ===== */
const THEME_KEY = 'kasirku_theme_v1';
const theme = ref('gelap');
function applyTheme(t) {
    theme.value = t;
    try { localStorage.setItem(THEME_KEY, t); } catch { /* abaikan */ }
    if (typeof document !== 'undefined') {
        document.documentElement.dataset.theme = t;
        document.documentElement.style.colorScheme = t === 'terang' ? 'light' : 'dark';
    }
}
function initTheme() {
    let saved = 'gelap';
    try { saved = localStorage.getItem(THEME_KEY) || 'gelap'; } catch { /* abaikan */ }
    // tema terang belum dibangun — kunci ke gelap, wiring localStorage sudah siap
    applyTheme(saved === 'terang' ? 'gelap' : saved);
}

function reset() {
    resetMockDb();
    try { localStorage.removeItem('kasirku_role_v1'); } catch { /* abaikan */ }
    router.visit('/');
}
</script>
