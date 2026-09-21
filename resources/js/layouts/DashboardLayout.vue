<template>
    <div class="grid min-h-dvh grid-cols-[248px_1fr] bg-[#eef3f0] dark:bg-[#0a0a0a] font-sans text-slate-900 dark:text-white">
        <!-- ============ SIDEBAR TUNGGAL ============ -->
        <DashboardSidebar />

        <!-- ============ MAIN ============ -->
        <div class="flex min-w-0 flex-col">
            <!-- TOPBAR -->
            <header
                class="sticky top-0 z-20 flex h-16 items-center justify-between gap-4 border-b border-slate-200 dark:border-white/[0.06] bg-white/85 dark:bg-[#0a0a0a]/85 px-6 backdrop-blur-xl"
            >
                <div class="min-w-0">
                    <h1 class="truncate text-[15px] font-bold tracking-tight">
                        {{ pageTitle }}
                    </h1>
                    <p class="truncate text-[11px] text-slate-500 dark:text-white/40">
                        {{ pageSubtitle }}
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <!-- SEARCH GLOBAL : cari barang / pelanggan / supplier, ⌘K -->
                    <div class="relative hidden md:block">
                        <label
                            class="flex h-9 items-center gap-2 rounded-lg border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-white/[0.02] px-3 transition-colors focus-within:border-emerald-500/40 focus-within:bg-emerald-600/5 dark:focus-within:bg-white/[0.04]"
                        >
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-3.5 w-3.5 shrink-0 text-slate-500 dark:text-white/40">
                                <circle cx="11" cy="11" r="7" />
                                <path d="M21 21l-4.3-4.3" />
                            </svg>
                            <input
                                ref="searchRef"
                                v-model="q"
                                @focus="searchOpen = true"
                                @keydown.enter="goFirst"
                                @keydown.escape="closeAll"
                                placeholder="Cari barang, pelanggan…"
                                class="w-44 bg-transparent text-xs text-slate-900 dark:text-white outline-none placeholder:text-slate-400 dark:placeholder:text-white/30"
                            />
                            <kbd class="rounded border border-slate-200 dark:border-white/10 bg-slate-900/[0.04] dark:bg-white/5 px-1 py-px font-mono text-[9px] font-bold text-slate-500 dark:text-white/40">
                                ⌘K
                            </kbd>
                        </label>
                        <!-- hasil -->
                        <div v-if="searchOpen && q.trim().length >= 2"
                            class="absolute top-11 right-0 w-80 overflow-hidden rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-[#111] shadow-2xl shadow-slate-900/10 dark:shadow-black/60">
                            <div v-if="!results.length" class="px-4 py-6 text-center text-xs text-slate-400 dark:text-white/30">Tidak ketemu "{{ q }}" di barang / pelanggan / supplier.</div>
                            <div v-else class="max-h-80 overflow-y-auto py-1.5">
                                <button v-for="(r, i) in results" :key="i" @click="go(r)"
                                    class="flex w-full items-center gap-2.5 px-3 py-2 text-left hover:bg-emerald-600/5 dark:hover:bg-white/5">
                                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md text-xs" :class="r.cls">{{ r.icon }}</span>
                                    <span class="min-w-0 flex-1">
                                        <span class="block truncate text-xs font-bold">{{ r.label }}</span>
                                        <span class="block truncate text-[10px] text-slate-500 dark:text-white/40">{{ r.sub }}</span>
                                    </span>
                                    <span class="shrink-0 text-[9px] font-bold tracking-wider text-slate-400 dark:text-white/25 uppercase">{{ r.type }}</span>
                                </button>
                            </div>
                            <p class="border-t border-slate-200 dark:border-white/[0.06] px-3 py-1.5 text-[10px] text-slate-400 dark:text-white/25">Enter = buka hasil pertama • Esc = tutup</p>
                        </div>
                    </div>

                    <!-- NOTIF -->
                    <div class="relative">
                        <button
                            type="button"
                            @click="notifOpen = !notifOpen; searchOpen = false"
                            title="Notifikasi"
                            class="relative flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-white/[0.02] text-slate-600 dark:text-white/60 transition-all hover:bg-emerald-600/5 dark:hover:bg-white/[0.06] hover:text-slate-900 dark:hover:text-white"
                        >
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="h-[17px] w-[17px]">
                                <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9" />
                                <path d="M13.73 21a2 2 0 01-3.46 0" />
                            </svg>
                            <span v-if="alerts.length" class="absolute top-2 right-2.5 h-1.5 w-1.5 rounded-full bg-rose-500 ring-2 ring-white dark:ring-[#0a0a0a]" />
                        </button>
                        <!-- dropdown ringkas -->
                        <div v-if="notifOpen"
                            class="absolute top-11 right-0 w-80 overflow-hidden rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-[#111] shadow-2xl shadow-slate-900/10 dark:shadow-black/60">
                            <div class="flex items-center justify-between border-b border-slate-200 dark:border-white/[0.06] px-4 py-2.5">
                                <p class="text-xs font-black">Notifikasi <span class="text-slate-400 dark:text-white/30">({{ alerts.length }})</span></p>
                                <button @click="goNotif" class="text-[11px] font-bold text-emerald-700 dark:text-emerald-400 hover:text-emerald-300">Lihat semua →</button>
                            </div>
                            <div class="max-h-80 overflow-y-auto py-1.5">
                                <button v-for="(a, i) in alerts.slice(0, 6)" :key="i" @click="go(a)"
                                    class="flex w-full items-start gap-2.5 px-4 py-2.5 text-left hover:bg-emerald-600/5 dark:hover:bg-white/5">
                                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md text-xs" :class="a.cls">{{ a.icon }}</span>
                                    <span class="min-w-0 flex-1">
                                        <span class="block text-xs font-bold">{{ a.title }}</span>
                                        <span class="block truncate text-[11px] text-slate-500 dark:text-white/40">{{ a.sub }}</span>
                                    </span>
                                </button>
                                <p v-if="!alerts.length" class="px-4 py-6 text-center text-xs text-slate-400 dark:text-white/30">Aman! Tidak ada peringatan. 🎉</p>
                            </div>
                        </div>
                    </div>

                    <!-- PROFILE -->
                    <button
                        type="button"
                        @click="keluar"
                        title="Klik untuk logout"
                        class="group flex h-9 items-center gap-2.5 rounded-lg border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-white/[0.02] pr-2 pl-1 transition-all hover:bg-emerald-600/5 dark:hover:bg-white/[0.06]"
                    >
                        <div class="flex h-7 w-7 items-center justify-center rounded-md text-[10px] font-black text-white"
                            :style="{ background: ROLE_COLOR[auth.role.value] }">
                            {{ auth.user.value?.inisial ?? '?' }}
                        </div>
                        <div class="hidden text-left leading-tight sm:block">
                            <p class="text-[11px] font-bold">{{ auth.user.value?.nama_lengkap ?? 'Tamu' }}</p>
                            <p class="text-[10px] font-bold" :style="{ color: ROLE_COLOR[auth.role.value] }">{{ auth.roleLabel.value }}</p>
                        </div>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="hidden h-3 w-3 text-slate-400 dark:text-white/30 transition-transform group-hover:translate-y-0.5 sm:block">
                            <path d="M6 9l6 6 6-6" />
                        </svg>
                    </button>
                </div>
            </header>

            <!-- klik di luar menutup dropdown -->
            <div v-if="searchOpen || notifOpen" @click="closeAll" class="fixed inset-0 z-10"></div>

            <!-- PAGE CONTENT -->
            <main class="relative z-0 flex-1 p-6">
                <slot />
            </main>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import DashboardSidebar from '@/components/DashboardSidebar.vue';
import { ROLE_COLOR, logoutMock, syncAuthFromServer, useAuthMock } from '@/composables/useAuthMock';
import { usePosStore } from '@/composables/usePosStore';
import { apiLogout } from '@/lib/api';
import { dayKey, formatRupiahShort, todayKey } from '@/lib/format';

const auth = useAuthMock();
const store = usePosStore();
const keluar = () => {
    apiLogout().catch(() => {}).finally(() => {
        logoutMock();
        router.visit('/');
    });
};

const page = usePage();
const pageTitle = computed(() => page.props.title || 'Dashboard');
const pageSubtitle = computed(() => page.props.subtitle || 'Ringkasan aktivitas');

/* ================= SEARCH GLOBAL ================= */
const q = ref('');
const searchOpen = ref(false);
const searchRef = ref(null);
const results = computed(() => {
    const s = q.value.trim().toLowerCase();
    if (s.length < 2) return [];
    const out = [];
    for (const b of store.barangAktif.value) {
        if (b.nama.toLowerCase().includes(s) || b.barcode.includes(s))
            out.push({ type: 'produk', icon: '📦', cls: 'bg-amber-500/15 text-amber-700 dark:text-amber-300', label: b.nama, sub: `${b.barcode} • stok ${b.stok}`, href: '/produk' });
        if (out.length >= 5) break;
    }
    for (const p of store.pelangganAktif.value) {
        if (p.nama_pelanggan.toLowerCase().includes(s) || (p.telepon || '').includes(s))
            out.push({ type: 'pelanggan', icon: '👥', cls: 'bg-pink-500/15 text-pink-700 dark:text-pink-300', label: p.nama_pelanggan, sub: store.namaKelompokPelanggan(p.id_kelompok_pelanggan), href: '/pelanggan' });
        if (out.length >= 10) break;
    }
    for (const sp of store.supplierAktif.value) {
        if (sp.nama.toLowerCase().includes(s))
            out.push({ type: 'supplier', icon: '🚚', cls: 'bg-teal-500/15 text-teal-700 dark:text-teal-300', label: sp.nama, sub: sp.no_telepon || 'supplier', href: '/supplier' });
        if (out.length >= 13) break;
    }
    return out.slice(0, 13);
});
function go(r) { closeAll(); router.visit(r.href); }
function goFirst() { if (results.value.length) go(results.value[0]); }
function onKey(e) {
    if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') {
        e.preventDefault();
        searchRef.value?.focus();
        searchOpen.value = true;
    }
}
onMounted(() => window.addEventListener('keydown', onKey));
onUnmounted(() => window.removeEventListener('keydown', onKey));

/* Sinkron peran ke session server saat layout naik. Mencegah menu basi
   (localStorage peran lama) yang diklik lalu mental ke dashboard. */
onMounted(async () => {
    const status = await syncAuthFromServer();
    if (status === 'unauthorized') router.visit('/');
});

/* ================= NOTIFIKASI (ringkasan + badge, beda per peran) =================
   ATURAN: setiap alert hanya boleh link ke halaman yang BISA dibuka peran itu.
   - kasir  : /pelanggan, /transaksi
   - admin  : /produk, /pembelian, /laporan
   - super admin : /sekolah, /user, /laporan, /notifikasi */
const notifOpen = ref(false);
const role = computed(() => auth.role.value);
const alerts = computed(() => {
    const out = [];
    // ---- KASIR: pelanggan + shift sendiri, href aman untuk kasir ----
    if (role.value === 'kasir') {
        const semingguLalu = Date.now() - 7 * 86400000;
        const baru = store.pelangganAktif.value.filter((p) => +new Date(p.created_at) >= semingguLalu);
        if (baru.length)
            out.push({ icon: '🆕', cls: 'bg-pink-500/15 text-pink-700 dark:text-pink-300', title: `${baru.length} pelanggan baru minggu ini`, sub: baru.slice(0, 2).map((p) => p.nama_pelanggan).join(', '), href: '/pelanggan' });
        const piutang = store.piutang.value;
        if (piutang.length)
            out.push({ icon: '💸', cls: 'bg-orange-500/15 text-orange-700 dark:text-orange-300', title: `${piutang.length} piutang belum bayar`, sub: `Total ${formatRupiahShort(piutang.reduce((s, t) => s + t.total_faktur, 0))} — hubungi pelanggan`, href: '/pelanggan' });
        const myId = auth.user.value?.id_user ?? -1;
        const shift = store.penjualanAktif.value.filter((p) => dayKey(p.tanggal_penjualan) === todayKey() && p.id_user === myId).length;
        out.push({ icon: '🧾', cls: 'bg-emerald-500/15 text-emerald-700 dark:text-emerald-300', title: `${shift} transaksi shift saya hari ini`, sub: 'Lihat di riwayat transaksi', href: '/riwayat-transaksi' });
        return out;
    }
    // ---- ADMIN & SUPER ADMIN: barang/stok (super admin dikembalikan ke /notifikasi) ----
    const habis = store.barangAktif.value.filter((b) => b.stok === 0);
    const tipis = store.barangAktif.value.filter((b) => b.stok > 0 && b.stok <= 10);
    for (const b of habis.slice(0, 3))
        out.push({ icon: '⛔', cls: 'bg-rose-500/15 text-rose-700 dark:text-rose-300', title: `Stok habis: ${b.nama}`, sub: role.value === 'admin' ? 'Segera restock via Pembelian' : 'Lihat di Notifikasi', href: role.value === 'admin' ? '/pembelian' : '/notifikasi' });
    if (tipis.length)
        out.push({ icon: '⚠️', cls: 'bg-amber-500/15 text-amber-700 dark:text-amber-300', title: `${tipis.length} barang stok menipis (≤10)`, sub: tipis.slice(0, 2).map((b) => b.nama).join(', '), href: role.value === 'admin' ? '/produk' : '/notifikasi' });
    // ---- ADMIN: + draft pembelian (operasional toko) ----
    if (role.value === 'admin') {
        const draft = store.pembelianAktif.value.filter((p) => p.status_pembelian === 'draft');
        if (draft.length)
            out.push({ icon: '📥', cls: 'bg-violet-500/15 text-violet-700 dark:text-violet-300', title: `${draft.length} pembelian masih draft`, sub: 'Selesaikan agar stok bertambah', href: '/pembelian' });
        const today = store.penjualanAktif.value.filter((p) => dayKey(p.tanggal_penjualan) === todayKey()).length;
        out.push({ icon: '🧾', cls: 'bg-emerald-500/15 text-emerald-700 dark:text-emerald-300', title: `${today} transaksi hari ini`, sub: 'Pantau di laporan', href: '/laporan' });
        return out;
    }
    // ---- SUPER ADMIN: semua notifikasi + jaringan ----
    if (role.value === 'super admin') {
        const piutang = store.piutang.value;
        if (piutang.length)
            out.push({ icon: '💸', cls: 'bg-orange-500/15 text-orange-700 dark:text-orange-300', title: `${piutang.length} piutang belum bayar`, sub: `Total ${formatRupiahShort(piutang.reduce((s, t) => s + t.total_faktur, 0))}`, href: '/laporan' });
        const draft = store.pembelianAktif.value.filter((p) => p.status_pembelian === 'draft');
        if (draft.length)
            out.push({ icon: '📥', cls: 'bg-violet-500/15 text-violet-700 dark:text-violet-300', title: `${draft.length} pembelian masih draft`, sub: 'Lihat di Notifikasi', href: '/notifikasi' });
        const today = store.penjualanAktif.value.filter((p) => dayKey(p.tanggal_penjualan) === todayKey()).length;
        out.push({ icon: '🧾', cls: 'bg-emerald-500/15 text-emerald-700 dark:text-emerald-300', title: `${today} transaksi hari ini`, sub: 'Pantau di laporan', href: '/laporan' });
    }
    return out;
});
function goNotif() { closeAll(); router.visit('/notifikasi'); }
function closeAll() { searchOpen.value = false; notifOpen.value = false; }
</script>
