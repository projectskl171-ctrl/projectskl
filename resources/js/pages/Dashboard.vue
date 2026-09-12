<template>
    <div class="space-y-6">
        <!-- STAT CARDS -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div
                v-for="stat in stats"
                :key="stat.label"
                class="relative overflow-hidden rounded-xl border border-white/[0.06] bg-white/[0.02] p-5"
            >
                <div
                    class="pointer-events-none absolute -top-10 -right-10 h-32 w-32 rounded-full blur-3xl"
                    :style="{ background: hexA(stat.color, 0.18) }"
                />
                <div class="relative">
                    <div class="flex items-center justify-between">
                        <div
                            class="flex h-9 w-9 items-center justify-center rounded-lg"
                            :style="{ background: hexA(stat.color, 0.12), color: stat.color }"
                        >
                            <component :is="stat.icon" class="h-[18px] w-[18px]" />
                        </div>
                        <span
                            class="rounded-md px-1.5 py-0.5 text-[10px] font-bold"
                            :style="{ background: hexA(stat.color, 0.12), color: stat.color }"
                        >
                            {{ stat.trend }}
                        </span>
                    </div>
                    <p class="mt-4 text-2xl font-bold tracking-tight">{{ stat.value }}</p>
                    <p class="mt-1 text-xs text-white/40">{{ stat.label }}</p>
                </div>
            </div>
        </div>

        <!-- CHART + RECENT -->
        <div class="grid grid-cols-1 gap-4 xl:grid-cols-3">
            <!-- Chart placeholder -->
            <div class="rounded-xl border border-white/[0.06] bg-white/[0.02] p-5 xl:col-span-2">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-bold tracking-tight">Penjualan 7 Hari Terakhir</h2>
                        <p class="mt-0.5 text-[11px] text-white/40">Ringkasan omzet harian toko</p>
                    </div>
                    <span class="rounded-md border border-white/10 bg-white/5 px-2 py-1 text-[10px] font-bold text-white/50">
                        Minggu ini
                    </span>
                </div>
                <!-- Simple CSS bars -->
                <div class="mt-6 flex h-44 items-end gap-2">
                    <div
                        v-for="(v, i) in bars"
                        :key="i"
                        class="group relative flex-1 rounded-t-md bg-gradient-to-t from-emerald-600/40 to-emerald-400/90 transition-all hover:from-emerald-500/60 hover:to-emerald-300"
                        :style="{ height: v + '%' }"
                    >
                        <span
                            class="pointer-events-none absolute -top-7 left-1/2 -translate-x-1/2 rounded-md border border-white/10 bg-black px-1.5 py-0.5 text-[10px] font-bold whitespace-nowrap opacity-0 transition-opacity group-hover:opacity-100"
                        >
                            {{ v }}%
                        </span>
                    </div>
                </div>
                <div class="mt-2 flex gap-2">
                    <span v-for="(d, i) in days" :key="i" class="flex-1 text-center text-[10px] font-medium text-white/30">
                        {{ d }}
                    </span>
                </div>
            </div>

            <!-- Transaksi terbaru -->
            <div class="rounded-xl border border-white/[0.06] bg-white/[0.02] p-5">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-bold tracking-tight">Transaksi Terbaru</h2>
                    <Link href="/transaksi" class="text-[11px] font-bold text-emerald-400 hover:text-emerald-300">
                        Lihat semua
                    </Link>
                </div>
                <div class="mt-4 space-y-3">
                    <div
                        v-for="trx in recent"
                        :key="trx.id"
                        class="flex items-center gap-3 rounded-lg border border-white/[0.05] bg-white/[0.02] px-3 py-2.5"
                    >
                        <div
                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md text-[11px] font-black"
                            :style="{ background: hexA(trx.color, 0.14), color: trx.color }"
                        >
                            {{ trx.initial }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-xs font-bold">{{ trx.id }}</p>
                            <p class="truncate text-[11px] text-white/40">{{ trx.item }}</p>
                        </div>
                        <p class="shrink-0 text-xs font-bold">{{ trx.total }}</p>
                    </div>
                </div>
                <Link
                    href="/transaksi"
                    class="mt-4 flex h-9 w-full items-center justify-center rounded-lg bg-emerald-500 text-xs font-bold text-white transition-colors hover:bg-emerald-400"
                >
                    + Transaksi Baru
                </Link>
            </div>
        </div>

        <!-- QUICK MENU -->
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            <Link
                v-for="menu in quickMenus"
                :key="menu.href"
                :href="menu.href"
                class="group flex items-center gap-3 rounded-xl border border-white/[0.06] bg-white/[0.02] p-4 transition-colors hover:bg-white/[0.04]"
            >
                <div
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg transition-transform group-hover:scale-105"
                    :style="{ background: hexA(menu.color, 0.12), color: menu.color }"
                >
                    <component :is="menu.icon" class="h-5 w-5" />
                </div>
                <div class="min-w-0">
                    <p class="truncate text-xs font-bold">{{ menu.label }}</p>
                    <p class="truncate text-[11px] text-white/40">{{ menu.desc }}</p>
                </div>
            </Link>
        </div>
    </div>
</template>

<script setup>
import { defineComponent, h } from 'vue';
import { Link } from '@inertiajs/vue3';
import DashboardLayout from '@/layouts/DashboardLayout.vue';

defineOptions({
    layout: DashboardLayout,
});

/* ---- mini icon factory (sama gaya dengan layout) ---- */
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
                        typeof d === 'string' ? h('path', { d, key: i }) : h(d.tag, { ...d, key: i }),
                    ),
                );
        },
    });

const IconCart = makeIcon([
    'M3 3h2l.4 2M7 13h10l4-8H5.4',
    { tag: 'circle', cx: 9, cy: 20, r: 1.5 },
    { tag: 'circle', cx: 17, cy: 20, r: 1.5 },
]);
const IconMoney = makeIcon(['M3 3v18h18', 'M7 14l4-4 4 4 6-6']);
const IconBox = makeIcon(['M21 8l-9-5-9 5 9 5 9-5z', 'M3 8v8l9 5 9-5V8', 'M12 13v8']);
const IconUsers = makeIcon([
    'M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2',
    { tag: 'circle', cx: 9, cy: 7, r: 4 },
]);
const IconPackage = makeIcon([
    'M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z',
    'M3.27 6.96L12 12.01l8.73-5.05',
    'M12 22.08V12',
]);
const IconChart = makeIcon(['M3 3v18h18', 'M7 14l4-4 4 4 6-6']);
const IconTruck = makeIcon([
    'M1 3h15v13H1z',
    'M16 8h4l3 3v5h-7V8z',
    { tag: 'circle', cx: 5.5, cy: 18.5, r: 2.5 },
    { tag: 'circle', cx: 18.5, cy: 18.5, r: 2.5 },
]);

const hexA = (hex, alpha) => {
    const h = hex.replace('#', '');
    const r = parseInt(h.slice(0, 2), 16);
    const g = parseInt(h.slice(2, 4), 16);
    const b = parseInt(h.slice(4, 6), 16);
    return `rgba(${r}, ${g}, ${b}, ${alpha})`;
};

const stats = [
    { label: 'Omzet Hari Ini', value: 'Rp 2,4 Jt', trend: '+12%', color: '#10b981', icon: IconMoney },
    { label: 'Transaksi Hari Ini', value: '48', trend: '+8%', color: '#3b82f6', icon: IconCart },
    { label: 'Total Produk', value: '326', trend: '+3', color: '#f59e0b', icon: IconBox },
    { label: 'Pelanggan', value: '1.204', trend: '+21', color: '#ec4899', icon: IconUsers },
];

const bars = [35, 55, 42, 70, 58, 82, 64];
const days = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];

const recent = [
    { id: '#TRX-1042', item: '3 item • Tunai', total: 'Rp 185rb', initial: 'A', color: '#10b981' },
    { id: '#TRX-1041', item: '5 item • QRIS', total: 'Rp 320rb', initial: 'B', color: '#3b82f6' },
    { id: '#TRX-1040', item: '2 item • Tunai', total: 'Rp 96rb', initial: 'C', color: '#f59e0b' },
    { id: '#TRX-1039', item: '7 item • Transfer', total: 'Rp 512rb', initial: 'D', color: '#8b5cf6' },
];

const quickMenus = [
    { label: 'Transaksi', desc: 'Kasir penjualan', href: '/transaksi', color: '#3b82f6', icon: IconCart },
    { label: 'Produk', desc: 'Kelola stok', href: '/produk', color: '#f59e0b', icon: IconBox },
    { label: 'Pembelian', desc: 'Stok masuk', href: '/pembelian', color: '#8b5cf6', icon: IconPackage },
    { label: 'Laporan', desc: 'Analitik omzet', href: '/laporan', color: '#f97316', icon: IconChart },
];
</script>
