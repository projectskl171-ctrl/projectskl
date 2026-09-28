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
                    <!-- NOTIF -->
                    <div class="relative">
                        <button
                            type="button"
                            @click="notifOpen = !notifOpen"
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
                                <button v-for="(a, i) in alerts.slice(0, 6)" :key="i" @click="goAlert(a)"
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

                    <!-- PROFILE : klik = buka Settings -->
                    <button
                        type="button"
                        @click="goSettings"
                        title="Buka pengaturan"
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
            <div v-if="notifOpen" @click="closeAll" class="fixed inset-0 z-10"></div>

            <!-- PAGE CONTENT -->
            <main class="relative z-0 flex-1 p-6">
                <slot />
            </main>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import DashboardSidebar from '@/components/DashboardSidebar.vue';
import { ROLE_COLOR, syncAuthFromServer, useAuthMock } from '@/composables/useAuthMock';
import { usePosStore } from '@/composables/usePosStore';
import { dayKey, formatRupiahShort, todayKey } from '@/lib/format';

const auth = useAuthMock();
const store = usePosStore();
/* Klik foto/nama profil = masuk ke Settings (bukan logout). */
function goSettings() { closeAll(); router.visit('/settings'); }
function goAlert(a) { closeAll(); router.visit(a.href); }

const page = usePage();
const pageTitle = computed(() => page.props.title || 'Dashboard');
const pageSubtitle = computed(() => page.props.subtitle || 'Ringkasan aktivitas');

/* Sinkron peran ke session server saat layout naik. Mencegah menu basi
   (localStorage peran lama) yang diklik lalu mental ke dashboard. */
onMounted(async () => {
    const status = await syncAuthFromServer();
    if (status === 'unauthorized') router.visit('/');
});

/* ================= NOTIFIKASI (ringkasan + badge, beda per peran) =================
   ATURAN: setiap alert hanya boleh link ke halaman yang BISA dibuka peran itu.
   - kasir  : /pelanggan, /transaksi, /riwayat-transaksi
   - admin  : /produk, /pembelian, /laporan
   - super admin : hanya sekolah & admin baru hari ini (/sekolah, /user) */
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
    // ---- ADMIN: barang/stok. Super admin ditangani di blok bawah. ----
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
    // ---- SUPER ADMIN: hanya sekolah & admin yang bertambah hari ini ----
    if (role.value === 'super admin') {
        const today = todayKey();
        const sekolahBaru = store.state.sekolah.filter((s) => s.created_at && dayKey(s.created_at) === today);
        for (const s of sekolahBaru.slice(0, 3))
            out.push({ icon: '🏫', cls: 'bg-sky-500/15 text-sky-700 dark:text-sky-300', title: `Sekolah baru: ${s.nama_sekolah}`, sub: `${s.kode_sekolah} • terdaftar hari ini`, href: '/sekolah' });
        const adminBaru = store.usersAktif.value.filter((u) => u.id_role === 2 && u.created_at && dayKey(u.created_at) === today);
        for (const u of adminBaru.slice(0, 3))
            out.push({ icon: '👤', cls: 'bg-violet-500/15 text-violet-700 dark:text-violet-300', title: `Admin baru: ${u.nama_lengkap}`, sub: `@${u.username} • ${store.namaSekolah(u.id_sekolah)}`, href: '/user' });
    }
    return out;
});
function goNotif() { closeAll(); router.visit('/notifikasi'); }
function closeAll() { notifOpen.value = false; }
</script>
