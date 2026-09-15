<template>
    <RoleDenied v-if="!auth.can('/user')" page="User" :needed="['Admin', 'Super Admin']" />
    <div v-else class="space-y-4">
        <div v-if="auth.role.value === 'admin'" class="rounded-xl border border-violet-500/20 bg-violet-500/[0.06] px-4 py-3 text-xs text-white/70">
            👮 <b>Mode Admin:</b> kamu hanya boleh melihat, menambah, mengedit & menonaktifkan akun <b>kasir di sekolahmu</b>.
            Kelola admin hanya bisa dilakukan <b>Super Admin</b>.
        </div>
        <div v-else class="rounded-xl border border-emerald-500/20 bg-emerald-500/[0.06] px-4 py-3 text-xs text-white/70">
            🛡️ <b>Mode Super Admin:</b> kendali penuh — tambah/kurangi <b>admin</b> maupun <b>kasir</b>.
        </div>

        <div class="grid grid-cols-3 gap-4">
            <div v-for="s in stats" :key="s.label" class="rounded-xl border border-white/[0.06] bg-white/[0.02] p-4">
                <p class="text-[11px] text-white/40">{{ s.label }}</p><p class="mt-1 text-xl font-black">{{ s.value }}</p>
            </div>
        </div>
        <div class="flex flex-col gap-2 sm:flex-row">
            <input v-model="q" placeholder="Cari nama / username…" class="h-10 flex-1 rounded-lg border border-white/[0.08] bg-black/40 px-3 text-sm outline-none placeholder:text-white/25" />
            <select v-model="fRole" class="h-10 rounded-lg border border-white/[0.08] bg-black/40 px-3 text-sm outline-none">
                <option :value="0">Semua role</option>
                <option v-for="r in roleOptions" :key="r.id" :value="r.id">{{ r.nama }}</option>
            </select>
            <button @click="baru" class="h-10 rounded-lg bg-indigo-500 px-4 text-sm font-black text-white hover:bg-indigo-400">+ {{ auth.role.value === 'admin' ? 'Kasir' : 'User' }}</button>
        </div>
        <div class="overflow-x-auto rounded-xl border border-white/[0.06]">
            <table class="w-full min-w-[680px] text-left text-xs">
                <thead><tr class="bg-white/[0.03] text-[10px] tracking-wider text-white/40 uppercase">
                    <th class="px-4 py-2.5">User</th><th class="px-3 py-2.5">Role</th><th class="px-3 py-2.5 text-right">Transaksi</th><th class="px-3 py-2.5 text-right">Status</th><th class="px-3 py-2.5 text-right">Aksi</th>
                </tr></thead>
                <tbody>
                    <tr v-for="u in filtered" :key="u.id_user" class="border-t border-white/[0.05] hover:bg-white/[0.02]">
                        <td class="px-4 py-2.5">
                            <div class="flex items-center gap-2.5">
                                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-indigo-400 to-indigo-600 text-xs font-black">{{ u.nama_lengkap.slice(0, 1).toUpperCase() }}</div>
                                <div><p class="font-bold">{{ u.nama_lengkap }} <span v-if="u.id_user === auth.user.value?.id_user" class="rounded bg-white/10 px-1 text-[9px] text-white/50">KAMU</span></p><p class="font-mono text-[10px] text-white/30">@{{ u.username }} • {{ store.namaSekolah(u.id_sekolah) }}</p></div>
                            </div>
                        </td>
                        <td class="px-3 py-2.5"><span class="rounded px-1.5 py-0.5 text-[10px] font-black" :class="roleClass(u.id_role)">{{ store.namaRole(u.id_role) }}</span></td>
                        <td class="px-3 py-2.5 text-right font-bold">{{ jualCount(u.id_user) }} <span class="font-normal text-white/30">jual</span></td>
                        <td class="px-3 py-2.5 text-right"><button @click="toggle(u)" :disabled="!bisaUbah(u)" :class="[u.is_active ? 'bg-emerald-500/15 text-emerald-300' : 'bg-white/5 text-white/40', !bisaUbah(u) && 'cursor-not-allowed opacity-40']" class="rounded-md px-2 py-1 text-[11px] font-black">{{ u.is_active ? 'Aktif' : 'Nonaktif' }}</button></td>
                        <td class="px-3 py-2.5 text-right"><button v-if="bisaUbah(u)" @click="edit(u)" class="rounded-md bg-white/5 px-2 py-1 text-[11px] font-bold hover:bg-white/10">Edit</button><span v-else class="text-[10px] text-white/25">🔒</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="text-[11px] text-white/25">tb_user + roles • admin: CRUD kasir sekolahnya • super admin: CRUD admin & kasir + atur sekolahnya</p>
    </div>

    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4 backdrop-blur-sm" @click.self="show = false">
        <div class="w-full max-w-md rounded-xl border border-white/10 bg-[#111] p-5">
            <h3 class="text-sm font-black">{{ form.id_user ? 'Edit User' : (auth.role.value === 'admin' ? 'Kasir Baru' : 'User Baru') }}</h3>
            <div class="mt-4 grid grid-cols-2 gap-2 text-xs">
                <label class="col-span-2 block text-white/50">Nama lengkap<input v-model="form.nama_lengkap" class="mt-1 h-10 w-full rounded-lg border border-white/10 bg-black/50 px-3 text-sm text-white outline-none" /></label>
                <label class="block text-white/50">Username<input v-model="form.username" class="mt-1 h-10 w-full rounded-lg border border-white/10 bg-black/50 px-3 font-mono text-sm text-white outline-none" /></label>
                <label v-if="auth.role.value === 'super admin'" class="block text-white/50">Role
                    <select v-model="form.id_role" class="mt-1 h-10 w-full rounded-lg border border-white/10 bg-black/50 px-2 text-sm text-white outline-none"><option :value="2">admin</option><option :value="3">kasir</option></select></label>
                <label v-if="auth.role.value === 'super admin'" class="block text-white/50">Sekolah
                    <select v-model="form.id_sekolah" class="mt-1 h-10 w-full rounded-lg border border-white/10 bg-black/50 px-2 text-sm text-white outline-none"><option v-for="s in store.state.sekolah" :key="s.id_sekolah" :value="s.id_sekolah">{{ s.nama_sekolah }}{{ s.is_active ? '' : ' (nonaktif)' }}</option></select></label>
                <label v-else class="flex items-end pb-2 text-white/50"><span class="rounded bg-blue-500/15 px-2 py-1 text-[11px] font-black text-blue-300">Role: kasir (dikunci)</span></label>
                <label class="col-span-2 block text-white/50">Password {{ form.id_user ? '(kosongkan = tidak diubah)' : '' }}<input v-model="form.password" type="password" placeholder="••••••••" class="mt-1 h-10 w-full rounded-lg border border-white/10 bg-black/50 px-3 text-sm text-white outline-none" /></label>
            </div>
            <div class="mt-4 flex gap-2"><button @click="show = false" class="h-10 flex-1 rounded-lg bg-white/5 text-sm font-bold">Batal</button>
            <button @click="simpan" class="h-10 flex-1 rounded-lg bg-indigo-500 text-sm font-black text-white hover:bg-indigo-400">Simpan</button></div>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import RoleDenied from '@/components/RoleDenied.vue';
import { hydrate, usePosStore } from '@/composables/usePosStore';
import { useAuthMock } from '@/composables/useAuthMock';

defineOptions({ layout: DashboardLayout });
const props = defineProps({ users: Array });
onMounted(() => hydrate(props));
const store = usePosStore();
const auth = useAuthMock();
const q = ref(''), fRole = ref(0), show = ref(false), form = ref({});

/* Sekolah milik user yang login (admin terkunci 1 sekolah, super admin = 0/semua).
   Diambil dari tb_user agar admin sekolah lain pun scope-nya benar. */
const mySchool = computed(() => {
    const me = store.usersAktif.value.find((u) => u.id_user === auth.user.value?.id_user);
    if (me) return me.id_sekolah;
    return auth.role.value === 'super admin' ? 0 : 1;
});

/* admin hanya melihat/mengelola kasir DI SEKOLAHNYA; super admin semua user */
const scope = computed(() => auth.role.value === 'admin'
    ? store.usersAktif.value.filter((u) => u.id_role === 3 && u.id_sekolah === (mySchool.value || 1))
    : store.usersAktif.value);
const roleOptions = computed(() => auth.role.value === 'admin'
    ? [{ id: 3, nama: 'kasir' }]
    : [{ id: 2, nama: 'admin' }, { id: 3, nama: 'kasir' }]);
const bisaUbah = (u) => {
    if (auth.role.value === 'super admin') return u.id_user !== 1; // akun super utama dikunci
    if (auth.role.value === 'admin') return u.id_role === 3 && u.id_user !== auth.user.value?.id_user;
    return false;
};
const filtered = computed(() => scope.value.filter((u) => {
    const okQ = !q.value || u.nama_lengkap.toLowerCase().includes(q.value.toLowerCase()) || u.username.toLowerCase().includes(q.value.toLowerCase());
    return okQ && (!fRole.value || u.id_role === Number(fRole.value));
}).sort((a, b) => a.id_role - b.id_role));
const roleClass = (r) => r === 1 ? 'bg-rose-500/15 text-rose-300' : r === 2 ? 'bg-violet-500/15 text-violet-300' : 'bg-blue-500/15 text-blue-300';
const jualCount = (id) => store.penjualanAktif.value.filter((t) => t.id_user === id).length;
const stats = computed(() => auth.role.value === 'admin'
    ? [
        { label: 'Kasir Terdaftar', value: scope.value.length },
        { label: 'Kasir Aktif', value: scope.value.filter((u) => u.is_active).length },
        { label: 'Kasir Nonaktif', value: scope.value.filter((u) => !u.is_active).length },
      ]
    : [
        { label: 'Total User', value: store.usersAktif.value.length },
        { label: 'Admin', value: store.usersAktif.value.filter((u) => u.id_role === 2).length },
        { label: 'Kasir', value: store.usersAktif.value.filter((u) => u.id_role === 3).length },
      ]);
function baru() { form.value = { nama_lengkap: '', username: '', id_role: 3, id_sekolah: auth.role.value === 'admin' ? (mySchool.value || 1) : (store.sekolahAktif.value[0]?.id_sekolah ?? 1), password: '' }; show.value = true; }
function edit(u) { form.value = { ...u, password: '' }; show.value = true; }
function toggle(u) { if (bisaUbah(u)) store.toggleUser(u.id_user); }
function simpan() {
    if (!form.value.nama_lengkap || !form.value.username) return;
    const payload = { ...form.value };
    if (auth.role.value === 'admin') { payload.id_role = 3; payload.id_sekolah = mySchool.value || 1; } // paksa kasir sekolah sendiri
    if (!payload.password) delete payload.password;
    else payload.password = '— hashed —';
    store.saveUser(payload); show.value = false;
}
</script>
