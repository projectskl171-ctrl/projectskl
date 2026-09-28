<template>
    <RoleDenied v-if="!auth.can('/user')" page="User" :needed="['Admin', 'Super Admin']" />
    <div v-else class="space-y-4">
        <div class="grid grid-cols-3 gap-4">
            <div v-for="s in stats" :key="s.label" class="rounded-xl border border-slate-200 dark:border-white/[0.06] bg-white dark:bg-white/[0.02] p-4 shadow-sm dark:shadow-none">
                <p class="text-[11px] text-slate-500 dark:text-white/40">{{ s.label }}</p><p class="mt-1 text-xl font-black">{{ s.value }}</p>
            </div>
        </div>
        <div class="flex flex-col gap-2 sm:flex-row">
            <input v-model="q" placeholder="Cari nama / username…" class="h-10 flex-1 rounded-lg border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-black/40 px-3 text-sm outline-none placeholder:text-slate-400 dark:placeholder:text-white/25" />
            <select v-if="auth.role.value === 'super admin'" v-model="fSekolah" class="h-10 rounded-lg border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-black/40 px-3 text-sm outline-none">
                <option :value="0">Semua sekolah</option>
                <option v-for="s in store.state.sekolah" :key="s.id_sekolah" :value="s.id_sekolah">{{ s.nama_sekolah }}{{ s.is_active ? '' : ' (nonaktif)' }}</option>
            </select>
            <button @click="baru" class="h-10 rounded-lg bg-indigo-500 px-4 text-sm font-black text-white hover:bg-indigo-400">+ {{ auth.role.value === 'admin' ? 'Kasir' : 'Admin' }}</button>
        </div>
        <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-white/[0.06] shadow-sm dark:shadow-none">
            <table class="w-full min-w-[680px] text-left text-xs">
                <thead><tr class="bg-white/[0.03] text-[10px] tracking-wider text-slate-500 dark:text-white/40 uppercase">
                    <th class="px-4 py-2.5">User</th><th class="px-3 py-2.5">Role</th><th class="px-3 py-2.5 text-right">Transaksi</th><th class="px-3 py-2.5 text-right">Status</th><th class="px-3 py-2.5 text-right">Aksi</th>
                </tr></thead>
                <tbody>
                    <tr v-for="u in paged" :key="u.id_user" class="border-t border-slate-200 dark:border-white/[0.05] hover:bg-emerald-600/5 dark:hover:bg-white/[0.02]">
                        <td class="px-4 py-2.5">
                            <div class="flex items-center gap-2.5">
                                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-indigo-400 to-indigo-600 text-xs font-black">{{ u.nama_lengkap.slice(0, 1).toUpperCase() }}</div>
                                <div><p class="font-bold">{{ u.nama_lengkap }} <span v-if="u.id_user === auth.user.value?.id_user" class="rounded bg-slate-900/[0.06] dark:bg-white/10 px-1 text-[9px] text-slate-500 dark:text-white/50">KAMU</span></p><p class="font-mono text-[10px] text-slate-400 dark:text-white/30">@{{ u.username }} • {{ store.namaSekolah(u.id_sekolah) }}</p></div>
                            </div>
                        </td>
                        <td class="px-3 py-2.5"><span class="rounded px-1.5 py-0.5 text-[10px] font-black" :class="roleClass(u.id_role)">{{ store.namaRole(u.id_role) }}</span></td>
                        <td class="px-3 py-2.5 text-right font-bold">{{ jualCount(u.id_user) }} <span class="font-normal text-slate-400 dark:text-white/30">jual</span></td>
                        <td class="px-3 py-2.5 text-right"><button @click="toggle(u)" :disabled="!bisaToggle(u)" :class="[u.is_active ? 'bg-emerald-500/15 text-emerald-700 dark:text-emerald-300' : 'bg-slate-900/[0.04] dark:bg-white/5 text-slate-500 dark:text-white/40', !bisaToggle(u) && 'cursor-not-allowed opacity-40']" class="rounded-md px-2 py-1 text-[11px] font-black">{{ u.is_active ? 'Aktif' : 'Nonaktif' }}</button></td>
                        <td class="px-3 py-2.5 text-right whitespace-nowrap">
                            <button v-if="bisaUbah(u)" @click="edit(u)" class="rounded-md bg-slate-900/[0.04] dark:bg-white/5 px-2 py-1 text-[11px] font-bold hover:bg-slate-900/5 dark:hover:bg-white/10">Edit</button>
                            <button v-if="bisaHapus(u)" @click="hapus(u)" class="ml-1 rounded-md bg-slate-900/[0.04] dark:bg-white/5 px-2 py-1 text-[11px] font-bold text-rose-700 dark:text-rose-300 hover:bg-rose-500/20">Hapus</button>
                            <span v-if="!bisaUbah(u) && !bisaHapus(u)" class="text-[10px] text-slate-400 dark:text-white/25">🔒</span>
                        </td>
                    </tr>
                </tbody>
            </table>
            <p v-if="!filtered.length" class="py-10 text-center text-sm text-slate-400 dark:text-white/30">Tidak ada user yang cocok.</p>
        </div>
        <Pagination :page="page" :total-pages="totalPages" @update:page="page = $event" />
    </div>

    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4 backdrop-blur-sm" @click.self="show = false">
        <div class="w-full max-w-md rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-[#111] p-5 shadow-sm dark:shadow-none">
            <h3 class="text-sm font-black">{{ form.id_user ? 'Edit User' : (auth.role.value === 'admin' ? 'Kasir Baru' : 'Admin Baru') }}</h3>
            <div class="mt-4 grid grid-cols-2 gap-2 text-xs">
                <label class="col-span-2 block text-slate-500 dark:text-white/50">Nama lengkap<input v-model="form.nama_lengkap" class="mt-1 h-10 w-full rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-black/50 px-3 text-sm text-slate-900 dark:text-white outline-none" /></label>
                <label class="block text-slate-500 dark:text-white/50">Username<input v-model="form.username" class="mt-1 h-10 w-full rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-black/50 px-3 font-mono text-sm text-slate-900 dark:text-white outline-none" /></label>
                <label v-if="auth.role.value === 'super admin' && !form.id_user" class="block text-slate-500 dark:text-white/50">Sekolah
                    <select v-model="form.id_sekolah" class="mt-1 h-10 w-full rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-black/50 px-2 text-sm text-slate-900 dark:text-white outline-none"><option v-for="s in store.state.sekolah" :key="s.id_sekolah" :value="s.id_sekolah">{{ s.nama_sekolah }}{{ s.is_active ? '' : ' (nonaktif)' }}</option></select></label>
                <label class="col-span-2 block text-slate-500 dark:text-white/50">Password {{ form.id_user ? '(kosongkan = tidak diubah)' : '(wajib diisi)' }}<input v-model="form.password" type="password" placeholder="••••••••" class="mt-1 h-10 w-full rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-black/50 px-3 text-sm text-slate-900 dark:text-white outline-none" /></label>
            </div>
            <p v-if="ferr" class="mt-2 rounded-lg bg-rose-500/10 px-3 py-2 text-[11px] font-bold text-rose-700 dark:text-rose-300">{{ ferr }}</p>
            <div class="mt-4 flex gap-2"><button @click="show = false" class="h-10 flex-1 rounded-lg bg-slate-900/[0.04] dark:bg-white/5 text-sm font-bold">Batal</button>
            <button @click="simpan" class="h-10 flex-1 rounded-lg bg-indigo-500 text-sm font-black text-white hover:bg-indigo-400">Simpan</button></div>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import RoleDenied from '@/components/RoleDenied.vue';
import Pagination from '@/components/Pagination.vue';
import { usePagination } from '@/composables/usePagination';
import { hydrate, usePosStore } from '@/composables/usePosStore';
import { useAuthMock } from '@/composables/useAuthMock';

defineOptions({ layout: DashboardLayout });
const props = defineProps({ users: Array });
onMounted(() => hydrate(props));
const store = usePosStore();
const auth = useAuthMock();
const q = ref(''), fSekolah = ref(0), show = ref(false), form = ref({}), ferr = ref('');

/* Sekolah milik user yang login (admin terkunci 1 sekolah, super admin = 0/semua). */
const mySchool = computed(() => {
    const me = store.usersAktif.value.find((u) => u.id_user === auth.user.value?.id_user);
    if (me) return me.id_sekolah;
    return auth.role.value === 'super admin' ? 0 : 1;
});

/* admin: hanya kasir DI SEKOLAHNYA. super admin: admin + super admin saja
   (kasir urusan admin sekolah masing-masing). */
const scope = computed(() => {
    if (auth.role.value === 'admin')
        return store.usersAktif.value.filter((u) => u.id_role === 3 && u.id_sekolah === (mySchool.value || 1));
    return store.usersAktif.value.filter((u) => u.id_role === 1 || u.id_role === 2);
});
/* Super admin tidak bisa mengubah username/fullname/password siapa pun
   (pakai Settings untuk diri sendiri). Admin hanya mengelola kasir
   sekolahnya, tidak bisa menyentuh akunnya sendiri di sini. */
const bisaUbah = (u) => {
    if (auth.role.value === 'admin') return u.id_role === 3 && u.id_user !== auth.user.value?.id_user;
    return false;
};
/* Toggle aktif/nonaktif: admin untuk kasirnya, super admin hanya untuk admin. */
const bisaToggle = (u) => {
    if (u.id_user === auth.user.value?.id_user) return false;
    if (auth.role.value === 'super admin') return u.id_role === 2;
    if (auth.role.value === 'admin') return u.id_role === 3;
    return false;
};
const bisaHapus = (u) => {
    if (u.id_user === auth.user.value?.id_user) return false;
    if (auth.role.value === 'super admin') return u.id_role === 2;
    if (auth.role.value === 'admin') return u.id_role === 3;
    return false;
};
const { page, totalPages, paged, filtered } = usePagination(() => scope.value.filter((u) => {
    const okQ = !q.value || u.nama_lengkap.toLowerCase().includes(q.value.toLowerCase()) || u.username.toLowerCase().includes(q.value.toLowerCase());
    const okS = auth.role.value !== 'super admin' || !fSekolah.value || u.id_sekolah === Number(fSekolah.value);
    return okQ && okS;
}).sort((a, b) => a.id_role - b.id_role), 10, [q, fSekolah]);
const roleClass = (r) => r === 1 ? 'bg-rose-500/15 text-rose-700 dark:text-rose-300' : r === 2 ? 'bg-violet-500/15 text-violet-700 dark:text-violet-300' : 'bg-blue-500/15 text-blue-700 dark:text-blue-300';
const jualCount = (id) => store.penjualanAktif.value.filter((t) => t.id_user === id).length;
const stats = computed(() => auth.role.value === 'admin'
    ? [
        { label: 'Kasir Terdaftar', value: scope.value.length },
        { label: 'Kasir Aktif', value: scope.value.filter((u) => u.is_active).length },
        { label: 'Kasir Nonaktif', value: scope.value.filter((u) => !u.is_active).length },
      ]
    : [
        { label: 'Admin Terdaftar', value: scope.value.filter((u) => u.id_role === 2).length },
        { label: 'Admin Aktif', value: scope.value.filter((u) => u.id_role === 2 && u.is_active).length },
        { label: 'Super Admin', value: scope.value.filter((u) => u.id_role === 1).length },
      ]);
function baru() {
    ferr.value = '';
    form.value = {
        nama_lengkap: '', username: '',
        id_role: auth.role.value === 'admin' ? 3 : 2,
        id_sekolah: auth.role.value === 'admin' ? (mySchool.value || 1) : (store.sekolahAktif.value[0]?.id_sekolah ?? 1),
        password: '',
    };
    show.value = true;
}
function edit(u) { ferr.value = ''; form.value = { ...u, password: '' }; show.value = true; }
function toggle(u) { if (bisaToggle(u)) store.toggleUser(u.id_user); }
function hapus(u) {
    if (!bisaHapus(u)) return;
    if (!confirm(`Hapus ${u.nama_lengkap} (@${u.username})?`)) return;
    store.deleteUser(u.id_user);
}
function simpan() {
    ferr.value = '';
    if (!form.value.nama_lengkap?.trim() || !form.value.username?.trim()) { ferr.value = 'Nama lengkap & username wajib diisi.'; return; }
    const duplikat = store.usersAktif.value.some((u) =>
        u.id_user !== form.value.id_user && u.username.toLowerCase() === form.value.username.trim().toLowerCase());
    if (duplikat) { ferr.value = `Username "${form.value.username.trim()}" sudah dipakai user lain.`; return; }
    if (!form.value.id_user && !form.value.password) { ferr.value = 'Password wajib diisi untuk user baru.'; return; }
    const payload = { ...form.value, nama_lengkap: form.value.nama_lengkap.trim(), username: form.value.username.trim() };
    if (auth.role.value === 'admin') { payload.id_role = 3; payload.id_sekolah = mySchool.value || 1; } // paksa kasir sekolah sendiri
    if (auth.role.value === 'super admin' && !payload.id_user) { payload.id_role = 2; } // +Admin otomatis admin
    if (auth.role.value === 'super admin' && payload.id_user) { delete payload.id_sekolah; delete payload.id_role; }
    if (!payload.password) delete payload.password;
    else payload.password = '— hashed —';
    store.saveUser(payload); show.value = false;
}
</script>
