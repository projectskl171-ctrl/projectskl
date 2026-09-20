<template>
    <RoleDenied v-if="!auth.can('/produk')" page="Produk" :needed="['Admin']" />
    <div v-else class="space-y-4">
        <div class="grid grid-cols-2 gap-4 xl:grid-cols-4">
            <div v-for="s in stats" :key="s.label" class="rounded-xl border border-white/[0.06] bg-white/[0.02] p-4">
                <p class="text-[11px] text-white/40">{{ s.label }}</p><p class="mt-1 text-xl font-black">{{ s.value }}</p>
            </div>
        </div>

        <div class="flex flex-col gap-2 lg:flex-row">
            <input v-model="q" placeholder="Cari nama / barcode…" class="h-10 flex-1 rounded-lg border border-white/[0.08] bg-black/40 px-3 text-sm outline-none placeholder:text-white/25" />
            <select v-model="fKat" class="h-10 rounded-lg border border-white/[0.08] bg-black/40 px-3 text-sm outline-none">
                <option :value="0">Semua kategori</option>
                <option v-for="k in store.state.kategori" :key="k.id_kategori" :value="k.id_kategori">{{ k.nama }}</option>
            </select>
            <select v-model="fStok" class="h-10 rounded-lg border border-white/[0.08] bg-black/40 px-3 text-sm outline-none">
                <option value="">Semua stok</option><option value="tipis">Menipis ≤10</option><option value="habis">Habis</option><option value="aman">Aman</option>
            </select>
            <button @click="baru" class="h-10 rounded-lg bg-amber-500 px-4 text-sm font-black text-black hover:bg-amber-400">+ Produk</button>
        </div>

        <div class="overflow-x-auto rounded-xl border border-white/[0.06]">
            <table class="w-full min-w-[820px] text-left text-xs">
                <thead><tr class="bg-white/[0.03] text-[10px] tracking-wider text-white/40 uppercase">
                    <th class="px-4 py-2.5">Barang (barcode)</th><th class="px-3 py-2.5">Kategori</th><th class="px-3 py-2.5 text-right">Beli → Jual</th><th class="px-3 py-2.5 text-right">Margin</th><th class="px-3 py-2.5 text-right">Stok</th><th class="px-3 py-2.5 text-right">Aksi</th>
                </tr></thead>
                <tbody>
                    <tr v-for="b in filtered" :key="b.id_barang" class="border-t border-white/[0.05] hover:bg-white/[0.02]">
                        <td class="px-4 py-2.5"><p class="font-bold">{{ b.nama }}</p><p class="font-mono text-[10px] text-white/30">{{ b.barcode }} • {{ b.satuan }} • {{ store.namaSupplier(b.id_supplier) }}</p></td>
                        <td class="px-3 py-2.5"><span class="rounded bg-white/5 px-1.5 py-0.5 text-[10px] font-bold text-white/60">{{ store.namaKategori(b.id_kategori) }}</span><p class="mt-0.5 text-[10px] text-white/30">{{ store.namaKelompok(b.id_kelompok_kategori) }}</p></td>
                        <td class="px-3 py-2.5 text-right whitespace-nowrap text-white/50">{{ formatRupiahShort(b.harga_beli) }} → <span class="font-bold text-white">{{ formatRupiahShort(b.harga_jual) }}</span></td>
                        <td class="px-3 py-2.5 text-right font-bold text-emerald-400">{{ margin(b) }}%</td>
                        <td class="px-3 py-2.5 text-right"><span class="rounded-md px-1.5 py-0.5 font-black" :class="b.stok === 0 ? 'bg-rose-500/15 text-rose-300' : b.stok <= 10 ? 'bg-amber-500/15 text-amber-300' : 'bg-emerald-500/15 text-emerald-300'">{{ b.stok }}</span></td>
                        <td class="px-3 py-2.5 text-right whitespace-nowrap">
                            <button @click="edit(b)" class="rounded-md bg-white/5 px-2 py-1 text-[11px] font-bold hover:bg-white/10">Edit</button>
                            <button @click="store.deleteBarang(b.id_barang)" class="ml-1 rounded-md bg-white/5 px-2 py-1 text-[11px] font-bold text-rose-300 hover:bg-rose-500/20">Hapus</button>
                        </td>
                    </tr>
                </tbody>
            </table>
            <p v-if="!filtered.length" class="py-10 text-center text-sm text-white/30">Tidak ada produk.</p>
        </div>
        <p class="text-[11px] text-white/25">tb_barang • soft-delete via is_delete • harga_beli = HPP terakhir dari tb_detail_pembelian</p>
    </div>

    <div v-if="show && auth.can('/produk')" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4 backdrop-blur-sm" @click.self="show = false">
        <div class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-xl border border-white/10 bg-[#111] p-5">
            <h3 class="text-sm font-black">{{ form.id_barang ? 'Edit Produk' : 'Produk Baru' }}</h3>
            <div class="mt-4 grid grid-cols-2 gap-2 text-xs">
                <label class="col-span-2 text-white/50">Nama barang<input v-model="form.nama" class="mt-1 h-10 w-full rounded-lg border border-white/10 bg-black/50 px-3 text-sm text-white outline-none" /></label>
                <label class="text-white/50">Barcode<input v-model="form.barcode" class="mt-1 h-10 w-full rounded-lg border border-white/10 bg-black/50 px-3 font-mono text-sm text-white outline-none" /></label>
                <label class="text-white/50">Satuan
                    <select v-model="form.satuan" class="mt-1 h-10 w-full rounded-lg border border-white/10 bg-black/50 px-2 text-sm text-white outline-none"><option>pcs</option><option>porsi</option><option>botol</option><option>cup</option><option>kotak</option><option>pak</option></select></label>
                <label class="text-white/50">Kategori
                    <select v-model="form.id_kategori" class="mt-1 h-10 w-full rounded-lg border border-white/10 bg-black/50 px-2 text-sm text-white outline-none"><option v-for="k in store.state.kategori" :key="k.id_kategori" :value="k.id_kategori">{{ k.nama }}</option></select></label>
                <label class="text-white/50">Kelompok
                    <select v-model="form.id_kelompok_kategori" class="mt-1 h-10 w-full rounded-lg border border-white/10 bg-black/50 px-2 text-sm text-white outline-none"><option v-for="k in store.state.kelompok" :key="k.id_kelompok" :value="k.id_kelompok">{{ k.nama_kelompok }}</option></select></label>
                <label class="col-span-2 text-white/50">Supplier
                    <select v-model="form.id_supplier" class="mt-1 h-10 w-full rounded-lg border border-white/10 bg-black/50 px-2 text-sm text-white outline-none"><option v-for="s in store.supplierAktif.value" :key="s.id_supplier" :value="s.id_supplier">{{ s.nama }}</option></select></label>
                <label class="text-white/50">Harga beli<input v-model.number="form.harga_beli" type="number" class="mt-1 h-10 w-full rounded-lg border border-white/10 bg-black/50 px-3 text-sm text-white outline-none" /></label>
                <label class="text-white/50">Harga jual<input v-model.number="form.harga_jual" type="number" class="mt-1 h-10 w-full rounded-lg border border-white/10 bg-black/50 px-3 text-sm text-white outline-none" /></label>
                <label class="text-white/50">Stok<input v-model.number="form.stok" type="number" class="mt-1 h-10 w-full rounded-lg border border-white/10 bg-black/50 px-3 text-sm text-white outline-none" /></label>
                <label class="flex items-end gap-2 pb-2 text-white/50"><input v-model="form.is_active" type="checkbox" :true-value="1" :false-value="0" class="h-4 w-4 accent-emerald-500" /> Aktif dijual</label>
            </div>
            <p class="mt-2 text-xs text-white/40">Margin: <span class="font-black text-emerald-400">{{ form.harga_beli ? Math.round(((form.harga_jual - form.harga_beli) / form.harga_beli) * 100) : 0 }}%</span></p>
            <div class="mt-4 flex gap-2"><button @click="show = false" class="h-10 flex-1 rounded-lg bg-white/5 text-sm font-bold">Batal</button>
            <button @click="simpan" class="h-10 flex-1 rounded-lg bg-amber-500 text-sm font-black text-black hover:bg-amber-400">Simpan</button></div>
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
const props = defineProps({ barang: Array, kategori: Array, supplier: Array });
onMounted(() => hydrate(props));
const store = usePosStore();
const auth = useAuthMock();
const q = ref(''), fKat = ref(0), fStok = ref(''), show = ref(false);
const form = ref({});

const filtered = computed(() => store.barangAktif.value.filter((b) => {
    const okQ = !q.value || b.nama.toLowerCase().includes(q.value.toLowerCase()) || b.barcode.includes(q.value);
    const okK = !fKat.value || b.id_kategori === Number(fKat.value);
    const okS = !fStok.value || (fStok.value === 'habis' && b.stok === 0) || (fStok.value === 'tipis' && b.stok <= 10) || (fStok.value === 'aman' && b.stok > 10);
    return okQ && okK && okS;
}).sort((a, b) => a.nama.localeCompare(b.nama)));
const margin = (b) => b.harga_beli ? Math.round(((b.harga_jual - b.harga_beli) / b.harga_beli) * 100) : 0;
const stats = computed(() => [
    { label: 'Total SKU', value: store.barangAktif.value.length },
    { label: 'Nilai Stok (HPP)', value: formatRupiahShort(store.barangAktif.value.reduce((s, b) => s + b.stok * b.harga_beli, 0)) },
    { label: 'Stok Menipis ≤10', value: store.barangAktif.value.filter((b) => b.stok <= 10).length },
    { label: 'Nonaktif', value: store.barangAktif.value.filter((b) => !b.is_active).length },
]);
function baru() { form.value = { nama: '', barcode: '', satuan: 'pcs', id_kategori: 1, id_kelompok_kategori: 1, id_supplier: 1, harga_beli: 0, harga_jual: 0, stok: 0, is_active: 1 }; show.value = true; }
function edit(b) { form.value = { ...b }; show.value = true; }
function simpan() { if (!form.value.nama) return; store.saveBarang(form.value); show.value = false; }
</script>
