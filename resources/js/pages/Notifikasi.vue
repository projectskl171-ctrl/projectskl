<template>
    <RoleDenied v-if="!auth.can('/notifikasi')" page="Notifikasi" :needed="['Kasir', 'Admin', 'Super Admin']" />
    <div v-else class="space-y-4">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
            <div class="flex flex-wrap gap-1.5">
                <button v-for="f in filters" :key="f.key" @click="fil = f.key"
                    :class="fil === f.key ? 'bg-rose-500 text-white' : 'bg-slate-900/[0.04] dark:bg-white/5 text-slate-500 dark:text-white/50 hover:bg-slate-900/5 dark:hover:bg-white/10'"
                    class="h-9 rounded-lg px-3 text-xs font-black">{{ f.label }}</button>
            </div>
            <p class="text-[11px] text-slate-400 dark:text-white/30 sm:ml-auto">{{ shown }} notifikasi</p>
        </div>

        <!-- ============ KASIR : info pelanggan & shift (tanpa data admin) ============ -->
        <template v-if="auth.role.value === 'kasir'">
            <div v-if="fil === 'all' || fil === 'pelanggan'" class="rounded-xl border border-slate-200 dark:border-white/[0.06] bg-white dark:bg-white/[0.02] p-4 shadow-sm dark:shadow-none">
                <h2 class="text-sm font-bold">👥 Pelanggan baru minggu ini <span class="text-slate-400 dark:text-white/30">({{ pelangganBaru.length }})</span></h2>
                <div class="mt-3 space-y-2">
                    <div v-for="p in pgPelanggan.paged.value" :key="p.id_pelanggan" class="flex items-center gap-3 rounded-lg border border-slate-200 dark:border-white/[0.05] bg-white dark:bg-black/30 px-3 py-2.5 text-xs">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-pink-500/15 text-sm font-black text-pink-700 dark:text-pink-300">{{ p.nama_pelanggan.slice(0, 1).toUpperCase() }}</span>
                        <div class="min-w-0 flex-1"><p class="truncate font-bold">{{ p.nama_pelanggan }}</p><p class="text-[11px] text-slate-500 dark:text-white/40">{{ store.namaKelompokPelanggan(p.id_kelompok_pelanggan) }} • {{ formatDate(p.created_at) }}</p></div>
                        <Link href="/pelanggan" class="shrink-0 rounded-md bg-slate-900/[0.04] dark:bg-white/5 px-2 py-1 text-[11px] font-bold hover:bg-slate-900/5 dark:hover:bg-white/10">Lihat →</Link>
                    </div>
                    <p v-if="!pelangganBaru.length" class="py-4 text-center text-xs text-slate-400 dark:text-white/30">Belum ada pelanggan baru minggu ini.</p>
                </div>
                <Pagination :page="pgPelanggan.page.value" :total-pages="pgPelanggan.totalPages.value" @update:page="pgPelanggan.page.value = $event" />
            </div>

            <div v-if="fil === 'all' || fil === 'pelanggan'" class="rounded-xl border border-slate-200 dark:border-white/[0.06] bg-white dark:bg-white/[0.02] p-4 shadow-sm dark:shadow-none">
                <h2 class="text-sm font-bold">💸 Piutang perlu ditagih <span class="text-slate-400 dark:text-white/30">({{ piutang.length }}) • {{ formatRupiahShort(piutangTotal) }}</span></h2>
                <div class="mt-3 space-y-2">
                    <div v-for="t in pgPiutang.paged.value" :key="t.id_penjualan" class="flex items-center gap-3 rounded-lg border border-slate-200 dark:border-white/[0.05] bg-white dark:bg-black/30 px-3 py-2.5 text-xs">
                        <div class="min-w-0 flex-1"><p class="truncate font-bold">#TRX-{{ String(t.id_penjualan).padStart(4, '0') }} • {{ store.namaPelanggan(t.id_pelanggan) }}</p><p class="text-[11px] text-slate-500 dark:text-white/40">{{ formatDateTime(t.tanggal_penjualan) }} • {{ t.cara_bayar }}</p></div>
                        <p class="shrink-0 font-black text-amber-700 dark:text-amber-400">{{ formatRupiah(t.total_faktur) }}</p>
                    </div>
                    <p v-if="!piutang.length" class="py-4 text-center text-xs text-slate-400 dark:text-white/30">Tidak ada piutang. 💰</p>
                </div>
                <Pagination :page="pgPiutang.page.value" :total-pages="pgPiutang.totalPages.value" @update:page="pgPiutang.page.value = $event" />
                <Link href="/pelanggan" class="mt-3 flex h-9 w-full items-center justify-center rounded-lg bg-slate-900/[0.04] dark:bg-white/5 text-[11px] font-bold text-slate-600 dark:text-white/70 hover:bg-slate-900/5 dark:hover:bg-white/10">Hubungi via Data Pelanggan →</Link>
            </div>

            <div v-if="fil === 'all' || fil === 'shift'" class="rounded-xl border border-emerald-500/15 bg-emerald-500/[0.04] px-4 py-3 text-xs text-slate-600 dark:text-white/60">
                🧾 Shift kamu hari ini: <b class="text-slate-900 dark:text-white">{{ myToday.length }} transaksi</b> senilai <b class="text-emerald-700 dark:text-emerald-400">{{ formatRupiah(myOmzet) }}</b>.
                <Link href="/transaksi" class="font-bold text-emerald-700 dark:text-emerald-400 hover:text-emerald-300">Buka kasir →</Link>
            </div>
        </template>

        <!-- ============ SUPER ADMIN : hanya sekolah & admin baru hari ini ============ -->
        <template v-if="auth.role.value === 'super admin'">
            <div v-if="fil === 'all' || fil === 'sekolah'" class="rounded-xl border border-slate-200 dark:border-white/[0.06] bg-white dark:bg-white/[0.02] p-4 shadow-sm dark:shadow-none">
                <h2 class="text-sm font-bold">🏫 Sekolah baru hari ini <span class="text-slate-400 dark:text-white/30">({{ sekolahBaru.length }})</span></h2>
                <div class="mt-3 space-y-2">
                    <div v-for="s in sekolahBaru" :key="s.id_sekolah" class="flex items-center gap-3 rounded-lg border border-slate-200 dark:border-white/[0.05] bg-white dark:bg-black/30 px-3 py-2.5 text-xs">
                        <div class="min-w-0 flex-1"><p class="truncate font-bold">{{ s.nama_sekolah }}</p><p class="text-[11px] text-slate-500 dark:text-white/40">{{ s.kode_sekolah }} • {{ s.alamat_sekolah || '—' }}</p></div>
                        <Link href="/sekolah" class="shrink-0 rounded-md bg-slate-900/[0.04] dark:bg-white/5 px-2 py-1 text-[11px] font-bold hover:bg-slate-900/5 dark:hover:bg-white/10">Lihat →</Link>
                    </div>
                    <p v-if="!sekolahBaru.length" class="py-4 text-center text-xs text-slate-400 dark:text-white/30">Tidak ada sekolah baru hari ini.</p>
                </div>
            </div>

            <div v-if="fil === 'all' || fil === 'admin'" class="rounded-xl border border-slate-200 dark:border-white/[0.06] bg-white dark:bg-white/[0.02] p-4 shadow-sm dark:shadow-none">
                <h2 class="text-sm font-bold">👤 Admin baru hari ini <span class="text-slate-400 dark:text-white/30">({{ adminBaru.length }})</span></h2>
                <div class="mt-3 space-y-2">
                    <div v-for="u in adminBaru" :key="u.id_user" class="flex items-center gap-3 rounded-lg border border-slate-200 dark:border-white/[0.05] bg-white dark:bg-black/30 px-3 py-2.5 text-xs">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-violet-500/15 text-sm font-black text-violet-700 dark:text-violet-300">{{ u.nama_lengkap.slice(0, 1).toUpperCase() }}</span>
                        <div class="min-w-0 flex-1"><p class="truncate font-bold">{{ u.nama_lengkap }}</p><p class="text-[11px] text-slate-500 dark:text-white/40">@{{ u.username }} • {{ store.namaSekolah(u.id_sekolah) }}</p></div>
                        <Link href="/user" class="shrink-0 rounded-md bg-slate-900/[0.04] dark:bg-white/5 px-2 py-1 text-[11px] font-bold hover:bg-slate-900/5 dark:hover:bg-white/10">Lihat →</Link>
                    </div>
                    <p v-if="!adminBaru.length" class="py-4 text-center text-xs text-slate-400 dark:text-white/30">Tidak ada admin baru hari ini.</p>
                </div>
            </div>
        </template>

        <!-- ============ ADMIN : info barang/stok ============ -->
        <template v-else-if="auth.role.value === 'admin'">
            <template v-if="fil === 'all' || fil === 'stok'">
                <div class="rounded-xl border border-slate-200 dark:border-white/[0.06] bg-white dark:bg-white/[0.02] p-4 shadow-sm dark:shadow-none">
                    <h2 class="text-sm font-bold">📦 Stok perlu perhatian <span class="text-slate-400 dark:text-white/30">({{ stokList.length }})</span></h2>
                    <div class="mt-3 space-y-2">
                        <div v-for="b in pgStok.paged.value" :key="b.id_barang" class="flex items-center gap-3 rounded-lg border border-slate-200 dark:border-white/[0.05] bg-white dark:bg-black/30 px-3 py-2.5 text-xs">
                            <span class="flex h-8 w-8 items-center justify-center rounded-md text-sm" :class="b.stok === 0 ? 'bg-rose-500/15' : 'bg-amber-500/15'">{{ b.stok === 0 ? '⛔' : '⚠️' }}</span>
                            <div class="min-w-0 flex-1"><p class="truncate font-bold">{{ b.nama }}</p><p class="text-[11px] text-slate-500 dark:text-white/40">{{ b.stok === 0 ? 'Habis total — segera buat pembelian' : `Sisa ${b.stok} ${b.satuan}` }}</p></div>
                            <Link v-if="auth.can('/pembelian')" href="/pembelian" class="shrink-0 rounded-md bg-slate-900/[0.04] dark:bg-white/5 px-2 py-1 text-[11px] font-bold hover:bg-slate-900/5 dark:hover:bg-white/10">Restock →</Link>
                        </div>
                        <p v-if="!stokList.length" class="py-4 text-center text-xs text-slate-400 dark:text-white/30">Semua stok aman. 🎉</p>
                    </div>
                    <Pagination :page="pgStok.page.value" :total-pages="pgStok.totalPages.value" @update:page="pgStok.page.value = $event" />
                </div>
            </template>

            <!-- DRAFT PEMBELIAN (admin) -->
            <template v-if="(fil === 'all' || fil === 'beli')">
                <div class="rounded-xl border border-slate-200 dark:border-white/[0.06] bg-white dark:bg-white/[0.02] p-4 shadow-sm dark:shadow-none">
                    <h2 class="text-sm font-bold">📥 Draft pembelian <span class="text-slate-400 dark:text-white/30">({{ draft.length }})</span></h2>
                    <div class="mt-3 space-y-2">
                        <div v-for="p in pgDraft.paged.value" :key="p.id_pembelian" class="flex items-center gap-3 rounded-lg border border-slate-200 dark:border-white/[0.05] bg-white dark:bg-black/30 px-3 py-2.5 text-xs">
                            <div class="min-w-0 flex-1"><p class="truncate font-mono font-bold">{{ p.nomor_faktur }} • {{ store.namaSupplier(p.id_supplier) }}</p><p class="text-[11px] text-slate-500 dark:text-white/40">{{ formatDate(p.tanggal_faktur) }}</p></div>
                            <p class="shrink-0 font-black">{{ formatRupiah(p.total_bayar) }}</p>
                            <Link v-if="auth.can('/pembelian')" href="/pembelian" class="shrink-0 rounded-md bg-slate-900/[0.04] dark:bg-white/5 px-2 py-1 text-[11px] font-bold hover:bg-slate-900/5 dark:hover:bg-white/10">Proses →</Link>
                        </div>
                        <p v-if="!draft.length" class="py-4 text-center text-xs text-slate-400 dark:text-white/30">Tidak ada draft. ✅</p>
                    </div>
                    <Pagination :page="pgDraft.page.value" :total-pages="pgDraft.totalPages.value" @update:page="pgDraft.page.value = $event" />
                </div>
            </template>

            <!-- INFO HARI INI (admin) -->
            <div v-if="fil === 'all'" class="rounded-xl border border-emerald-500/15 bg-emerald-500/[0.04] px-4 py-3 text-xs text-slate-600 dark:text-white/60">
                🧾 Hari ini ada <b class="text-slate-900 dark:text-white">{{ todayCount }} transaksi</b> senilai <b class="text-emerald-700 dark:text-emerald-400">{{ formatRupiah(todayOmzet) }}</b>. Semangat! 🚀
            </div>
        </template>
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import RoleDenied from '@/components/RoleDenied.vue';
import Pagination from '@/components/Pagination.vue';
import { usePagination } from '@/composables/usePagination';
import { hydrate, usePosStore } from '@/composables/usePosStore';
import { useAuthMock } from '@/composables/useAuthMock';
import { dayKey, formatDate, formatDateTime, formatRupiah, formatRupiahShort, todayKey } from '@/lib/format';

defineOptions({ layout: DashboardLayout });
const props = defineProps({ penjualan: Array, barang: Array, pembelian: Array });
onMounted(() => hydrate(props));
const store = usePosStore();
const auth = useAuthMock();

const filters = computed(() => {
    if (auth.role.value === 'kasir') {
        return [
            { key: 'all', label: 'Semua' },
            { key: 'pelanggan', label: 'Pelanggan' },
            { key: 'shift', label: 'Shift Saya' },
        ];
    }
    if (auth.role.value === 'super admin') {
        return [
            { key: 'all', label: 'Semua' },
            { key: 'sekolah', label: 'Sekolah Baru' },
            { key: 'admin', label: 'Admin Baru' },
        ];
    }
    return [
        { key: 'all', label: 'Semua' },
        { key: 'stok', label: 'Stok' },
        { key: 'beli', label: 'Pembelian' },
    ];
});
const fil = ref('all');

const stokList = computed(() => store.barangAktif.value.filter((b) => b.stok <= 10).sort((a, b) => a.stok - b.stok));
const piutang = computed(() => store.piutang.value.sort((a, b) => +new Date(b.tanggal_penjualan) - +new Date(a.tanggal_penjualan)));
const piutangTotal = computed(() => piutang.value.reduce((s, t) => s + t.total_faktur, 0));
const draft = computed(() => store.pembelianAktif.value.filter((p) => p.status_pembelian === 'draft'));
const pelangganBaru = computed(() => {
    const semingguLalu = Date.now() - 7 * 86400000;
    return store.pelangganAktif.value.filter((p) => +new Date(p.created_at) >= semingguLalu);
});
const myToday = computed(() =>
    store.penjualanAktif.value.filter((p) => dayKey(p.tanggal_penjualan) === todayKey() && p.id_user === (auth.user.value?.id_user ?? -1)),
);
const myOmzet = computed(() => myToday.value.reduce((s, t) => s + t.total_faktur, 0));
const shown = computed(() => {
    if (auth.role.value === 'kasir') return pelangganBaru.value.length + piutang.value.length + 1;
    if (auth.role.value === 'super admin') return sekolahBaru.value.length + adminBaru.value.length;
    return stokList.value.length + draft.value.length + 1;
});
const sekolahBaru = computed(() => {
    const today = todayKey();
    return store.state.sekolah.filter((s) => s.created_at && dayKey(s.created_at) === today);
});
const adminBaru = computed(() => {
    const today = todayKey();
    return store.usersAktif.value.filter((u) => u.id_role === 2 && u.created_at && dayKey(u.created_at) === today);
});
const todayRows = computed(() => store.penjualanAktif.value.filter((p) => dayKey(p.tanggal_penjualan) === todayKey()));
const todayCount = computed(() => todayRows.value.length);
const todayOmzet = computed(() => todayRows.value.reduce((s, t) => s + t.total_faktur, 0));

/* Paginasi tiap daftar notifikasi. */
const pgPelanggan = usePagination(() => pelangganBaru.value, 8);
const pgPiutang = usePagination(() => piutang.value, 8);
const pgStok = usePagination(() => stokList.value, 8);
const pgDraft = usePagination(() => draft.value, 8);
</script>
