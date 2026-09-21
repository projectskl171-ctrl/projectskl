<template>
    <RoleDenied v-if="!auth.can('/supplier')" page="Supplier" :needed="['Admin']" />
    <div v-else class="space-y-4">
        <div class="flex flex-col gap-2 sm:flex-row">
            <input v-model="q" placeholder="Cari supplier / telepon…" class="h-10 flex-1 rounded-lg border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-black/40 px-3 text-sm outline-none placeholder:text-slate-400 dark:placeholder:text-white/25" />
            <button @click="baru" class="h-10 rounded-lg bg-teal-500 px-4 text-sm font-black text-black hover:bg-teal-400">+ Supplier</button>
        </div>
        <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
            <div v-for="s in filtered" :key="s.id_supplier" class="rounded-xl border border-slate-200 dark:border-white/[0.06] bg-white dark:bg-white/[0.02] p-4">
                <div class="flex items-start justify-between gap-2">
                    <div class="flex items-center gap-2.5">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-teal-500/15 text-sm font-black text-teal-700 dark:text-teal-300">{{ s.nama.slice(0, 1).toUpperCase() }}</div>
                        <div><p class="text-sm font-bold">{{ s.nama }}</p><p class="text-[11px] text-slate-500 dark:text-white/40">📞 {{ s.no_telepon }}</p></div>
                    </div>
                    <div class="flex gap-1">
                        <button @click="edit(s)" class="rounded-md bg-slate-900/[0.04] dark:bg-white/5 px-2 py-1 text-[11px] font-bold hover:bg-slate-900/5 dark:hover:bg-white/10">Edit</button>
                        <button @click="store.deleteSupplier(s.id_supplier)" class="rounded-md bg-slate-900/[0.04] dark:bg-white/5 px-2 py-1 text-[11px] font-bold text-rose-700 dark:text-rose-300 hover:bg-rose-500/20">✕</button>
                    </div>
                </div>
                <p class="mt-2 line-clamp-1 text-xs text-slate-400 dark:text-white/30">📍 {{ s.alamat_supplier }}</p>
                <div class="mt-3 grid grid-cols-3 gap-2 border-t border-slate-200 dark:border-white/[0.06] pt-2.5 text-center">
                    <div><p class="text-sm font-black">{{ produkCount(s.id_supplier) }}</p><p class="text-[10px] text-slate-400 dark:text-white/35">SKU dipasok</p></div>
                    <div><p class="text-sm font-black">{{ beliCount(s.id_supplier) }}</p><p class="text-[10px] text-slate-400 dark:text-white/35">Pembelian</p></div>
                    <div><p class="text-sm font-black text-teal-700 dark:text-teal-300">{{ formatRupiahShort(nilaiBeli(s.id_supplier)) }}</p><p class="text-[10px] text-slate-400 dark:text-white/35">Nilai beli</p></div>
                </div>
            </div>
        </div>
        <p v-if="!filtered.length" class="rounded-xl border border-dashed border-slate-200 dark:border-white/10 py-10 text-center text-sm text-slate-400 dark:text-white/30">Tidak ada supplier.</p>
    </div>

    <div v-if="show && auth.can('/supplier')" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4 backdrop-blur-sm" @click.self="show = false">
        <div class="w-full max-w-md rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-[#111] p-5">
            <h3 class="text-sm font-black">{{ form.id_supplier ? 'Edit Supplier' : 'Supplier Baru' }} <span class="font-normal text-slate-400 dark:text-white/30">(tb_supplier)</span></h3>
            <div class="mt-4 space-y-2 text-xs">
                <label class="block text-slate-500 dark:text-white/50">Nama supplier<input v-model="form.nama" class="mt-1 h-10 w-full rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-black/50 px-3 text-sm text-slate-900 dark:text-white outline-none" /></label>
                <label class="block text-slate-500 dark:text-white/50">No. telepon<input v-model="form.no_telepon" class="mt-1 h-10 w-full rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-black/50 px-3 text-sm text-slate-900 dark:text-white outline-none" /></label>
                <label class="block text-slate-500 dark:text-white/50">Alamat<textarea v-model="form.alamat_supplier" rows="2" class="mt-1 w-full rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-black/50 px-3 py-2 text-sm text-slate-900 dark:text-white outline-none"></textarea></label>
            </div>
            <div class="mt-4 flex gap-2"><button @click="show = false" class="h-10 flex-1 rounded-lg bg-slate-900/[0.04] dark:bg-white/5 text-sm font-bold">Batal</button>
            <button @click="simpan" class="h-10 flex-1 rounded-lg bg-teal-500 text-sm font-black text-black hover:bg-teal-400">Simpan</button></div>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import { hydrate, usePosStore } from '@/composables/usePosStore';
import RoleDenied from '@/components/RoleDenied.vue';
import { useAuthMock } from '@/composables/useAuthMock';
import { formatRupiahShort } from '@/lib/format';

defineOptions({ layout: DashboardLayout });
const props = defineProps({ supplier: Array, barang: Array, pembelian: Array });
onMounted(() => hydrate(props));
const store = usePosStore();
const auth = useAuthMock();
const q = ref(''), show = ref(false), form = ref({});

const filtered = computed(() => store.supplierAktif.value.filter((s) =>
    !q.value || s.nama.toLowerCase().includes(q.value.toLowerCase()) || (s.no_telepon || '').includes(q.value)));
const produkCount = (id) => store.barangAktif.value.filter((b) => b.id_supplier === id).length;
const beliCount = (id) => store.pembelianAktif.value.filter((p) => p.id_supplier === id).length;
const nilaiBeli = (id) => store.pembelianAktif.value.filter((p) => p.id_supplier === id).reduce((s, p) => s + p.total_bayar, 0);
function baru() { form.value = { nama: '', no_telepon: '', alamat_supplier: '' }; show.value = true; }
function edit(s) { form.value = { ...s }; show.value = true; }
function simpan() { if (!form.value.nama) return; store.saveSupplier(form.value); show.value = false; }
</script>
