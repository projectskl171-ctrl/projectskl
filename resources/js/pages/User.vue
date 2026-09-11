<template>
    <div class="flex min-h-[60vh] flex-col items-center justify-center rounded-xl border border-dashed border-white/[0.08] bg-white/[0.02] p-12 text-center">
        <div class="mb-4 flex h-14 w-14 items-center justify-center rounded-xl bg-white/[0.04] text-white/40">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" class="h-6 w-6">
                <circle cx="12" cy="12" r="10" />
                <path d="M12 16v-4M12 8h.01" />
            </svg>
        </div>
        <h2 class="text-base font-bold tracking-tight">Transaksi</h2>
        <p class="mt-1 max-w-sm text-sm text-white/40">
            Halaman ini sedang dalam pengembangan.
        </p>
    </div>
</template>

<script setup>
import DashboardLayout from '@/layouts/DashboardLayout.vue';

defineOptions({
    layout: DashboardLayout,
});
</script>