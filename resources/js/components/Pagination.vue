<template>
    <div v-if="totalPages > 1" class="mt-3 flex flex-wrap items-center justify-between gap-2 text-[11px] text-slate-500 dark:text-white/40">
        <button @click="$emit('update:page', Math.max(1, page - 1))" :disabled="page <= 1"
            class="h-8 rounded-lg bg-slate-900/[0.04] dark:bg-white/5 px-3 font-bold text-slate-600 dark:text-white/70 disabled:opacity-40">‹ Prev</button>
        <div class="flex flex-wrap items-center justify-center gap-1">
            <button v-for="p in pages" :key="p.key" @click="p.num && $emit('update:page', p.num)" :disabled="!p.num"
                :class="[
                    'h-8 min-w-8 rounded-lg px-2 font-black',
                    p.num === page
                        ? 'bg-emerald-500 text-white'
                        : p.num
                          ? 'bg-slate-900/[0.04] dark:bg-white/5 text-slate-600 dark:text-white/70 hover:bg-slate-900/10 dark:hover:bg-white/10'
                          : 'text-slate-400 dark:text-white/25',
                ]">{{ p.label }}</button>
        </div>
        <span class="shrink-0">Halaman {{ page }} / {{ totalPages }}</span>
        <button @click="$emit('update:page', Math.min(totalPages, page + 1))" :disabled="page >= totalPages"
            class="h-8 rounded-lg bg-slate-900/[0.04] dark:bg-white/5 px-3 font-bold text-slate-600 dark:text-white/70 disabled:opacity-40">Next ›</button>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    page: { type: Number, required: true },
    totalPages: { type: Number, required: true },
    maxButtons: { type: Number, default: 7 },
});
defineEmits(['update:page']);

/* Nomor halaman 1, 2, 3 … x dengan ellipsis (… ) di tengah. */
const pages = computed(() => {
    const total = Math.max(1, props.totalPages);
    const cur = Math.min(Math.max(1, props.page), total);
    const max = Math.max(5, props.maxButtons);
    const nums = new Set([1, total, cur - 1, cur, cur + 1]);
    if (cur <= 3) { nums.add(2); nums.add(3); nums.add(4); }
    if (cur >= total - 2) { nums.add(total - 1); nums.add(total - 2); nums.add(total - 3); }
    const sorted = [...nums].filter((n) => n >= 1 && n <= total).sort((a, b) => a - b).slice(0, max + 2);
    const out = [];
    let prev = 0;
    for (const n of sorted) {
        if (n - prev > 1) out.push({ key: `e${n}`, num: 0, label: '…' });
        out.push({ key: `p${n}`, num: n, label: String(n) });
        prev = n;
    }
    return out;
});
</script>
