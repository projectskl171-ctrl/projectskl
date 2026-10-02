<template>
    <RoleDenied v-if="!auth.can('/settings')" page="Settings" :needed="['Kasir', 'Admin', 'Super Admin']" />
    <div v-else class="mx-auto max-w-3xl space-y-4">
        <!-- ===== PROFIL (admin & super admin) : kasir hanya dapat tema ===== -->
        <div v-if="bisaAturProfil" class="rounded-xl border border-slate-200 dark:border-white/[0.06] bg-white dark:bg-white/[0.02] p-5 shadow-sm dark:shadow-none">
            <div class="flex items-center gap-3">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl text-sm font-black text-white" :style="{ background: ROLE_COLOR[auth.role.value] }">{{ auth.user.value?.inisial }}</div>
                <div>
                    <p class="text-sm font-black">{{ auth.user.value?.nama_lengkap }}</p>
                    <p class="text-[11px] text-slate-500 dark:text-white/40">@{{ auth.user.value?.username }} • {{ auth.roleLabel.value }}{{ auth.user.value?.id_sekolah ? ` • ${store.namaSekolah(mySchoolId)}` : '' }}</p>
                </div>
            </div>

            <div class="mt-4 space-y-2">
                <!-- Nama lengkap -->
                <div class="flex flex-col gap-2 rounded-lg border border-slate-200 dark:border-white/[0.06] bg-white dark:bg-black/30 px-3 py-2.5 sm:flex-row sm:items-center">
                    <div class="min-w-0 flex-1">
                        <p class="text-[10px] font-bold tracking-wider text-slate-400 dark:text-white/30 uppercase">Nama lengkap</p>
                        <template v-if="editKey !== 'nama'">
                            <p class="truncate text-sm font-bold">{{ nama }}</p>
                            <p v-if="sisaNama > 0" class="text-[10px] text-amber-600 dark:text-amber-400">Bisa diganti lagi dalam {{ sisaNama }} hari</p>
                        </template>
                        <input v-else v-model="draft" class="mt-1 h-9 w-full rounded-lg border border-emerald-500/40 bg-white dark:bg-black/50 px-3 text-sm outline-none" />
                    </div>
                    <div class="flex shrink-0 gap-1.5">
                        <template v-if="editKey !== 'nama'">
                            <button @click="mulaiEdit('nama', nama)" :disabled="sisaNama > 0" class="h-8 rounded-lg bg-slate-900/[0.04] dark:bg-white/5 px-3 text-[11px] font-black disabled:opacity-40">Ubah</button>
                        </template>
                        <template v-else>
                            <button @click="simpanNama" class="h-8 rounded-lg bg-emerald-500 px-3 text-[11px] font-black text-white hover:bg-emerald-400">Simpan</button>
                            <button @click="batalEdit" class="h-8 rounded-lg bg-slate-900/[0.04] dark:bg-white/5 px-3 text-[11px] font-bold">Batal</button>
                        </template>
                    </div>
                </div>

                <!-- Username -->
                <div class="flex flex-col gap-2 rounded-lg border border-slate-200 dark:border-white/[0.06] bg-white dark:bg-black/30 px-3 py-2.5 sm:flex-row sm:items-center">
                    <div class="min-w-0 flex-1">
                        <p class="text-[10px] font-bold tracking-wider text-slate-400 dark:text-white/30 uppercase">Username</p>
                        <template v-if="editKey !== 'username'">
                            <p class="truncate font-mono text-sm font-bold">@{{ username }}</p>
                            <p v-if="sisaUsername > 0" class="text-[10px] text-amber-600 dark:text-amber-400">Bisa diganti lagi dalam {{ sisaUsername }} hari</p>
                        </template>
                        <input v-else v-model="draft" class="mt-1 h-9 w-full rounded-lg border border-emerald-500/40 bg-white dark:bg-black/50 px-3 font-mono text-sm outline-none" />
                    </div>
                    <div class="flex shrink-0 gap-1.5">
                        <template v-if="editKey !== 'username'">
                            <button @click="mulaiEdit('username', username)" :disabled="sisaUsername > 0" class="h-8 rounded-lg bg-slate-900/[0.04] dark:bg-white/5 px-3 text-[11px] font-black disabled:opacity-40">Ubah</button>
                        </template>
                        <template v-else>
                            <button @click="simpanUsername" class="h-8 rounded-lg bg-emerald-500 px-3 text-[11px] font-black text-white hover:bg-emerald-400">Simpan</button>
                            <button @click="batalEdit" class="h-8 rounded-lg bg-slate-900/[0.04] dark:bg-white/5 px-3 text-[11px] font-bold">Batal</button>
                        </template>
                    </div>
                </div>

                <!-- Password -->
                <div class="flex items-center gap-2 rounded-lg border border-slate-200 dark:border-white/[0.06] bg-white dark:bg-black/30 px-3 py-2.5">
                    <div class="min-w-0 flex-1">
                        <p class="text-[10px] font-bold tracking-wider text-slate-400 dark:text-white/30 uppercase">Password</p>
                        <p class="font-mono text-sm font-bold tracking-widest">••••••</p>
                    </div>
                    <button @click="bukaPopupPass" class="h-8 shrink-0 rounded-lg bg-slate-900/[0.04] dark:bg-white/5 px-3 text-[11px] font-black">Ubah</button>
                </div>
            </div>

            <p v-if="msg" class="mt-3 rounded-lg px-3 py-2 text-[11px] font-bold" :class="msgOk ? 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300' : 'bg-rose-500/10 text-rose-700 dark:text-rose-300'">{{ msg }}</p>
        </div>

        <!-- ===== TEMA (semua peran) ===== -->
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

    <!-- ===== POPUP GANTI PASSWORD ===== -->
    <div v-if="showPass" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4 backdrop-blur-sm">
        <div class="w-full max-w-sm rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-[#111] p-5">
            <!-- Langkah 1 : password lama -->
            <template v-if="passStep === 1">
                <h3 class="text-sm font-black">Verifikasi password lama</h3>
                <label class="mt-3 block text-xs text-slate-500 dark:text-white/50">Password lama
                    <input v-model="passLama" type="password" placeholder="••••••••" class="mt-1 h-10 w-full rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-black/50 px-3 text-sm outline-none" />
                </label>
                <p v-if="passErr" class="mt-2 rounded-lg bg-rose-500/10 px-3 py-2 text-[11px] font-bold text-rose-700 dark:text-rose-300">{{ passErr }}</p>
                <div class="mt-4 flex gap-2">
                    <button @click="tutupPopupPass" class="h-10 flex-1 rounded-lg bg-slate-900/[0.04] dark:bg-white/5 text-sm font-bold">Batal</button>
                    <button @click="cekPassLama" class="h-10 flex-1 rounded-lg bg-emerald-500 text-sm font-black text-white hover:bg-emerald-400">Lanjut</button>
                </div>
            </template>
            <!-- Langkah 2 : password baru -->
            <template v-else>
                <h3 class="text-sm font-black">Password baru</h3>
                <label class="mt-3 block text-xs text-slate-500 dark:text-white/50">Password baru
                    <input v-model="passBaru" type="password" placeholder="Minimal 3 karakter" class="mt-1 h-10 w-full rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-black/50 px-3 text-sm outline-none" />
                </label>
                <label class="mt-2 block text-xs text-slate-500 dark:text-white/50">Konfirmasi password baru
                    <input v-model="passBaru2" type="password" placeholder="Ulangi password baru" class="mt-1 h-10 w-full rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-black/50 px-3 text-sm outline-none" />
                </label>
                <p v-if="passErr" class="mt-2 rounded-lg bg-rose-500/10 px-3 py-2 text-[11px] font-bold text-rose-700 dark:text-rose-300">{{ passErr }}</p>
                <p v-if="passOk" class="mt-2 rounded-lg bg-emerald-500/10 px-3 py-2 text-[11px] font-bold text-emerald-700 dark:text-emerald-300">{{ passOk }}</p>
                <div class="mt-4 flex gap-2">
                    <button @click="tutupPopupPass" class="h-10 flex-1 rounded-lg bg-slate-900/[0.04] dark:bg-white/5 text-sm font-bold">Batal</button>
                    <button @click="simpanPassBaru" class="h-10 flex-1 rounded-lg bg-emerald-500 text-sm font-black text-white hover:bg-emerald-400">Simpan</button>
                </div>
            </template>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import RoleDenied from '@/components/RoleDenied.vue';
import { hydrate, usePosStore } from '@/composables/usePosStore';
import { ROLE_COLOR, updateMockProfile, useAuthMock } from '@/composables/useAuthMock';
import { useAppearance } from '@/composables/useAppearance';
import { apiFetch } from '@/lib/api';

defineOptions({ layout: DashboardLayout });
const props = defineProps({ users: Array });
onMounted(() => {
    hydrate(props);
    muatProfil();
});
const store = usePosStore();
const auth = useAuthMock();
const { appearance, updateAppearance } = useAppearance();

/* Kasir tidak pegang profil (diatur admin) : hanya tema. */
const bisaAturProfil = computed(() => auth.role.value === 'admin' || auth.role.value === 'super admin');

const myId = computed(() => auth.user.value?.id_user ?? 0);
const mySchoolId = computed(() => {
    if (auth.user.value?.id_sekolah != null) return Number(auth.user.value.id_sekolah);
    const me = store.usersAktif.value.find((u) => Number(u.id_user) === Number(myId.value));
    if (me?.id_sekolah != null) return Number(me.id_sekolah);
    return auth.role.value === 'super admin' ? 0 : 1;
});

const nama = ref(auth.user.value?.nama_lengkap ?? '');
const username = ref(auth.user.value?.username ?? '');
const sisaNama = ref(0);
const sisaUsername = ref(0);
const msg = ref(''), msgOk = ref(true);

const editKey = ref(''); // '' | 'nama' | 'username'
const draft = ref('');

const COOLDOWN_NAMA = 7, COOLDOWN_USER = 30;
function sisaHari(changedAt, days) {
    if (!changedAt) return 0;
    const next = new Date(changedAt);
    next.setDate(next.getDate() + days);
    next.setHours(0, 0, 0, 0);
    const now = new Date();
    now.setHours(0, 0, 0, 0);
    return Math.max(0, Math.ceil((next - now) / 86400000));
}
function segarkanDariMock() {
    const me = store.usersAktif.value.find((u) => u.id_user === myId.value);
    nama.value = me?.nama_lengkap ?? auth.user.value?.nama_lengkap ?? '';
    username.value = me?.username ?? auth.user.value?.username ?? '';
    sisaNama.value = sisaHari(me?.nama_lengkap_changed_at, COOLDOWN_NAMA);
    sisaUsername.value = sisaHari(me?.username_changed_at, COOLDOWN_USER);
}
async function muatProfil() {
    segarkanDariMock();
    try {
        const res = await apiFetch('/api/settings/me');
        const d = res?.data;
        if (d) {
            nama.value = d.nama_lengkap ?? nama.value;
            username.value = d.username ?? username.value;
            sisaNama.value = Number(d.nama_lengkap_cooldown_sisa_hari ?? 0);
            sisaUsername.value = Number(d.username_cooldown_sisa_hari ?? 0);
            // Sinkron header/sidebar dengan nama DB asli (bukan mock basi).
            try { updateMockProfile(nama.value, username.value); } catch { /* abaikan */ }
        }
    } catch (e) {
        // mode lokal : pakai mock; tampilkan pesan hanya bila bukan 403 kasir
        if (e?.status && e.status !== 403) msg.value = '';
    }
}

function mulaiEdit(key, current) {
    editKey.value = key;
    draft.value = current ?? '';
    msg.value = '';
}
function batalEdit() {
    editKey.value = '';
    draft.value = '';
}

/* Simpan ke mock lokal (dipakai saat API tidak terjangkau). */
function simpanMock(patch) {
    const me = store.usersAktif.value.find((u) => u.id_user === myId.value);
    if (me) store.saveUser({ id_user: me.id_user, ...patch });
    if (patch.nama_lengkap || patch.username)
        updateMockProfile(patch.nama_lengkap ?? nama.value, patch.username ?? username.value);
    segarkanDariMock();
}
function notifOk(teks) { msg.value = teks; msgOk.value = true; }
function notifGagal(teks) { msg.value = teks; msgOk.value = false; }

async function simpanNama() {
    const val = draft.value.trim();
    if (!val) { notifGagal('Nama lengkap tidak boleh kosong.'); return; }
    try {
        await apiFetch('/api/settings/profile', { method: 'PUT', body: JSON.stringify({ nama_lengkap: val }) });
        simpanMock({ nama_lengkap: val, nama_lengkap_changed_at: new Date().toISOString() });
        batalEdit();
        notifOk('Nama lengkap berhasil diganti.');
        muatProfil();
    } catch (e) {
        notifGagal(e?.errors?.nama_lengkap?.[0] || e?.message || 'Gagal menyimpan nama.');
    }
}
async function simpanUsername() {
    const val = draft.value.trim();
    if (!val) { notifGagal('Username tidak boleh kosong.'); return; }
    if (val.length < 3) { notifGagal('Username minimal 3 karakter.'); return; }
    const duplikat = store.usersAktif.value.some((u) => u.id_user !== myId.value && (u.username || '').toLowerCase() === val.toLowerCase());
    if (duplikat) { notifGagal(`Username "${val}" sudah dipakai user lain.`); return; }
    try {
        await apiFetch('/api/settings/profile', { method: 'PUT', body: JSON.stringify({ username: val }) });
        simpanMock({ username: val, username_changed_at: new Date().toISOString() });
        batalEdit();
        notifOk('Username berhasil diganti.');
        muatProfil();
    } catch (e) {
        notifGagal(e?.errors?.username?.[0] || e?.message || 'Gagal menyimpan username.');
    }
}

/* ===== Popup password : langkah 1 verifikasi, langkah 2 simpan ===== */
const showPass = ref(false);
const passStep = ref(1);
const passLama = ref('');
const passBaru = ref('');
const passBaru2 = ref('');
const passErr = ref('');
const passOk = ref('');
function bukaPopupPass() {
    showPass.value = true;
    passStep.value = 1;
    passLama.value = ''; passBaru.value = ''; passBaru2.value = '';
    passErr.value = ''; passOk.value = '';
}
function tutupPopupPass() { showPass.value = false; }
async function cekPassLama() {
    passErr.value = '';
    if (!passLama.value) { passErr.value = 'Isi password lama dulu.'; return; }
    try {
        await apiFetch('/api/settings/password/check', { method: 'POST', body: JSON.stringify({ password_lama: passLama.value }) });
        passStep.value = 2;
    } catch (e) {
        passErr.value = e?.message || 'Password lama salah.';
    }
}
async function simpanPassBaru() {
    passErr.value = ''; passOk.value = '';
    if (!passBaru.value) { passErr.value = 'Password baru tidak boleh kosong.'; return; }
    if (passBaru.value.length < 3) { passErr.value = 'Password baru minimal 3 karakter.'; return; }
    if (passBaru.value !== passBaru2.value) { passErr.value = 'Konfirmasi password baru tidak sama.'; return; }
    try {
        await apiFetch('/api/settings/password', {
            method: 'PUT',
            body: JSON.stringify({ password_baru: passBaru.value, password_baru_confirmation: passBaru2.value }),
        });
        passOk.value = 'Password berhasil diganti.';
        setTimeout(tutupPopupPass, 900);
    } catch (e) {
        passErr.value = e?.errors?.password_baru?.[0] || e?.message || 'Gagal mengganti password.';
    }
}
</script>
