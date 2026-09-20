<template>
    <RoleDenied v-if="!auth.can('/pembelian')" page="Pembelian" :needed="['Admin']" />
    <div v-else class="space-y-4">
        <div class="grid grid-cols-2 gap-4 xl:grid-cols-4">
            <div v-for="s in stats" :key="s.label" class="rounded-xl border border-white/[0.06] bg-white/[0.02] p-4">
                <p class="text-[11px] text-white/40">{{ s.label }}</p>
                <p class="mt-1 text-xl font-black">{{ s.value }}</p>
            </div>
        </div>

        <div class="flex flex-col gap-2 sm:flex-row">
            <input v-model="q" placeholder="Cari no. faktur / supplier…" class="h-10 flex-1 rounded-lg border border-white/[0.08] bg-black/40 px-3 text-sm outline-none placeholder:text-white/25" />
            <select v-model="status" class="h-10 rounded-lg border border-white/[0.08] bg-black/40 px-3 text-sm outline-none">
                <option value="">Semua status</option><option value="draft">Draft</option><option value="selesai">Selesai</option>
            </select>
            <button @click="showForm = true" class="h-10 rounded-lg bg-violet-500 px-4 text-sm font-black text-white hover:bg-violet-400">+ Pembelian Baru</button>
        </div>

        <div class="space-y-2">
            <div v-for="p in filtered" :key="p.id_pembelian" class="rounded-xl border border-white/[0.06] bg-white/[0.02]">
                <button @click="openId = openId === p.id_pembelian ? 0 : p.id_pembelian" class="flex w-full flex-wrap items-center gap-2 px-4 py-3 text-left text-xs">
                    <span class="font-mono font-black">{{ p.nomor_faktur }}</span>
                    <span class="text-white/40">{{ store.namaSupplier(p.id_supplier) }} • {{ formatDate(p.tanggal_faktur) }}</span>
                    <span class="rounded px-1.5 py-0.5 text-[10px] font-black" :class="p.status_pembelian === 'selesai' ? 'bg-emerald-500/15 text-emerald-300' : 'bg-amber-500/15 text-amber-300'">{{ p.status_pembelian }}</span>
                    <span class="rounded bg-white/5 px-1.5 py-0.5 text-[10px] font-bold text-white/50">{{ p.jenis_transaksi }} • {{ p.cara_bayar }}</span>
                    <span class="ml-auto font-black">{{ formatRupiah(p.total_bayar) }}</span>
                </button>
                <div v-if="openId === p.id_pembelian" class="border-t border-white/[0.06] px-4 py-3 text-xs">
                    <p v-if="p.note" class="mb-2 text-white/40">📝 {{ p.note }}</p>
                    <table class="w-full text-left">
                        <thead><tr class="text-[10px] tracking-wider text-white/30 uppercase"><th class="py-1">Barang</th><th class="py-1 text-right">Qty</th><th class="py-1 text-right">Harga</th><th class="py-1 text-right">Subtotal</th></tr></thead>
                        <tbody>
                            <tr v-for="d in store.detailBeli(p.id_pembelian)" :key="d.id_detail_pembelian" class="border-t border-white/5">
                                <td class="py-1.5 font-bold">{{ store.namaBarang(d.id_barang) }}</td>
                                <td class="py-1.5 text-right">{{ d.jumlah }}</td>
                                <td class="py-1.5 text-right text-white/50">{{ formatRupiah(d.harga_beli) }}</td>
                                <td class="py-1.5 text-right font-bold">{{ formatRupiah(d.subtotal) }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="mt-3 flex gap-2">
                        <button v-if="p.status_pembelian === 'draft'" @click="store.selesaikanPembelian(p.id_pembelian)" class="h-9 rounded-lg bg-emerald-500 px-4 text-xs font-black text-white hover:bg-emerald-400">✓ Tandai Selesai (stok +)</button>
                        <button v-if="p.status_pembelian === 'draft'" @click="store.deletePembelian(p.id_pembelian)" class="h-9 rounded-lg bg-white/5 px-4 text-xs font-bold text-rose-300 hover:bg-rose-500/20">Hapus draft</button>
                        <span v-else class="text-[11px] text-white/30">Stok sudah masuk saat pembelian diselesaikan.</span>
                    </div>
                </div>
            </div>
            <p v-if="!filtered.length" class="rounded-xl border border-dashed border-white/10 py-10 text-center text-sm text-white/30">Tidak ada pembelian.</p>
        </div>
    </div>

    <!-- FORM -->
    <div v-if="showForm && auth.can('/pembelian')" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4 backdrop-blur-sm" @click.self="showForm = false">
        <div class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-xl border border-white/10 bg-[#111] p-5">
            <h3 class="text-sm font-black">Pembelian Baru (tb_pembelian + tb_detail_pembelian)</h3>
            <div class="mt-4 grid grid-cols-2 gap-2">
                <label class="col-span-2 text-xs text-white/50">Supplier
                    <select v-model="f.supplier" class="mt-1 h-10 w-full rounded-lg border border-white/10 bg-black/50 px-3 text-sm text-white outline-none">
                        <option v-for="s in store.supplierAktif.value" :key="s.id_supplier" :value="s.id_supplier">{{ s.nama }}</option>
                    </select></label>
                <label class="text-xs text-white/50">Jenis
                    <select v-model="f.jenis" class="mt-1 h-10 w-full rounded-lg border border-white/10 bg-black/50 px-2 text-sm text-white outline-none"><option value="tunai">Tunai</option><option value="kredit">Kredit</option></select></label>
                <label class="text-xs text-white/50">Cara bayar
                    <select v-model="f.cara" class="mt-1 h-10 w-full rounded-lg border border-white/10 bg-black/50 px-2 text-sm text-white outline-none"><option>Transfer</option><option>Tunai</option><option>Tempo 14 hari</option></select></label>
                <label class="col-span-2 text-xs text-white/50">Catatan
                    <input v-model="f.note" class="mt-1 h-10 w-full rounded-lg border border-white/10 bg-black/50 px-3 text-sm outline-none" placeholder="cth: Restock awal bulan" /></label>
            </div>
            <div class="mt-3 space-y-2">
                <div v-for="(l, i) in f.lines" :key="i" class="flex gap-2">
                    <select v-model="l.barang" class="h-10 flex-1 rounded-lg border border-white/10 bg-black/50 px-2 text-xs outline-none">
                        <option v-for="b in store.barangAktif.value" :key="b.id_barang" :value="b.id_barang">{{ b.nama }} (Rp{{ b.harga_beli }})</option>
                    </select>
                    <input v-model.number="l.jumlah" type="number" min="1" class="h-10 w-16 rounded-lg border border-white/10 bg-black/50 px-2 text-right text-sm outline-none" />
                    <input v-model.number="l.harga" type="number" min="0" class="h-10 w-28 rounded-lg border border-white/10 bg-black/50 px-2 text-right text-sm outline-none" />
                    <button @click="f.lines.splice(i, 1)" class="text-rose-400">✕</button>
                </div>
                <button @click="tambahBaris" class="h-9 w-full rounded-lg bg-white/5 text-xs font-bold text-white/60 hover:bg-white/10">+ Tambah baris</button>
            </div>
            <p class="mt-3 text-right text-sm font-black">Total: <span class="text-violet-300">{{ formatRupiah(formTotal) }}</span></p>
            <p v-if="ferr" class="mt-2 rounded-lg bg-rose-500/10 px-3 py-2 text-[11px] font-bold text-rose-300">{{ ferr }}</p>
            <div class="mt-4 flex gap-2">
                <button @click="showForm = false" class="h-10 flex-1 rounded-lg bg-white/5 text-sm font-bold">Batal</button>
                <button @click="simpan" class="h-10 flex-1 rounded-lg bg-violet-500 text-sm font-black text-white hover:bg-violet-400">Simpan sebagai Draft</button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import { hydrate, usePosStore } from '@/composables/usePosStore';
import RoleDenied from '@/components/RoleDenied.vue';
import { useAuthMock } from '@/composables/useAuthMock';
import { formatDate, formatRupiah } from '@/lib/format';

defineOptions({ layout: DashboardLayout });
const props = defineProps({ pembelian: Array, supplier: Array, barang: Array });
onMounted(() => hydrate(props));
const store = usePosStore();
const auth = useAuthMock();

const q = ref(''), status = ref(''), openId = ref(0), showForm = ref(false), ferr = ref('');
const f = ref({ supplier: 1, jenis: 'tunai', cara: 'Transfer', note: '', lines: [{ barang: 1, jumlah: 10, harga: 5000 }] });

const filtered = computed(() => store.pembelianAktif.value.filter((p) => {
    const okQ = !q.value || p.nomor_faktur.toLowerCase().includes(q.value.toLowerCase()) || store.namaSupplier(p.id_supplier).toLowerCase().includes(q.value.toLowerCase());
    return okQ && (!status.value || p.status_pembelian === status.value);
}).sort((a, b) => b.id_pembelian - a.id_pembelian));

const stats = computed(() => [
    { label: 'Total Pembelian', value: formatRupiah(store.pembelianAktif.value.reduce((s, p) => s + p.total_bayar, 0)) },
    { label: 'Draft', value: String(store.pembelianAktif.value.filter((p) => p.status_pembelian === 'draft').length) },
    { label: 'Selesai', value: String(store.pembelianAktif.value.filter((p) => p.status_pembelian === 'selesai').length) },
    { label: 'Supplier Aktif', value: String(store.supplierAktif.value.length) },
]);
const formTotal = computed(() => f.value.lines.reduce((s, l) => s + (l.jumlah || 0) * (l.harga || 0), 0));
function tambahBaris() { f.value.lines.push({ barang: store.barangAktif.value[0]?.id_barang ?? 1, jumlah: 10, harga: 5000 }); }
function simpan() {
    ferr.value = '';
    if (!f.value.lines.length) { ferr.value = 'Tambahkan minimal 1 baris barang.'; return; }
    store.createPembelian({ id_supplier: f.value.supplier, jenis_transaksi: f.value.jenis, cara_bayar: f.value.cara, note: f.value.note, lines: f.value.lines.map((l) => ({ id_barang: l.barang, jumlah: l.jumlah, harga_beli: l.harga })) });
    showForm.value = false;
    f.value = { supplier: 1, jenis: 'tunai', cara: 'Transfer', note: '', lines: [{ barang: 1, jumlah: 10, harga: 5000 }] };
}
</script>
