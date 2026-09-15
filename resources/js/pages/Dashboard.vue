<template>
    <div class="space-y-5">
        <!-- Banner peran -->
        <div class="flex flex-wrap items-center gap-3 rounded-xl border border-white/[0.06] bg-white/[0.02] px-4 py-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg text-sm font-black text-white"
                :style="{ background: ROLE_COLOR[auth.role.value] }">{{ auth.user.value?.inisial }}</div>
            <div class="min-w-0 flex-1">
                <p class="text-sm font-black">Halo, {{ auth.user.value?.nama_lengkap }}! 👋</p>
                <p class="text-[11px] text-white/40">{{ roleDesc }}</p>
            </div>
            <span class="rounded-md px-2 py-1 text-[10px] font-black tracking-widest uppercase"
                :style="{ background: hexA(ROLE_COLOR[auth.role.value], 0.15), color: ROLE_COLOR[auth.role.value] }">
                Mode {{ auth.roleLabel.value }}
            </span>
        </div>

        <!-- ============ KASIR : 1 dagang di 1 sekolah, fokus jualan ============ -->
        <template v-if="auth.role.value === 'kasir'">
            <div class="rounded-xl border border-blue-500/20 bg-blue-500/[0.05] px-4 py-2.5 text-[11px] text-white/60">
                🏪 <b>Dagang:</b> Kasir 01 • <b>{{ store.namaSekolah(1) }}</b> — datamu hanya mencakup transaksi & pelanggan dagang ini.
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div v-for="s in kasirStats" :key="s.label" class="rounded-xl border border-white/[0.06] bg-white/[0.02] p-5">
                    <p class="text-[11px] text-white/40">{{ s.label }}</p>
                    <p class="mt-1 text-2xl font-black">{{ s.value }}</p>
                    <p class="mt-0.5 text-[11px]" :style="{ color: s.color }">{{ s.sub }}</p>
                </div>
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <Link href="/transaksi" class="group flex items-center gap-4 rounded-xl border border-emerald-500/20 bg-emerald-500/[0.07] p-5 transition-all hover:bg-emerald-500/[0.12]">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-500 text-xl">🧾</div>
                    <div><p class="font-black">Buka Kasir</p><p class="text-xs text-white/40">Mulai transaksi penjualan baru</p></div>
                </Link>
                <Link href="/pelanggan" class="group flex items-center gap-4 rounded-xl border border-white/[0.06] bg-white/[0.02] p-5 transition-all hover:bg-white/[0.04]">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-pink-500/15 text-xl">👥</div>
                    <div><p class="font-black">Data Pelanggan</p><p class="text-xs text-white/40">Kelola pelanggan daganganmu (tambah / edit / hapus)</p></div>
                </Link>
            </div>
            <div class="rounded-xl border border-white/[0.06] bg-white/[0.02] p-5">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-bold">Transaksi Shift Saya ({{ myToday.length }})</h2>
                    <Link href="/transaksi" class="text-[11px] font-bold text-emerald-400 hover:text-emerald-300">Riwayat lengkap →</Link>
                </div>
                <div class="mt-3 space-y-2">
                    <div v-for="t in myToday.slice(0, 6)" :key="t.id_penjualan" class="flex items-center justify-between rounded-lg border border-white/[0.05] bg-black/30 px-3 py-2.5 text-xs">
                        <span class="font-bold">#TRX-{{ String(t.id_penjualan).padStart(4, '0') }} • {{ store.namaPelanggan(t.id_pelanggan) }}</span>
                        <span class="font-black text-emerald-400">{{ formatRupiah(t.total_faktur) }}</span>
                    </div>
                    <p v-if="!myToday.length" class="py-6 text-center text-xs text-white/30">Belum ada transaksi. Gas ke kasir! 🚀</p>
                </div>
            </div>
        </template>

        <!-- ============ ADMIN (1 sekolah) & SUPER ADMIN (semua sekolah) ============ -->
        <template v-else>
            <!-- Konteks scope -->
            <div v-if="auth.role.value === 'admin'" class="rounded-xl border border-violet-500/20 bg-violet-500/[0.05] px-4 py-2.5 text-[11px] text-white/60">
                🏫 <b>Scope:</b> 1 sekolah — <b>{{ store.namaSekolah(1) }}</b>. Semua angka di bawah hanya mencakup sekolah ini.
            </div>
            <div v-else class="flex flex-col gap-2 rounded-xl border border-emerald-500/20 bg-emerald-500/[0.05] px-4 py-3 sm:flex-row sm:items-center">
                <p class="flex-1 text-[11px] text-white/60">🌐 <b>Scope:</b> semua sekolah aktif ({{ store.sekolahAktif.value.length }}). Pilih scope untuk memfilter angka operasional:</p>
                <select v-model.number="scopeSekolah" class="h-9 rounded-lg border border-white/[0.08] bg-black/40 px-3 text-xs font-bold outline-none">
                    <option :value="0">Semua sekolah</option>
                    <option v-for="s in store.sekolahAktif.value" :key="s.id_sekolah" :value="s.id_sekolah">{{ s.nama_sekolah }}</option>
                </select>
            </div>

            <div v-if="auth.role.value === 'super admin'" class="rounded-xl border border-amber-500/20 bg-amber-500/[0.06] p-4">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-[11px] font-black uppercase tracking-[0.18em] text-amber-300">Status sekolah</p>
                    <span class="rounded-md bg-amber-500/15 px-2 py-1 text-[10px] font-black text-amber-200">{{ store.sekolahBelumBayar.value.length }} menunggak</span>
                </div>
                <div class="mt-3 grid grid-cols-1 gap-2 sm:grid-cols-2">
                    <div v-for="s in store.sekolahBelumBayar.value" :key="s.id_sekolah" class="rounded-lg border border-amber-500/20 bg-black/20 px-3 py-2 text-xs text-white/70">
                        <p class="font-black text-white">{{ s.nama_sekolah }}</p>
                        <p class="mt-1 text-[10px] text-white/45">{{ s.kode_sekolah }} • terakhir bayar: {{ s.bulan_terakhir_bayar || '—' }}</p>
                    </div>
                    <div v-if="!store.sekolahBelumBayar.value.length" class="rounded-lg border border-emerald-500/15 bg-emerald-500/[0.06] px-3 py-2 text-xs text-emerald-200">
                        Semua sekolah sudah bayar bulan ini. Status aman.
                    </div>
                </div>
            </div>

            <!-- Khusus super admin: ringkasan jaringan -->
            <template v-if="auth.role.value === 'super admin'">
                <div class="grid grid-cols-2 gap-4 xl:grid-cols-4">
                    <div v-for="s in netStats" :key="s.label" class="relative overflow-hidden rounded-xl border border-white/[0.06] bg-white/[0.02] p-5">
                        <div class="pointer-events-none absolute -top-10 -right-10 h-32 w-32 rounded-full blur-3xl" :style="{ background: hexA(s.color, 0.18) }" />
                        <div class="relative">
                            <p class="text-2xl font-bold tracking-tight">{{ s.value }}</p>
                            <p class="mt-1 text-xs text-white/40">{{ s.label }}</p>
                            <p class="mt-0.5 text-[10px] font-bold" :style="{ color: s.color }">{{ s.sub }}</p>
                        </div>
                    </div>
                </div>
                <div class="overflow-x-auto rounded-xl border border-white/[0.06]">
                    <table class="w-full min-w-[720px] text-left text-xs">
                        <thead><tr class="bg-white/[0.03] text-[10px] tracking-wider text-white/40 uppercase">
                            <th class="px-4 py-2.5">Sekolah</th><th class="px-3 py-2.5 text-right">Admin</th><th class="px-3 py-2.5 text-right">Kasir</th><th class="px-3 py-2.5 text-right">SKU</th><th class="px-3 py-2.5 text-right">Trx Hari Ini</th><th class="px-3 py-2.5 text-right">Omzet Hari Ini</th>
                        </tr></thead>
                        <tbody>
                            <tr v-for="r in perSekolah" :key="r.id" class="border-t border-white/[0.05] hover:bg-white/[0.02]">
                                <td class="px-4 py-2.5"><p class="font-bold">{{ r.nama }}</p><p class="font-mono text-[10px] text-white/30">{{ r.kode }}</p></td>
                                <td class="px-3 py-2.5 text-right font-bold">{{ r.admin }}</td>
                                <td class="px-3 py-2.5 text-right font-bold">{{ r.kasir }}</td>
                                <td class="px-3 py-2.5 text-right font-bold">{{ r.sku }}</td>
                                <td class="px-3 py-2.5 text-right font-bold">{{ r.trx }}</td>
                                <td class="px-3 py-2.5 text-right font-black text-emerald-400">{{ formatRupiahShort(r.omzet) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </template>

            <!-- Operasional (scope-aware) -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div v-for="s in stats" :key="s.label" class="relative overflow-hidden rounded-xl border border-white/[0.06] bg-white/[0.02] p-5">
                    <div class="pointer-events-none absolute -top-10 -right-10 h-32 w-32 rounded-full blur-3xl" :style="{ background: hexA(s.color, 0.18) }" />
                    <div class="relative">
                        <div class="flex items-center justify-between">
                            <div class="flex h-9 w-9 items-center justify-center rounded-lg" :style="{ background: hexA(s.color, 0.12), color: s.color }">
                                <component :is="s.icon" class="h-[18px] w-[18px]" />
                            </div>
                            <span class="rounded-md px-1.5 py-0.5 text-[10px] font-bold" :style="{ background: hexA(s.color, 0.12), color: s.color }">{{ s.sub }}</span>
                        </div>
                        <p class="mt-4 text-2xl font-bold tracking-tight">{{ s.value }}</p>
                        <p class="mt-1 text-xs text-white/40">{{ s.label }}</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 xl:grid-cols-3">
                <div class="xl:col-span-2">
                    <OmzetChart :data="omzetScope7" :subtitle="auth.role.value === 'super admin' && scopeSekolah ? `tb_penjualan • ${store.namaSekolah(scopeSekolah)}` : 'tb_penjualan • total_faktur per hari'" />
                </div>
                <div class="rounded-xl border border-white/[0.06] bg-white/[0.02] p-5">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-bold tracking-tight">Penjualan Terbaru</h2>
                        <Link href="/transaksi" class="text-[11px] font-bold text-emerald-400 hover:text-emerald-300">Kasir →</Link>
                    </div>
                    <div class="mt-4 space-y-2.5">
                        <div v-for="t in recent" :key="t.id_penjualan" class="flex items-center gap-3 rounded-lg border border-white/[0.05] bg-white/[0.02] px-3 py-2.5">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-emerald-500/10 text-[11px] font-black text-emerald-400">{{ (namaPelanggan(t.id_pelanggan) || '?').slice(0, 1).toUpperCase() }}</div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-xs font-bold">#TRX-{{ String(t.id_penjualan).padStart(4, '0') }} • {{ namaPelanggan(t.id_pelanggan) }}</p>
                                <p class="truncate text-[11px] text-white/40">{{ formatDateTime(t.tanggal_penjualan) }} • {{ store.namaUser(t.id_user) }}</p>
                            </div>
                            <p class="shrink-0 text-xs font-bold">{{ formatRupiahShort(t.total_faktur) }}</p>
                        </div>
                        <p v-if="!recent.length" class="py-6 text-center text-xs text-white/30">Belum ada transaksi pada scope ini.</p>
                    </div>
                    <Link href="/transaksi" class="mt-4 flex h-9 w-full items-center justify-center rounded-lg bg-emerald-500 text-xs font-bold text-white transition-colors hover:bg-emerald-400">+ Transaksi Baru</Link>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
                <div class="rounded-xl border border-white/[0.06] bg-white/[0.02] p-5">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-bold tracking-tight">Stok Menipis <span class="text-white/30">({{ stokScope.length }})</span></h2>
                        <Link href="/produk" class="text-[11px] font-bold text-amber-400 hover:text-amber-300">Kelola →</Link>
                    </div>
                    <div class="mt-3 overflow-hidden rounded-lg border border-white/[0.05]">
                        <table class="w-full text-left text-xs">
                            <thead><tr class="bg-white/[0.03] text-[10px] tracking-wider text-white/40 uppercase"><th class="px-3 py-2">Barang</th><th class="px-3 py-2 text-right">Stok</th></tr></thead>
                            <tbody>
                                <tr v-for="b in stokScope.slice(0, 6)" :key="b.id_barang" class="border-t border-white/[0.05]">
                                    <td class="px-3 py-2 font-bold">{{ b.nama }}</td>
                                    <td class="px-3 py-2 text-right"><span class="rounded-md px-1.5 py-0.5 font-bold" :class="b.stok === 0 ? 'bg-rose-500/15 text-rose-400' : 'bg-amber-500/15 text-amber-400'">{{ b.stok }} {{ b.satuan }}</span></td>
                                </tr>
                            </tbody>
                        </table>
                        <p v-if="!stokScope.length" class="px-3 py-6 text-center text-xs text-white/30">Semua stok aman.</p>
                    </div>
                </div>
                <!-- Kinerja kasir (semua peran operasional bisa lihat) -->
                <div class="rounded-xl border border-white/[0.06] bg-white/[0.02] p-5">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-bold tracking-tight">Kinerja Kasir Hari Ini</h2>
                        <Link v-if="auth.can('/user')" href="/user" class="text-[11px] font-bold text-indigo-400 hover:text-indigo-300">Kelola user →</Link>
                    </div>
                    <div class="mt-3 space-y-2.5">
                        <div v-for="k in kinerjaKasir" :key="k.id" class="flex items-center gap-3 rounded-lg border border-white/[0.05] bg-white/[0.02] px-3 py-2.5 text-xs">
                            <div class="flex h-8 w-8 items-center justify-center rounded-md bg-indigo-500/15 text-[11px] font-black text-indigo-300">{{ k.inisial }}</div>
                            <div class="flex-1"><p class="font-bold">{{ k.nama }}</p><p class="text-[11px] text-white/40">{{ k.trx }} transaksi</p></div>
                            <p class="font-black text-emerald-400">{{ formatRupiahShort(k.omzet) }}</p>
                        </div>
                        <p v-if="!kinerjaKasir.length" class="py-6 text-center text-xs text-white/30">Belum ada transaksi hari ini.</p>
                    </div>
                </div>
            </div>

            <!-- Khusus super admin -->
            <div v-if="auth.role.value === 'super admin'" class="rounded-xl border border-emerald-500/15 bg-emerald-500/[0.04] p-5">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-bold tracking-tight">🛡️ Panel Super Admin — Kelola Akses</h2>
                    <Link href="/user" class="text-[11px] font-bold text-emerald-400 hover:text-emerald-300">Buka manajemen user →</Link>
                </div>
                <div class="mt-3 grid grid-cols-1 gap-2 sm:grid-cols-3">
                    <div v-for="u in store.usersAktif.value" :key="u.id_user" class="flex items-center gap-2.5 rounded-lg border border-white/[0.06] bg-black/30 px-3 py-2.5 text-xs">
                        <div class="flex h-8 w-8 items-center justify-center rounded-md bg-white/10 text-[11px] font-black">{{ u.nama_lengkap.slice(0, 1) }}</div>
                        <div class="min-w-0 flex-1"><p class="truncate font-bold">{{ u.nama_lengkap }}</p><p class="text-[10px] text-white/40">@{{ u.username }} • {{ store.namaSekolah(u.id_sekolah) }}</p></div>
                        <span class="rounded px-1.5 py-0.5 text-[10px] font-black" :class="u.id_role === 1 ? 'bg-rose-500/15 text-rose-300' : u.id_role === 2 ? 'bg-violet-500/15 text-violet-300' : 'bg-blue-500/15 text-blue-300'">{{ store.namaRole(u.id_role) }}</span>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>

<script setup>
import { computed, defineComponent, h, onMounted, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import OmzetChart from '@/components/OmzetChart.vue';
import { hydrate, usePosStore } from '@/composables/usePosStore';
import { ROLE_COLOR, ROLE_SCOPE, useAuthMock } from '@/composables/useAuthMock';
import { dayKey, formatDateTime, formatRupiah, formatRupiahShort, todayKey } from '@/lib/format';

defineOptions({ layout: DashboardLayout });
const props = defineProps({ penjualan: Array, barang: Array, pelanggan: Array });
onMounted(() => hydrate(props));

const store = usePosStore();
const auth = useAuthMock();
const { namaPelanggan } = store;

/* Scope sekolah: kasir & admin terkunci di sekolah 1 (satu-satunya sekolah mereka),
   super admin bisa pilih 0 = semua sekolah. */
const scopeSekolah = ref(0);
const scopeId = computed(() => (auth.role.value === 'super admin' ? scopeSekolah.value : 1));
const inScope = (row) => !scopeId.value || (row.id_sekolah ?? 1) === scopeId.value;

const jualScope = computed(() => store.penjualanAktif.value.filter(inScope));
const jualHariIniScope = computed(() => jualScope.value.filter((p) => dayKey(p.tanggal_penjualan) === todayKey()));
const omzetHariIniScope = computed(() => jualHariIniScope.value.reduce((s, p) => s + p.total_faktur, 0));
const barangScope = computed(() => store.barangAktif.value.filter(inScope));
const stokScope = computed(() => barangScope.value.filter((b) => b.stok <= 10));
const pelangganScope = computed(() => store.pelangganAktif.value.filter((p) => {
    if (!scopeId.value) return true;
    const kel = store.state.kelompokPelanggan.find((k) => k.id_kelompok_pelanggan === p.id_kelompok_pelanggan);
    return (kel?.id_sekolah ?? 1) === scopeId.value;
}));

const makeIcon = (paths) => defineComponent({
    setup(_, { attrs }) {
        return () => h('svg', { viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', 'stroke-width': 1.8, 'stroke-linecap': 'round', 'stroke-linejoin': 'round', ...attrs },
            paths.map((d, i) => typeof d === 'string' ? h('path', { d, key: i }) : h(d.tag, { ...d, key: i })));
    },
});
const IconMoney = makeIcon(['M3 3v18h18', 'M7 14l4-4 4 4 6-6']);
const IconCart = makeIcon(['M3 3h2l.4 2M7 13h10l4-8H5.4', { tag: 'circle', cx: 9, cy: 20, r: 1.5 }, { tag: 'circle', cx: 17, cy: 20, r: 1.5 }]);
const IconBox = makeIcon(['M21 8l-9-5-9 5 9 5 9-5z', 'M3 8v8l9 5 9-5V8', 'M12 13v8']);
const IconUsers = makeIcon(['M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2', { tag: 'circle', cx: 9, cy: 7, r: 4 }]);
const hexA = (hex, a) => { const v = hex.replace('#', ''); return `rgba(${parseInt(v.slice(0,2),16)}, ${parseInt(v.slice(2,4),16)}, ${parseInt(v.slice(4,6),16)}, ${a})`; };

const roleDesc = computed(() => ROLE_SCOPE[auth.role.value]);

const myToday = computed(() => store.penjualanAktif.value.filter((p) => dayKey(p.tanggal_penjualan) === todayKey() && p.id_user === (auth.user.value?.id_user ?? -1)));
const kasirStats = computed(() => [
    { label: 'Transaksi Shift Saya', value: String(myToday.value.length), sub: 'hari ini', color: '#3b82f6' },
    { label: 'Omzet Saya', value: formatRupiah(myToday.value.reduce((s, t) => s + t.total_faktur, 0)), sub: 'hari ini', color: '#10b981' },
    { label: 'Rata-rata / Trx', value: formatRupiah(myToday.value.length ? Math.round(myToday.value.reduce((s, t) => s + t.total_faktur, 0) / myToday.value.length) : 0), sub: 'average order', color: '#f59e0b' },
]);

const stats = computed(() => [
    { label: 'Omzet Hari Ini', value: formatRupiah(omzetHariIniScope.value), sub: `${jualHariIniScope.value.length} trx`, color: '#10b981', icon: IconMoney },
    { label: 'Transaksi Hari Ini', value: String(jualHariIniScope.value.length), sub: scopeId.value ? store.namaSekolah(scopeId.value) : 'semua sekolah', color: '#3b82f6', icon: IconCart },
    { label: 'Stok Menipis (≤10)', value: String(stokScope.value.length), sub: 'restock!', color: '#f59e0b', icon: IconBox },
    { label: 'Pelanggan Terdaftar', value: String(pelangganScope.value.length), sub: 'siswa/guru/umum', color: '#ec4899', icon: IconUsers },
]);

/* Ringkasan jaringan (super admin, selalu gabungan semua sekolah) */
const netStats = computed(() => {
    const today = todayKey();
    const jualToday = store.penjualanAktif.value.filter((p) => dayKey(p.tanggal_penjualan) === today);
    return [
        { label: 'Total Sekolah', value: String(store.sekolahAktif.value.length), sub: 'aktif', color: '#10b981' },
        { label: 'Total Pengguna', value: String(store.usersAktif.value.length), sub: `${store.usersAktif.value.filter((u) => u.id_role === 2).length} admin • ${store.usersAktif.value.filter((u) => u.id_role === 3).length} kasir`, color: '#6366f1' },
        { label: 'Omzet Jaringan Hari Ini', value: formatRupiah(jualToday.reduce((s, t) => s + t.total_faktur, 0)), sub: `${jualToday.length} trx gabungan`, color: '#f59e0b' },
        { label: 'Total SKU Aktif', value: String(store.barangAktif.value.length), sub: 'semua sekolah', color: '#ec4899' },
    ];
});
const perSekolah = computed(() => {
    const today = todayKey();
    return store.sekolahAktif.value.map((s) => {
        const jual = store.penjualanAktif.value.filter((p) => (p.id_sekolah ?? 1) === s.id_sekolah && dayKey(p.tanggal_penjualan) === today);
        return {
            id: s.id_sekolah, nama: s.nama_sekolah, kode: s.kode_sekolah,
            admin: store.usersAktif.value.filter((u) => u.id_sekolah === s.id_sekolah && u.id_role === 2).length,
            kasir: store.usersAktif.value.filter((u) => u.id_sekolah === s.id_sekolah && u.id_role === 3).length,
            sku: store.barangAktif.value.filter((b) => (b.id_sekolah ?? 1) === s.id_sekolah).length,
            trx: jual.length, omzet: jual.reduce((sum, t) => sum + t.total_faktur, 0),
        };
    });
});

/* Data grafik 7 hari mengikuti scope */
const omzetScope7 = computed(() => {
    const out = [];
    for (let i = 6; i >= 0; i--) {
        const d = new Date();
        d.setDate(d.getDate() - i);
        const key = dayKey(d);
        const rows = jualScope.value.filter((p) => dayKey(p.tanggal_penjualan) === key);
        out.push({
            label: d.toLocaleDateString('id-ID', { weekday: 'short' }),
            key,
            total: rows.reduce((s, p) => s + p.total_faktur, 0),
            trx: rows.length,
        });
    }
    return out;
});
const recent = computed(() => [...jualScope.value].sort((a, b) => +new Date(b.tanggal_penjualan) - +new Date(a.tanggal_penjualan)).slice(0, 5));
const kinerjaKasir = computed(() => {
    const map = {};
    for (const t of jualHariIniScope.value) {
        map[t.id_user] = map[t.id_user] || { id: t.id_user, nama: store.namaUser(t.id_user), inisial: store.namaUser(t.id_user).split(' ').map((w) => w[0]).slice(0, 2).join(''), trx: 0, omzet: 0 };
        map[t.id_user].trx++; map[t.id_user].omzet += t.total_faktur;
    }
    return Object.values(map).sort((a, b) => b.omzet - a.omzet);
});
</script>
