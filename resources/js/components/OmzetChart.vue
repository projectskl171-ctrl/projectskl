<template>
    <div class="rounded-xl border border-slate-200 dark:border-white/[0.06] bg-white dark:bg-white/[0.02] p-5">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div>
                <h2 class="text-sm font-bold tracking-tight">{{ title }}</h2>
                <p class="mt-0.5 text-[11px] text-slate-500 dark:text-white/40">{{ subtitle }}</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="rounded-md border border-slate-200 dark:border-white/10 bg-slate-900/[0.04] dark:bg-white/5 px-2 py-1 text-[10px] font-bold text-slate-500 dark:text-white/50">Total {{ formatRupiahShort(total) }}</span>
                <div class="flex rounded-lg bg-slate-900/[0.04] dark:bg-white/5 p-0.5 text-[11px] font-black">
                    <button @click="mode = 'bar'" :class="mode === 'bar' ? 'bg-emerald-500 text-white' : 'text-slate-500 dark:text-white/40 hover:text-slate-900 dark:hover:text-white'" class="rounded-md px-2.5 py-1">📊 Batang</button>
                    <button @click="mode = 'line'" :class="mode === 'line' ? 'bg-emerald-500 text-white' : 'text-slate-500 dark:text-white/40 hover:text-slate-900 dark:hover:text-white'" class="rounded-md px-2.5 py-1">📈 Garis</button>
                </div>
            </div>
        </div>

        <!-- ===== MODE BATANG ===== -->
        <template v-if="mode === 'bar'">
            <div class="mt-6 flex h-44 items-end gap-2">
                <div v-for="d in data" :key="d.key" class="group relative flex-1 rounded-t-md bg-gradient-to-t from-emerald-600/40 to-emerald-400/90 transition-all hover:from-emerald-500/60 hover:to-emerald-300"
                    :style="{ height: Math.max(4, (d.total / maxVal) * 100) + '%' }">
                    <span class="pointer-events-none absolute -top-7 left-1/2 -translate-x-1/2 rounded-md border border-slate-200 dark:border-white/10 bg-white dark:bg-black px-1.5 py-0.5 text-[10px] font-bold whitespace-nowrap opacity-0 transition-opacity group-hover:opacity-100">{{ formatRupiahShort(d.total) }}</span>
                </div>
            </div>
            <div class="mt-2 flex gap-2">
                <span v-for="d in data" :key="d.key" class="flex-1 text-center text-[10px] font-medium text-slate-400 dark:text-white/30">{{ d.label }}</span>
            </div>
        </template>

        <!-- ===== MODE GARIS (interaktif, tooltip ngikutin cursor) ===== -->
        <template v-else>
            <div ref="wrapRef" class="relative mt-4 cursor-crosshair select-none" @mousemove="onMove" @mouseleave="hover = null">
                <svg :viewBox="`0 0 ${W} ${H}`" class="block w-full">
                    <defs>
                        <linearGradient id="omzetFill" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#34d399" stop-opacity="0.35" />
                            <stop offset="100%" stop-color="#34d399" stop-opacity="0.02" />
                        </linearGradient>
                    </defs>
                    <!-- grid horizontal -->
                    <line v-for="g in [0.25, 0.5, 0.75]" :key="g" :x1="PAD" :x2="W - PAD" :y1="PAD + (H - 2 * PAD) * g" :y2="PAD + (H - 2 * PAD) * g" :stroke="gridStroke()" stroke-dasharray="3 4" />
                    <!-- area -->
                    <path :d="areaPath" fill="url(#omzetFill)" />
                    <!-- garis -->
                    <path :d="linePath" fill="none" stroke="#34d399" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" vector-effect="non-scaling-stroke" />
                    <!-- titik -->
                    <circle v-for="(p, i) in points" :key="p.x" :cx="p.x" :cy="p.y" r="4"
                        :fill="pointFill(i)" stroke="#34d399" stroke-width="2.5" />
                    <!-- garis vertikal penunjuk -->
                    <line v-if="hover" :x1="points[hover.i].x" :x2="points[hover.i].x" :y1="PAD" :y2="H - PAD" stroke="rgba(52,211,153,0.5)" stroke-dasharray="3 3" />
                </svg>

                <!-- tooltip: kanan-atas cursor, ikut gerak -->
                <div v-if="hover"
                    class="pointer-events-none absolute z-10 w-36 rounded-lg border border-emerald-500/30 bg-white dark:bg-black/90 p-2.5 shadow-xl shadow-emerald-500/10 backdrop-blur"
                    :style="{ left: tipPos.x + 'px', top: tipPos.y + 'px' }">
                    <p class="text-[10px] font-bold tracking-widest text-slate-500 dark:text-white/40 uppercase">{{ data[hover.i].label }}</p>
                    <p class="mt-0.5 text-sm font-black text-emerald-700 dark:text-emerald-400">{{ formatRupiah(data[hover.i].total) }}</p>
                    <p class="mt-0.5 text-[11px] text-slate-500 dark:text-white/50">{{ data[hover.i].trx }} transaksi</p>
                </div>
            </div>
            <div class="mt-1 flex gap-2">
                <span v-for="d in data" :key="d.key" class="flex-1 text-center text-[10px] font-medium text-slate-400 dark:text-white/30">{{ d.label }}</span>
            </div>
            <p class="mt-2 text-[10px] text-slate-400 dark:text-white/25">Arahkan cursor ke garis untuk detail harian.</p>
        </template>
    </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { formatRupiah, formatRupiahShort } from '@/lib/format';

const props = defineProps({
    data: { type: Array, default: () => [] }, // [{ label, key, total, trx }]
    title: { type: String, default: 'Omzet 7 Hari Terakhir' },
    subtitle: { type: String, default: 'tb_penjualan • total_faktur per hari' },
});

const mode = ref('bar');
const total = computed(() => props.data.reduce((s, d) => s + (d.total || 0), 0));
const maxVal = computed(() => Math.max(1, ...props.data.map((d) => d.total || 0)));

/* ---------- warna grid/titik mengikuti tema (dark final, light slate) ---------- */
const isDarkChart = () => typeof document !== 'undefined' && document.documentElement.classList.contains('dark');
const gridStroke = () => (isDarkChart() ? 'rgba(255,255,255,0.06)' : 'rgba(100,116,139,0.22)');
const pointFill = (i) => (hover.value && hover.value.i === i ? '#a7f3d0' : (isDarkChart() ? '#0a0a0a' : '#ffffff'));

/* ---------- geometri line chart (viewBox tetap, responsif) ---------- */
const W = 700, H = 220, PAD = 14;
const points = computed(() => {
    const n = Math.max(1, props.data.length);
    const max = maxVal.value;
    return props.data.map((d, i) => ({
        x: PAD + (n === 1 ? (W - 2 * PAD) / 2 : (i * (W - 2 * PAD)) / (n - 1)),
        y: H - PAD - ((d.total || 0) / max) * (H - 2 * PAD - 8),
    }));
});
const linePath = computed(() => points.value.map((p, i) => `${i === 0 ? 'M' : 'L'}${p.x.toFixed(1)},${p.y.toFixed(1)}`).join(' '));
const areaPath = computed(() => {
    if (!points.value.length) return '';
    const first = points.value[0], last = points.value[points.value.length - 1];
    return `${linePath.value} L${last.x.toFixed(1)},${H - PAD} L${first.x.toFixed(1)},${H - PAD} Z`;
});

/* ---------- tooltip ngikutin cursor (kanan-atas cursor) ---------- */
const wrapRef = ref(null);
const hover = ref(null); // { i, cx, cy } cx/cy = posisi px relatif container
const tipPos = computed(() => {
    if (!hover.value || !wrapRef.value) return { x: 0, y: 0 };
    const el = wrapRef.value;
    const w = el.clientWidth || 1, h = el.clientHeight || 1;
    // posisi px titik data (skala viewBox -> px)
    const px = (points.value[hover.value.i].x / W) * w;
    const py = (points.value[hover.value.i].y / H) * h;
    // kartu di kanan-atas cursor, dijepit biar nggak keluar container
    const TIP_W = 150, TIP_H = 86;
    return {
        x: Math.min(Math.max(4, px + 14), Math.max(4, w - TIP_W - 4)),
        y: Math.min(Math.max(4, py - TIP_H - 8), Math.max(4, h - TIP_H - 4)),
    };
});

function onMove(e) {
    const el = wrapRef.value;
    if (!el || !points.value.length) return;
    const rect = el.getBoundingClientRect();
    const mx = e.clientX - rect.left;
    // cari titik terdekat dari posisi cursor (dalam px)
    const w = rect.width || 1;
    let best = 0, bestDist = Infinity;
    points.value.forEach((p, i) => {
        const px = (p.x / W) * w;
        const dist = Math.abs(px - mx);
        if (dist < bestDist) { bestDist = dist; best = i; }
    });
    hover.value = { i: best };
}
</script>
