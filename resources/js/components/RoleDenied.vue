<template>
    <div class="flex min-h-[50vh] flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 bg-white p-10 text-center shadow-sm dark:border-white/[0.08] dark:bg-white/[0.02] dark:shadow-none">
        <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-rose-500/10 text-2xl">🔒</div>
        <h2 class="mt-4 text-base font-black tracking-tight text-slate-900 dark:text-white">Akses Ditolak</h2>
        <p class="mt-1 max-w-sm text-sm text-slate-500 dark:text-white/40">
            Halaman <span class="font-mono font-bold text-slate-700 dark:text-white/70">{{ page }}</span> butuh peran
            <span class="font-bold text-slate-700 dark:text-white/70">{{ needed.join(' / ') }}</span>.
            Kamu masuk sebagai <span class="font-bold" :style="{ color: myColor }">{{ myLabel }}</span>.
        </p>
        <div class="mt-5 flex flex-wrap justify-center gap-2">
            <button @click="keluar"
                class="h-10 rounded-lg bg-emerald-600 px-4 text-xs font-black text-white transition-transform hover:scale-105 hover:bg-emerald-500">
                Keluar &amp; Login Ulang
            </button>
        </div>
        <button @click="back" class="mt-3 text-xs font-bold text-slate-400 hover:text-slate-700 dark:text-white/40 dark:hover:text-white">← Kembali ke Dashboard</button>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { ROLE_COLOR, logoutMock, useAuthMock } from '@/composables/useAuthMock';
import { apiLogout } from '@/lib/api';

const props = defineProps({ page: { type: String, default: 'ini' }, needed: { type: Array, default: () => [] } });
const auth = useAuthMock();
const myLabel = computed(() => auth.roleLabel.value);
const myColor = computed(() => ROLE_COLOR[auth.role.value]);

function keluar() {
    apiLogout().catch(() => {}).finally(() => {
        logoutMock();
        router.visit('/');
    });
}
function back() {
    router.visit('/dashboard');
}
</script>
