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
                    <!-- NOTIF (DB persisten + badge angka merah) -->
                    <div class="relative">
                        <button
                            type="button"
                            @click="notifOpen = !notifOpen"
                            title="Notifikasi"
                            class="relative flex h-9 w-9 cursor-pointer items-center justify-center rounded-lg border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-white/[0.02] text-slate-600 dark:text-white/60 transition-all hover:bg-emerald-600/5 dark:hover:bg-white/[0.06] hover:text-slate-900 dark:hover:text-white"
                        >
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="h-[17px] w-[17px]">
                                <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9" />
                                <path d="M13.73 21a2 2 0 01-3.46 0" />
                            </svg>
                            <span v-if="unread > 0" class="absolute -top-1.5 -right-1.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-rose-500 px-1 text-[9px] font-black text-white ring-2 ring-white dark:ring-[#0a0a0a]">{{ unread > 99 ? '99+' : unread }}</span>
                        </button>
                        <!-- dropdown ringkas -->
                        <div v-if="notifOpen"
                            class="absolute top-11 right-0 z-30 w-80 overflow-hidden rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-[#111] shadow-2xl shadow-slate-900/10 dark:shadow-black/60">
                            <div class="flex items-center justify-between border-b border-slate-200 dark:border-white/[0.06] px-4 py-2.5">
                                <p class="text-xs font-black">Notifikasi <span class="text-slate-400 dark:text-white/30">({{ unread }} baru)</span></p>
                                <div class="flex items-center gap-2">
                                    <button v-if="unread > 0" @click="readAll" class="cursor-pointer text-[11px] font-bold text-slate-500 dark:text-white/40 hover:text-slate-900 dark:hover:text-white">Tandai dibaca</button>
                                    <button @click="goNotif" class="cursor-pointer text-[11px] font-bold text-emerald-700 dark:text-emerald-400 hover:text-emerald-300">Lihat semua →</button>
                                </div>
                            </div>
                            <div class="max-h-80 overflow-y-auto py-1.5">
                                <button v-for="a in notifs.slice(0, 6)" :key="a.id_notifikasi" @click="goAlert(a)"
                                    class="flex w-full cursor-pointer items-start gap-2.5 px-4 py-2.5 text-left hover:bg-emerald-600/5 dark:hover:bg-white/5"
                                    :class="!Number(a.is_read) && 'bg-emerald-500/[0.06]'">
                                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md text-xs" :class="iconCls(a.tipe)">{{ iconEmoji(a.tipe) }}</span>
                                    <span class="min-w-0 flex-1">
                                        <span class="flex items-center gap-1.5 text-xs font-bold">{{ a.judul }}
                                            <span v-if="!Number(a.is_read)" class="rounded bg-rose-500 px-1 py-px text-[8px] font-black text-white">BARU</span>
                                        </span>
                                        <span class="block truncate text-[11px] text-slate-500 dark:text-white/40">{{ a.pesan }}</span>
                                    </span>
                                </button>
                                <p v-if="!notifs.length" class="px-4 py-6 text-center text-xs text-slate-400 dark:text-white/30">Aman! Tidak ada peringatan. 🎉</p>
                            </div>
                        </div>
                    </div>

                    <!-- PROFILE : klik = buka Settings -->
                    <button
                        type="button"
                        @click="goSettings"
                        title="Buka pengaturan"
                        class="group flex h-9 cursor-pointer items-center gap-2.5 rounded-lg border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-white/[0.02] pr-2 pl-1 transition-all hover:bg-emerald-600/5 dark:hover:bg-white/[0.06]"
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

            <!-- PAGE CONTENT (tanpa z-0: agar modal fixed z-50 di dalam slot
                 bisa menutup sidebar z-30 & header z-20, bukan cuma content) -->
            <main class="relative flex-1 p-6">
                <slot />
            </main>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import DashboardSidebar from '@/components/DashboardSidebar.vue';
import { ROLE_COLOR, syncAuthFromServer, useAuthMock } from '@/composables/useAuthMock';
import { apiFetch } from '@/lib/api';

const auth = useAuthMock();
/* Klik foto/nama profil = masuk ke Settings (bukan logout). */
function goSettings() { closeAll(); router.visit('/settings'); }

const page = usePage();
const pageTitle = computed(() => page.props.title || 'Dashboard');
const pageSubtitle = computed(() => page.props.subtitle || 'Ringkasan aktivitas');

/* Sinkron peran ke session server saat layout naik. Mencegah menu basi
   (localStorage peran lama) yang diklik lalu mental ke dashboard. */
let poll = null;
onMounted(async () => {
    const status = await syncAuthFromServer();
    if (status === 'unauthorized') router.visit('/');
    fetchNotifs();
    poll = setInterval(fetchNotifs, 15000);
});
onUnmounted(() => { if (poll) clearInterval(poll); });

/* ================= NOTIFIKASI DB (tb_notifikasi, badge angka merah) ================= */
const notifOpen = ref(false);
const notifs = ref([]);
const unread = ref(0);

function iconEmoji(tipe) {
    if (tipe?.startsWith('kredit')) return '💸';
    if (tipe?.startsWith('omzet')) return '🎉';
    if (tipe === 'stok_habis') return '⛔';
    if (tipe === 'stok_menipis') return '⚠️';
    if (tipe?.startsWith('sekolah')) return '🏫';
    return '🔔';
}
function iconCls(tipe) {
    if (tipe?.startsWith('kredit')) return 'bg-orange-500/15 text-orange-700 dark:text-orange-300';
    if (tipe?.startsWith('omzet')) return 'bg-emerald-500/15 text-emerald-700 dark:text-emerald-300';
    if (tipe === 'stok_habis') return 'bg-rose-500/15 text-rose-700 dark:text-rose-300';
    if (tipe === 'stok_menipis') return 'bg-amber-500/15 text-amber-700 dark:text-amber-300';
    if (tipe?.startsWith('sekolah')) return 'bg-sky-500/15 text-sky-700 dark:text-sky-300';
    return 'bg-slate-500/15 text-slate-600 dark:text-white/60';
}
async function fetchNotifs() {
    try {
        const json = await apiFetch('/api/notifikasi');
        notifs.value = json?.data ?? [];
        unread.value = Number(json?.unread ?? notifs.value.filter((n) => !Number(n.is_read)).length);
    } catch { /* abaikan: badge hilang bila offline */ }
}
async function goAlert(a) {
    try {
        if (!Number(a.is_read)) {
            await apiFetch(`/api/notifikasi/${a.id_notifikasi}/read`, { method: 'POST' });
            a.is_read = 1;
            unread.value = Math.max(0, unread.value - 1);
        }
    } catch { /* tetap navigasi walau gagal tandai */ }
    closeAll();
    router.visit(a.href || '/notifikasi');
}
async function readAll() {
    try {
        await apiFetch('/api/notifikasi/read-all', { method: 'POST' });
        notifs.value = notifs.value.map((n) => ({ ...n, is_read: 1 }));
        unread.value = 0;
    } catch { /* abaikan */ }
}
function goNotif() { closeAll(); router.visit('/notifikasi'); }
function closeAll() { notifOpen.value = false; }
</script>
