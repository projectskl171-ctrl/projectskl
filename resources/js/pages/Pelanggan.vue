<template>
    <RoleDenied v-if="!auth.can('/pelanggan')" page="Pelanggan" :needed="['Kasir']" />
    <div v-else class="space-y-4">
        <div class="grid grid-cols-3 gap-4">
            <div v-for="s in stats" :key="s.label" class="rounded-xl border border-white/[0.06] bg-white/[0.02] p-4">
                <p class="text-[11px] text-white/40">{{ s.label }}</p><p class="mt-1 text-xl font-black">{{ s.value }}</p>
            </div>
        </div>
        <div class="flex flex-col gap-2 sm:flex-row">
            <input v-model="q" placeholder="Cari nama / telepon…" class="h-10 flex-1 rounded-lg border border-white/[0.08] bg-black/40 px-3 text-sm outline-none placeholder:text-white/25" />
            <select v-model="fKel" class="h-10 rounded-lg border border-white/[0.08] bg-black/40 px-3 text-sm outline-none">
                <option :value="0">Semua kelompok</option>
                <option v-for="k in store.state.kelompokPelanggan" :key="k.id_kelompok_pelanggan" :value="k.id_kelompok_pelanggan">{{ k.nama_kelompok }}</option>
            </select>
            <button @click="baru" class="h-10 rounded-lg bg-pink-500 px-4 text-sm font-black text-white hover:bg-pink-400">+ Pelanggan</button>
        </div>
        <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-3">
            <div v-for="p in filtered" :key="p.id_pelanggan" class="rounded-xl border border-white/[0.06] bg-white/[0.02] p-4">
                <div class="flex items-start justify-between gap-2">
                    <div class="flex items-center gap-2.5">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-pink-500/15 text-sm font-black text-pink-300">{{ p.nama_pelanggan.slice(0, 1).toUpperCase() }}</div>
                        <div><p class="text-sm font-bold">{{ p.nama_pelanggan }}</p>
                        <p class="text-[11px] text-white/40">{{ store.namaKelompokPelanggan(p.id_kelompok_pelanggan) }}</p></div>
                    </div>
                    <div class="flex gap-1">
                        <button @click="edit(p)" class="rounded-md bg-white/5 px-2 py-1 text-[11px] font-bold hover:bg-white/10">Edit</button>
                        <button @click="store.deletePelanggan(p.id_pelanggan)" class="rounded-md bg-white/5 px-2 py-1 text-[11px] font-bold text-rose-300 hover:bg-rose-500/20">✕</button>
                    </div>
                </div>
                <p class="mt-3 text-xs text-white/50">📞 {{ p.telepon || '—' }}</p>
                <p class="mt-0.5 line-clamp-1 text-xs text-white/30">📍 {{ p.alamat || '—' }}</p>
                <div class="mt-3 flex items-center justify-between border-t border-white/[0.06] pt-2.5 text-[11px]">
                    <span class="text-white/40">{{trxCount(p.id_pelanggan)}} transaksi</span>
                    <span class="font-black text-emerald-400">{{ formatRupiahShort(belanja(p.id_pelanggan)) }}</span>
                </div>
            </div>
        </div>
        <p v-if="!filtered.length" class="rounded-xl border border-dashed border-white/10 py-10 text-center text-sm text-white/30">Tidak ada pelanggan.</p>
    </div>

    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4 backdrop-blur-sm" @click.self="show = false">
        <div class="w-full max-w-md rounded-xl border border-white/10 bg-[#111] p-5">
            <h3 class="text-sm font-black">{{ form.id_pelanggan ? 'Edit Pelanggan' : 'Pelanggan Baru' }} <span class="font-normal text-white/30">(tb_pelanggan)</span></h3>
            <div class="mt-4 space-y-2 text-xs">
                <label class="block text-white/50">Nama<input v-model="form.nama_pelanggan" class="mt-1 h-10 w-full rounded-lg border border-white/10 bg-black/50 px-3 text-sm text-white outline-none" /></label>
                <div class="grid grid-cols-2 gap-2">
                    <label class="block text-white/50">Kelompok
                        <select v-model="form.id_kelompok_pelanggan" class="mt-1 h-10 w-full rounded-lg border border-white/10 bg-black/50 px-2 text-sm text-white outline-none"><option v-for="k in store.state.kelompokPelanggan" :key="k.id_kelompok_pelanggan" :value="k.id_kelompok_pelanggan">{{ k.nama_kelompok }}</option></select></label>
                    <label class="block text-white/50">Telepon<input v-model="form.telepon" class="mt-1 h-10 w-full rounded-lg border border-white/10 bg-black/50 px-3 text-sm text-white outline-none" /></label>
                </div>
                <label class="block text-white/50">Alamat<textarea v-model="form.alamat" rows="2" class="mt-1 w-full rounded-lg border border-white/10 bg-black/50 px-3 py-2 text-sm text-white outline-none"></textarea></label>
            </div>
            <div class="mt-4 flex gap-2"><button @click="show = false" class="h-10 flex-1 rounded-lg bg-white/5 text-sm font-bold">Batal</button>
            <button @click="simpan" class="h-10 flex-1 rounded-lg bg-pink-500 text-sm font-black text-white hover:bg-pink-400">Simpan</button></div>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import RoleDenied from '@/components/RoleDenied.vue';
import { hydrate, usePosStore } from '@/composables/usePosStore';
import { useAuthMock } from '@/composables/useAuthMock';
import { formatRupiahShort } from '@/lib/format';

defineOptions({ layout: DashboardLayout });
const props = defineProps({ pelanggan: Array });
onMounted(() => hydrate(props));
const store = usePosStore();
const auth = useAuthMock();
const q = ref(''), fKel = ref(0), show = ref(false), form = ref({});

const filtered = computed(() => store.pelangganAktif.value.filter((p) => {
    const okQ = !q.value || p.nama_pelanggan.toLowerCase().includes(q.value.toLowerCase()) || (p.telepon || '').includes(q.value);
    return okQ && (!fKel.value || p.id_kelompok_pelanggan === Number(fKel.value));
}));
const trxCount = (id) => store.penjualanAktif.value.filter((t) => t.id_pelanggan === id).length;
const belanja = (id) => store.penjualanAktif.value.filter((t) => t.id_pelanggan === id).reduce((s, t) => s + t.total_faktur, 0);
const stats = computed(() => [
    { label: 'Total Pelanggan', value: store.pelangganAktif.value.length },
    ...store.state.kelompokPelanggan.map((k) => ({ label: k.nama_kelompok, value: store.pelangganAktif.value.filter((p) => p.id_kelompok_pelanggan === k.id_kelompok_pelanggan).length })),
]);
function baru() { form.value = { nama_pelanggan: '', id_kelompok_pelanggan: 1, telepon: '', alamat: '' }; show.value = true; }
function edit(p) { form.value = { ...p }; show.value = true; }
function simpan() { if (!form.value.nama_pelanggan) return; store.savePelanggan(form.value); show.value = false; }
</script>