<template>
    <!-- ============ SIDEBAR (satu-satunya) ============ -->
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

        <!-- NAV : relative = offsetParent buat sliding indicator -->
        <nav ref="navRef" class="relative flex-1 overflow-y-auto py-4">
            <!-- Badge peran aktif -->
            <div class="mx-5 mb-3 flex items-center gap-2.5 rounded-lg border border-white/[0.06] bg-white/[0.02] px-3 py-2.5">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md text-[11px] font-black text-white"
                    :style="{ background: ROLE_COLOR[auth.role.value] }">
                    {{ auth.user.value?.inisial ?? '?' }}
                </div>
                <div class="min-w-0 flex-1 leading-tight">
                    <p class="truncate text-[11px] font-bold">{{ auth.user.value?.nama_lengkap ?? 'Tamu' }}</p>
                    <p class="text-[10px] font-bold" :style="{ color: ROLE_COLOR[auth.role.value] }">{{ auth.roleLabel.value }}</p>
                </div>
            </div>
            <template v-for="group in visibleGroups" :key="group.label">
                <p class="mt-1 mb-2 px-5 text-[10px] font-bold tracking-[0.14em] text-white/30 uppercase">
                    {{ group.label }}
                </p>
                <div class="mb-4 space-y-0">
                    <Link
                        v-for="item in group.items"
                        :key="item.href"
                        :href="item.href"
                        :data-href="item.href"
                        class="group relative flex h-9 w-full items-center gap-3 overflow-hidden px-5 text-[13px] font-medium transition-colors duration-150 ease-in"
                        :class="isActive(item.href) ? 'text-white' : 'text-white/55 hover:text-white'"
                    >
                        <!-- ===== ACTIVE BG : gradient + canvas particle random ===== -->
                        <span
                            class="pointer-events-none absolute inset-0 overflow-hidden transition-opacity duration-300 ease-in"
                            :class="isActive(item.href) ? 'opacity-100' : 'opacity-0'"
                            aria-hidden="true"
                        >
                            <span
                                class="absolute inset-0"
                                :style="{
                                    background: `linear-gradient(90deg, ${hexA(item.color, 0.28)}, ${hexA(item.color, 0.12)} 55%, ${hexA(item.color, 0.03)})`,
                                }"
                            />
                            <!-- particle RANDOM MOVEMENT (canvas, bukan drift linear) -->
                            <ParticleCanvas
                                v-if="isActive(item.href)"
                                :color="item.color"
                                :count="24"
                            />
                            <!-- floating orbs -->
                            <span
                                class="nav-orb absolute -top-4 left-8 h-10 w-10 rounded-full blur-md"
                                :style="{ background: hexA(item.color, 0.5) }"
                            />
                            <span
                                class="nav-orb-2 absolute -bottom-5 left-24 h-12 w-12 rounded-full blur-lg"
                                :style="{ background: `linear-gradient(135deg, ${hexA(item.color, 0.45)}, ${hexA('#ffffff', 0.12)})` }"
                            />
                            <!-- shine sweep infinite -->
                            <span class="nav-shine absolute inset-y-0 w-16" />
                            <!-- bottom glow line -->
                            <span
                                class="absolute inset-x-0 bottom-0 h-px"
                                :style="{ background: `linear-gradient(90deg, transparent, ${hexA(item.color, 0.7)}, transparent)` }"
                            />
                        </span>
                        <!-- ===== HOVER BG (inactive) ===== -->
                        <span
                            v-if="!isActive(item.href)"
                            class="pointer-events-none absolute inset-0 origin-left scale-x-0 overflow-hidden transition-transform duration-300 ease-in-out group-hover:scale-x-100"
                            :style="{
                                background: `linear-gradient(90deg, ${hexA(item.color, 0.16)}, ${hexA(item.color, 0.04)})`,
                            }"
                            aria-hidden="true"
                        />
                        <component
                            :is="item.icon"
                            class="relative z-10 h-[17px] w-[17px] shrink-0 transition-colors duration-150 ease-in"
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

            <!-- ===== SATU GARIS SELECTED : sliding pindah item + ganti warna ===== -->
            <span
                ref="indicatorRef"
                class="sidebar-indicator"
                :class="{ 'is-hidden': !indicator.visible, 'no-anim': !indicator.ready }"
                :style="{
                    top: indicator.top + 'px',
                    height: indicator.height + 'px',
                    background: indicator.color,
                    boxShadow: `0 0 12px ${hexA(indicator.color, 0.6)}, 0 0 28px ${hexA(indicator.color, 0.25)}`,
                }"
                aria-hidden="true"
            />
        </nav>

        <!-- FOOTER ACTIONS -->
        <div class="shrink-0 space-y-0 border-t border-white/[0.06] py-3">
            <!-- Ganti peran (demo frontend — backend nanti jadi halaman profil) -->
            <div class="flex gap-1.5 px-5 pb-2">
                <button v-for="r in quickRoles" :key="r"
                    @click="gantiPeran(r)"
                    class="h-7 flex-1 rounded-md text-[10px] font-black transition-all hover:brightness-125"
                    :class="auth.role.value === r ? 'text-white ring-1 ring-white/30' : 'text-white/45 bg-white/[0.04]'"
                    :style="auth.role.value === r ? { background: ROLE_COLOR[r] } : {}"
                    :title="'Masuk sebagai ' + ROLE_LABEL[r]">
                    {{ ROLE_LABEL[r].split(' ')[0].toUpperCase() }}
                </button>
            </div>
            <Link
                v-for="item in visibleFooter"
                :key="item.href"
                :href="item.href"
                @click="item.action ? item.action($event) : null"
                class="group relative flex h-9 w-full items-center gap-3 overflow-hidden px-5 text-[13px] font-medium text-white/55 transition-colors duration-150 ease-in hover:text-white"
            >
                <span
                    class="pointer-events-none absolute inset-0 origin-left scale-x-0 overflow-hidden transition-transform duration-300 ease-in group-hover:scale-x-100"
                    :style="{
                        background: `linear-gradient(90deg, ${hexA(item.color, 0.16)}, ${hexA(item.color, 0.04)})`,
                    }"
                    aria-hidden="true"
                />
                <component
                    :is="item.icon"
                    class="relative z-10 h-[17px] w-[17px] shrink-0 transition-colors duration-150 ease-in group-hover:text-[color:var(--c)]"
                    :style="{ '--c': item.color }"
                />
                <span class="relative z-10 flex-1 truncate">{{ item.label }}</span>
            </Link>
        </div>
    </aside>
</template>

<script setup>
import { computed, defineComponent, h, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { ROLE_COLOR, ROLE_LABEL, loginAs, logoutMock, useAuthMock } from '@/composables/useAuthMock';
import { apiLogout } from '@/lib/api';

const auth = useAuthMock();
const quickRoles = ['kasir', 'admin', 'super admin'];
const gantiPeran = (r) => {
    loginAs(r);
    router.visit('/dashboard');
};
const keluar = (e) => {
    e.preventDefault();
    apiLogout().catch(() => {}).finally(() => {
        logoutMock();
        router.visit('/');
    });
};

/* =========================================================
   PARTICLE CANVAS — random movement (velocity acak, bounce, twinkle)
   ========================================================= */
const ParticleCanvas = defineComponent({
    props: {
        color: { type: String, required: true },
        count: { type: Number, default: 24 },
    },
    setup(props) {
        const canvasRef = ref(null);
        let raf = 0;
        let ro = null;
        let parts = [];
        let w = 0;
        let hgt = 0;

        const rand = (a, b) => a + Math.random() * (b - a);

        const seed = () => {
            parts = Array.from({ length: props.count }, () => ({
                x: rand(0, w),
                y: rand(0, hgt),
                vx: rand(-0.45, 0.45),
                vy: rand(-0.35, 0.35),
                r: rand(0.6, 1.9),
                base: rand(0.25, 0.9),
                amp: rand(0.15, 0.4),
                speed: rand(0.02, 0.07),
                phase: rand(0, Math.PI * 2),
                white: Math.random() < 0.3,
            }));
        };

        const resize = () => {
            const c = canvasRef.value;
            if (!c || !c.parentElement) return;
            const dpr = Math.min(window.devicePixelRatio || 1, 2);
            w = c.parentElement.clientWidth;
            hgt = c.parentElement.clientHeight;
            c.width = Math.max(1, Math.floor(w * dpr));
            c.height = Math.max(1, Math.floor(hgt * dpr));
            const ctx = c.getContext('2d');
            ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
            seed();
        };

        const hexToRgb = (hex) => {
            const v = hex.replace('#', '');
            return [
                parseInt(v.slice(0, 2), 16),
                parseInt(v.slice(2, 4), 16),
                parseInt(v.slice(4, 6), 16),
            ];
        };

        onMounted(() => {
            const c = canvasRef.value;
            if (!c) return;
            resize();
            ro = new ResizeObserver(resize);
            if (c.parentElement) ro.observe(c.parentElement);
            const [r, g, b] = hexToRgb(props.color);
            let t = 0;
            const loop = () => {
                t += 1;
                const ctx = c.getContext('2d');
                ctx.clearRect(0, 0, w, hgt);
                for (const p of parts) {
                    // random walk halus biar makin hidup
                    p.vx += rand(-0.02, 0.02);
                    p.vy += rand(-0.02, 0.02);
                    // clamp kecepatan
                    const sp = Math.hypot(p.vx, p.vy);
                    const max = 0.6;
                    if (sp > max) {
                        p.vx = (p.vx / sp) * max;
                        p.vy = (p.vy / sp) * max;
                    }
                    p.x += p.vx;
                    p.y += p.vy;
                    if (p.x < 0) { p.x = 0; p.vx *= -1; }
                    if (p.x > w) { p.x = w; p.vx *= -1; }
                    if (p.y < 0) { p.y = 0; p.vy *= -1; }
                    if (p.y > hgt) { p.y = hgt; p.vy *= -1; }
                    const a = Math.max(0, Math.min(1, p.base + Math.sin(t * p.speed + p.phase) * p.amp));
                    ctx.beginPath();
                    ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
                    ctx.fillStyle = p.white
                        ? `rgba(255,255,255,${a})`
                        : `rgba(${r},${g},${b},${a})`;
                    ctx.fill();
                }
                raf = requestAnimationFrame(loop);
            };
            raf = requestAnimationFrame(loop);
        });

        onUnmounted(() => {
            cancelAnimationFrame(raf);
            if (ro) ro.disconnect();
        });

        return () => h('canvas', {
            ref: canvasRef,
            class: 'absolute inset-0 h-full w-full',
        });
    },
});

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
const IconShield = makeIcon(['M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z']);
const IconChart = makeIcon(['M3 3v18h18', 'M7 14l4-4 4 4 6-6']);
const IconBell = makeIcon([
    'M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9',
    'M13.73 21a2 2 0 01-3.46 0',
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
const IconSchool = makeIcon([
    'M3 10l9-6 9 6',
    'M5 10v10h14V10',
    'M9 20v-6h6v6',
]);
const IconReceipt = makeIcon([
    'M5 3h14v18l-2.3-1.5L14.4 21l-2.4-1.5L9.6 21l-2.3-1.5L5 21V3z',
    'M9 8h6',
    'M9 12h6',
]);

/* =========================================================
   NAV CONFIG — `roles` = peran yang boleh LIHAT menu ini.
   kasir: Dashboard, Transaksi, Riwayat Transaksi, Pelanggan, Notifikasi, Settings.
   admin: Dashboard, Produk, Pembelian, Supplier, User, Laporan, Notifikasi.
   super admin: Dashboard, Sekolah, User, Laporan, Notifikasi.
   ========================================================= */
const navGroups = [
    {
        label: 'Utama',
        items: [
            { label: 'Dashboard', href: '/dashboard', color: '#10b981', icon: IconGrid, roles: ['kasir', 'admin', 'super admin'] },
            { label: 'Transaksi', href: '/transaksi', color: '#3b82f6', icon: IconCart, badge: 'Baru', roles: ['kasir'] },
            { label: 'Riwayat Transaksi', href: '/riwayat-transaksi', color: '#38bdf8', icon: IconReceipt, roles: ['kasir'] },
            { label: 'Pembelian', href: '/pembelian', color: '#8b5cf6', icon: IconPackage, roles: ['admin'] },
        ],
    },
    {
        label: 'Master',
        items: [
            { label: 'Produk', href: '/produk', color: '#f59e0b', icon: IconBox, roles: ['admin'] },
            { label: 'Pelanggan', href: '/pelanggan', color: '#ec4899', icon: IconUsers, roles: ['kasir'] },
            { label: 'Supplier', href: '/supplier', color: '#14b8a6', icon: IconTruck, roles: ['admin'] },
            { label: 'Sekolah', href: '/sekolah', color: '#0ea5e9', icon: IconSchool, roles: ['super admin'] },
            { label: 'User', href: '/user', color: '#6366f1', icon: IconShield, roles: ['admin', 'super admin'] },
        ],
    },
    {
        label: 'Analitik',
        items: [
            { label: 'Laporan', href: '/laporan', color: '#f97316', icon: IconChart, roles: ['admin', 'super admin'] },
            { label: 'Notifikasi', href: '/notifikasi', color: '#f43f5e', icon: IconBell, roles: ['kasir', 'admin', 'super admin'] },
        ],
    },
];

/* Menu yang terlihat untuk peran yang sedang login */
const visibleGroups = computed(() =>
    navGroups
        .map((g) => ({ ...g, items: g.items.filter((i) => auth.can(i.href)) }))
        .filter((g) => g.items.length),
);

const footerItems = [
    { label: 'Settings', href: '/settings', color: '#94a3b8', icon: IconSettings, roles: ['kasir'] },
    { label: 'Logout', href: '/', color: '#ef4444', icon: IconLogout, action: keluar, roles: ['kasir', 'admin', 'super admin'] },
];

/* Footer difilter peran juga (admin tidak dapat Settings) */
const visibleFooter = computed(() =>
    footerItems.filter((i) => !i.roles || auth.can(i.href) || i.href === '/'),
);

/* =========================================================
   ACTIVE STATE
   ========================================================= */
const page = usePage();
const currentPath = computed(() => page.url.split('?')[0].replace(/\/$/, '') || '/');
const isActive = (href) => currentPath.value === href.replace(/\/$/, '');
const activeItem = computed(() => {
    for (const g of navGroups) {
        const found = g.items.find((i) => isActive(i.href));
        if (found) return found;
    }
    return null;
});

/* =========================================================
   SLIDING INDICATOR TUNGGAL — 1 garis pindah + ganti warna
   ========================================================= */
const navRef = ref(null);
const indicatorRef = ref(null);
const indicator = ref({
    top: 0,
    height: 20,
    color: '#10b981',
    visible: false,
    ready: false, // false = tanpa animasi (first paint)
});

const updateIndicator = () => {
    const nav = navRef.value;
    const item = activeItem.value;
    if (!nav || !item) {
        indicator.value.visible = false;
        return;
    }
    const el = nav.querySelector(`[data-href="${item.href}"]`);
    if (!el) {
        indicator.value.visible = false;
        return;
    }
    // offsetTop relatif ke <nav> karena nav = offsetParent (relative)
    const barH = 20;
    indicator.value.top = el.offsetTop + (el.offsetHeight - barH) / 2;
    indicator.value.height = barH;
    indicator.value.color = item.color;
    indicator.value.visible = true;
    // nyalakan animasi setelah paint pertama biar tidak sliding dari 0
    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            indicator.value.ready = true;
        });
    });
};

const onResize = () => updateIndicator();

onMounted(async () => {
    await nextTick();
    updateIndicator();
    // fallback: font/layout shift
    setTimeout(updateIndicator, 100);
    window.addEventListener('resize', onResize);
});

onUnmounted(() => window.removeEventListener('resize', onResize));

watch(currentPath, async () => {
    await nextTick();
    updateIndicator();
});

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

<style scoped>
/* ============ SATU GARIS SELECTED : sliding + ganti warna ============ */
.sidebar-indicator {
    position: absolute;
    right: 0;
    width: 3px;
    border-radius: 9999px 0 0 9999px;
    z-index: 20;
    pointer-events: none;
    transition:
        top 0.45s cubic-bezier(0.22, 1, 0.36, 1),
        height 0.35s cubic-bezier(0.22, 1, 0.36, 1),
        background-color 0.35s ease,
        box-shadow 0.35s ease,
        opacity 0.3s ease;
    animation: bar-glow 2.4s ease-in-out infinite;
}
.sidebar-indicator.is-hidden {
    opacity: 0;
}
.sidebar-indicator.no-anim {
    transition: none;
    animation: none;
}
@keyframes bar-glow {
    0%,
    100% {
        filter: brightness(1);
    }
    50% {
        filter: brightness(1.5);
    }
}

/* ============ FLOATING ORBS ============ */
.nav-orb {
    animation: orb-float 5s ease-in-out infinite alternate;
}
.nav-orb-2 {
    animation: orb-float-2 7s ease-in-out infinite alternate;
}
@keyframes orb-float {
    from {
        transform: translate(0, 0) scale(1);
        opacity: 0.9;
    }
    to {
        transform: translate(14px, 6px) scale(1.25);
        opacity: 0.5;
    }
}
@keyframes orb-float-2 {
    from {
        transform: translate(0, 0) scale(1.1);
        opacity: 0.7;
    }
    to {
        transform: translate(-12px, -5px) scale(0.9);
        opacity: 0.4;
    }
}

/* ============ SHINE SWEEP ============ */
.nav-shine {
    background: linear-gradient(
        100deg,
        transparent 20%,
        rgba(255, 255, 255, 0.22) 50%,
        transparent 80%
    );
    filter: blur(1px);
    animation: shine-sweep 3.8s ease-in infinite;
}
@keyframes shine-sweep {
    0% {
        left: -30%;
        opacity: 0;
    }
    15% {
        opacity: 1;
    }
    60% {
        left: 110%;
        opacity: 1;
    }
    100% {
        left: 110%;
        opacity: 0;
    }
}

@media (prefers-reduced-motion: reduce) {
    .sidebar-indicator,
    .nav-orb,
    .nav-orb-2,
    .nav-shine {
        animation: none;
    }
    .sidebar-indicator {
        transition: none;
    }
}
</style>
