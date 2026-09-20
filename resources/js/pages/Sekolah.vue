<template>
    <RoleDenied v-if="!auth.can('/sekolah')" page="Sekolah" :needed="['Super Admin']" />
    <div v-else class="space-y-4">
        <div class="grid grid-cols-1 gap-3 xl:grid-cols-2">
            <div v-for="s in sekolahList" :key="s.id_sekolah" class="rounded-xl border border-white/[0.06] bg-white/[0.02] p-5">
                <div class="flex items-center justify-between gap-2">
                    <p class="font-mono text-[10px] font-bold text-white/30">{{ s.kode_sekolah }}</p>
                    <div class="flex items-center gap-2">
                        <span class="rounded px-1.5 py-0.5 text-[10px] font-black" :class="statusClass(s)">{{ labelStatus(s) }}</span>
                        <button v-if="bisaToggle" @click="toggleAktif(s)" class="h-7 rounded-md bg-white/5 px-2.5 text-[11px] font-black text-white/70 hover:bg-white/10">{{ s.is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                    </div>
                </div>
                <p class="mt-2 text-[10px] text-white/40">Status langganan: {{ s.status_langganan ?? (s.is_active ? 'aktif' : 'terdaftar') }} • bayar terakhir: {{ s.bulan_terakhir_bayar || 'belum ada' }}</p>
                <label class="mt-2 block text-xs text-white/50">Nama sekolah<input v-model="s.nama_sekolah" class="mt-1 h-10 w-full rounded-lg border border-white/10 bg-black/50 px-3 text-sm font-bold text-white outline-none" /></label>
                <label class="mt-2 block text-xs text-white/50">Alamat<textarea v-model="s.alamat_sekolah" rows="2" class="mt-1 w-full rounded-lg border border-white/10 bg-black/50 px-3 py-2 text-sm text-white outline-none"></textarea></label>
                <label class="mt-2 block text-xs text-white/50">Website<input v-model="s.website" class="mt-1 h-10 w-full rounded-lg border border-white/10 bg-black/50 px-3 text-sm text-white outline-none" /></label>
                <button @click="simpan(s)" class="mt-3 h-9 rounded-lg bg-white/5 px-4 text-xs font-black text-white/70 hover:bg-white/10">Simpan {{ s.nama_sekolah }}</button>
            </div>
        </div>
        <p v-if="smsg" class="rounded-lg px-3 py-2 text-[11px] font-bold" :class="smsgOk ? 'bg-emerald-500/10 text-emerald-300' : 'bg-rose-500/10 text-rose-300'">{{ smsg }}</p>
        <p class="text-[11px] text-white/25">tb_sekolah • kelola semua sekolah + toggle aktif/nonaktif (sekolah nonaktif hilang dari dashboard & scope).</p>
    </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import RoleDenied from '@/components/RoleDenied.vue';
import { hydrate, usePosStore } from '@/composables/usePosStore';
import { useAuthMock } from '@/composables/useAuthMock';

defineOptions({ layout: DashboardLayout });
const props = defineProps({ sekolah: Array });
onMounted(() => hydrate(props));
const store = usePosStore();
const auth = useAuthMock();

/* Admin terkunci 1 sekolah, super admin = semua. */
const sekolahList = ref(JSON.parse(JSON.stringify(
    auth.role.value === 'super admin' ? store.state.sekolah : store.state.sekolah.filter((s) => s.id_sekolah === 1),
)));
const bisaToggle = auth.role.value === 'super admin';
const smsg = ref(''), smsgOk = ref(true);

function simpan(s) {
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
</script>
