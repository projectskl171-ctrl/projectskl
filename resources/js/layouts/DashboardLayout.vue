<template>
    <div class="grid min-h-dvh grid-cols-[248px_1fr] bg-[#0a0a0a] font-sans text-white">
        <!-- ============ SIDEBAR ============ -->
        <aside
            class="sticky top-0 z-30 flex h-dvh flex-col border-r border-white/[0.06] bg-[#0d0d0d]"
        >
            <!-- BRAND -->
            <div class="flex h-16 shrink-0 items-center gap-3 border-b border-white/[0.06] px-5">
                <div
                    class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-500 text-white shadow-[0_0_0_1px_rgba(16,185,129,0.2),0_4px_12px_-2px_rgba(16,185,129,0.4)]"
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4">
                        <path d="M3 3h2l.4 2M7 13h10l4-8H5.4" />
                        <circle cx="9" cy="20" r="1.5" />
                        <circle cx="17" cy="20" r="1.5" />
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="truncate text-[13px] font-bold tracking-tight leading-none">KasirKu</p>
                    <p class="mt-1 truncate text-[10px] font-medium tracking-[0.14em] text-white/40 uppercase leading-none">
                        POS System
                    </p>
                </div>
            </div>

            <!-- NAV -->
            <nav class="flex-1 overflow-y-auto px-3 py-4">
                <template v-for="group in navGroups" :key="group.label">
                    <p class="mt-1 mb-2 px-3 text-[10px] font-bold tracking-[0.14em] text-white/30 uppercase">
                        {{ group.label }}
                    </p>
                    <div class="mb-4 space-y-0.5">
                        <Link
                            v-for="item in group.items"
                            :key="item.href"
                            :href="item.href"
                            class="group relative flex h-9 w-full items-center gap-3 overflow-hidden rounded-lg px-3 text-[13px] font-medium transition-colors duration-150"
                            :class="
                                isActive(item.href)
                                    ? 'text-white'
                                    : 'text-white/55 hover:text-white'
                            "
                        >
                            <!-- active bg -->
                            <span
                                v-if="isActive(item.href)"
                                class="pointer-events-none absolute inset-0 rounded-lg"
                                :style="{ background: hexA(item.color, 0.14) }"
                            />
                            <!-- hover slide bg -->
                            <span
                                v-else
                                class="pointer-events-none absolute inset-0 origin-left scale-x-0 rounded-lg transition-transform duration-500 ease-[cubic-bezier(0.4,0,1,1)] group-hover:scale-x-100"
                                :style="{ background: hexA(item.color, 0.09) }"
                            />
                            <!-- left accent bar (active) -->
                            <span
                                v-if="isActive(item.href)"
                                class="pointer-events-none absolute top-1/2 left-0 h-5 w-[3px] -translate-y-1/2 rounded-r-full"
                                :style="{ background: item.color, boxShadow: `0 0 12px ${hexA(item.color, 0.6)}` }"
                            />
                            <component
                                :is="item.icon"
                                class="relative z-10 h-[17px] w-[17px] shrink-0 transition-colors duration-200"
                                :style="isActive(item.href) ? { color: item.color } : {}"
                            />
                            <span class="relative z-10 flex-1 truncate">{{ item.label }}</span>
                            <span
                                v-if="item.badge"
                                class="relative z-10 rounded-md bg-white/10 px-1.5 py-0.5 text-[9px] font-bold tracking-wide"
                            >
                                {{ item.badge }}
                            </span>
                        </Link>
                    </div>
                </template>
            </nav>

            <!-- FOOTER ACTIONS -->
            <div class="shrink-0 space-y-0.5 border-t border-white/[0.06] p-3">
                <Link
                    v-for="item in footerItems"
                    :key="item.href"
                    :href="item.href"
                    class="group relative flex h-9 w-full items-center gap-3 overflow-hidden rounded-lg px-3 text-[13px] font-medium text-white/55 transition-colors duration-150 hover:text-white"
                >
                    <span
                        class="pointer-events-none absolute inset-0 origin-left scale-x-0 rounded-lg transition-transform duration-500 ease-[cubic-bezier(0.4,0,1,1)] group-hover:scale-x-100"
                        :style="{ background: hexA(item.color, 0.09) }"
                    />
                    <component
                        :is="item.icon"
                        class="relative z-10 h-[17px] w-[17px] shrink-0 transition-colors duration-200 group-hover:text-[color:var(--c)]"
                        :style="{ '--c': item.color }"
                    />
                    <span class="relative z-10 flex-1 truncate">{{ item.label }}</span>
                </Link>
            </div>
        </aside>

        <!-- ============ MAIN ============ -->
        <div class="flex min-w-0 flex-col">
            <!-- TOPBAR -->
            <header
                class="sticky top-0 z-20 flex h-16 items-center justify-between gap-4 border-b border-white/[0.06] bg-[#0a0a0a]/85 px-6 backdrop-blur-xl"
            >
                <div class="min-w-0">
                    <h1 class="truncate text-[15px] font-bold tracking-tight">
                        {{ pageTitle }}
                    </h1>
                    <p class="truncate text-[11px] text-white/40">
                        {{ pageSubtitle }}
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <!-- SEARCH -->
                    <label
                        class="hidden h-9 items-center gap-2 rounded-lg border border-white/[0.08] bg-white/[0.02] px-3 transition-colors focus-within:border-emerald-500/40 focus-within:bg-white/[0.04] md:flex"
                    >
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-3.5 w-3.5 text-white/40">
                            <circle cx="11" cy="11" r="7" />
                            <path d="M21 21l-4.3-4.3" />
                        </svg>
                        <input
                            placeholder="Cari…"
                            class="w-40 bg-transparent text-xs text-white outline-none placeholder:text-white/30"
                        />
                        <kbd class="rounded border border-white/10 bg-white/5 px-1 py-px font-mono text-[9px] font-bold text-white/40">
                            ⌘K
                        </kbd>
                    </label>

                    <!-- NOTIF -->
                    <button
                        type="button"
                        class="relative flex h-9 w-9 items-center justify-center rounded-lg border border-white/[0.08] bg-white/[0.02] text-white/60 transition-all hover:bg-white/[0.06] hover:text-white"
                    >
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="h-[17px] w-[17px]">
                            <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9" />
                            <path d="M13.73 21a2 2 0 01-3.46 0" />
                        </svg>
                        <span class="absolute top-2 right-2.5 h-1.5 w-1.5 rounded-full bg-rose-500 ring-2 ring-[#0a0a0a]" />
                    </button>

                    <!-- PROFILE -->
                    <button
                        type="button"
                        class="group flex h-9 items-center gap-2.5 rounded-lg border border-white/[0.08] bg-white/[0.02] pr-2 pl-1 transition-all hover:bg-white/[0.06]"
                    >
                        <div class="flex h-7 w-7 items-center justify-center rounded-md bg-gradient-to-br from-emerald-400 to-emerald-600 text-[11px] font-black text-white">
                            A
                        </div>
                        <div class="hidden text-left leading-tight sm:block">
                            <p class="text-[11px] font-bold">Admin Utama</p>
                            <p class="text-[10px] text-white/40">Super Admin</p>
                        </div>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="hidden h-3 w-3 text-white/30 transition-transform group-hover:translate-y-0.5 sm:block">
                            <path d="M6 9l6 6 6-6" />
                        </svg>
                    </button>
                </div>
            </header>

            <!-- PAGE CONTENT -->
            <main class="flex-1 p-6">
                <slot />
            </main>
        </div>
    </div>
</template>

<script setup>
import { computed, defineComponent, h } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

/* =========================================================
   ICON FACTORY
   ========================================================= */
const makeIcon = (paths) =>
    defineComponent({
        setup(_, { attrs }) {
            return () =>
                h(
                    'svg',
                    {
                        viewBox: '0 0 24 24',
                        fill: 'none',
                        stroke: 'currentColor',
                        'stroke-width': 1.8,
                        'stroke-linecap': 'round',
                        'stroke-linejoin': 'round',
                        ...attrs,
                    },
                    paths.map((d, i) =>
                        typeof d === 'string'
                            ? h('path', { d, key: i })
                            : h(d.tag, { ...d, key: i }),
                    ),
                );
        },
    });

const IconGrid = makeIcon([
    { tag: 'rect', x: 3, y: 3, width: 7, height: 7, rx: 1.5 },
    { tag: 'rect', x: 14, y: 3, width: 7, height: 7, rx: 1.5 },
    { tag: 'rect', x: 3, y: 14, width: 7, height: 7, rx: 1.5 },
    { tag: 'rect', x: 14, y: 14, width: 7, height: 7, rx: 1.5 },
]);

const IconCart = makeIcon([
    'M3 3h2l.4 2M7 13h10l4-8H5.4',
    'M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17',
    { tag: 'circle', cx: 9, cy: 20, r: 1.5 },
    { tag: 'circle', cx: 17, cy: 20, r: 1.5 },
]);

const IconBox = makeIcon([
    'M21 8l-9-5-9 5 9 5 9-5z',
    'M3 8v8l9 5 9-5V8',
    'M12 13v8',
]);

const IconPackage = makeIcon([
    'M16.5 9.4L7.5 4.21',
    'M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z',
    'M3.27 6.96L12 12.01l8.73-5.05',
    'M12 22.08V12',
]);

const IconUsers = makeIcon([
    'M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2',
    { tag: 'circle', cx: 9, cy: 7, r: 4 },
    'M23 21v-2a4 4 0 00-3-3.87',
    'M16 3.13a4 4 0 010 7.75',
]);

const IconTruck = makeIcon([
    'M1 3h15v13H1z',
    'M16 8h4l3 3v5h-7V8z',
    { tag: 'circle', cx: 5.5, cy: 18.5, r: 2.5 },
    { tag: 'circle', cx: 18.5, cy: 18.5, r: 2.5 },
]);

const IconShield = makeIcon([
    'M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z',
]);

const IconChart = makeIcon([
    'M3 3v18h18',
    'M7 14l4-4 4 4 6-6',
]);

const IconSettings = makeIcon([
    { tag: 'circle', cx: 12, cy: 12, r: 3 },
    'M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 11-2.83 2.83l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 11-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 11-2.83-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H3a2 2 0 110-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 112.83-2.83l.06.06a1.65 1.65 0 001.82.33H9a1.65 1.65 0 001-1.51V3a2 2 0 114 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 112.83 2.83l-.06.06a1.65 1.65 0 00-.33 1.82V9a1.65 1.65 0 001.51 1H21a2 2 0 110 4h-.09a1.65 1.65 0 00-1.51 1z',
]);

const IconLogout = makeIcon([
    'M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4',
    'M16 17l5-5-5-5',
    'M21 12H9',
]);

/* =========================================================
   NAV CONFIG — pakai Inertia route helper (Ziggy)
   fallback ke string biasa kalau belum setup
   ========================================================= */
const navGroups = [
    {
        label: 'Utama',
        items: [
            { label: 'Dashboard', href: '/dashboard', color: '#10b981', icon: IconGrid },
            { label: 'Transaksi', href: '/transaksi', color: '#3b82f6', icon: IconCart, badge: 'Baru' },
            { label: 'Pembelian', href: '/pembelian', color: '#8b5cf6', icon: IconPackage },
        ],
    },
    {
        label: 'Master',
        items: [
            { label: 'Produk', href: '/produk', color: '#f59e0b', icon: IconBox },
            { label: 'Pelanggan', href: '/pelanggan', color: '#ec4899', icon: IconUsers },
            { label: 'Supplier', href: '/supplier', color: '#14b8a6', icon: IconTruck },
            { label: 'User', href: '/user', color: '#6366f1', icon: IconShield },
        ],
    },
    {
        label: 'Analitik',
        items: [
            { label: 'Laporan', href: '/laporan', color: '#f97316', icon: IconChart },
        ],
    },
];

const footerItems = [
    { label: 'Settings', href: '/settings/profile', color: '#94a3b8', icon: IconSettings },
    { label: 'Logout', href: '/logout', color: '#ef4444', icon: IconLogout },
];

/* =========================================================
   ACTIVE STATE — baca dari usePage().url
   ========================================================= */
const page = usePage();
const currentPath = computed(() => page.url.split('?')[0].replace(/\/$/, '') || '/');

const isActive = (href) => currentPath.value === href.replace(/\/$/, '');

/* =========================================================
   PAGE TITLE + SUBTITLE (dari meta yang kita taruh di pages)
   ========================================================= */
const pageTitle = computed(() => page.props.title || 'Dashboard');
const pageSubtitle = computed(() => page.props.subtitle || 'Ringkasan aktivitas');

/* =========================================================
   UTIL
   ========================================================= */
const hexA = (hex, alpha) => {
    const h = hex.replace('#', '');
    const r = parseInt(h.slice(0, 2), 16);
    const g = parseInt(h.slice(2, 4), 16);
    const b = parseInt(h.slice(4, 6), 16);
    return `rgba(${r}, ${g}, ${b}, ${alpha})`;
};
</script>