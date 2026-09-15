<template>
    <div class="flex min-h-[50vh] flex-col items-center justify-center rounded-xl border border-dashed border-white/[0.08] bg-white/[0.02] p-10 text-center">
        <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-rose-500/10 text-2xl">🔒</div>
        <h2 class="mt-4 text-base font-black tracking-tight">Akses Ditolak</h2>
        <p class="mt-1 max-w-sm text-sm text-white/40">
            Halaman <span class="font-mono font-bold text-white/70">{{ page }}</span> butuh peran
            <span class="font-bold text-white/70">{{ needed.join(' / ') }}</span>.
            Kamu masuk sebagai <span class="font-bold" :style="{ color: myColor }">{{ myLabel }}</span>.
        </p>
        <p class="mt-1 text-[11px] text-white/25">Mode demo frontend — backend nanti ganti ini dengan 403 + middleware role.</p>
        <div class="mt-5 flex flex-wrap justify-center gap-2">
            <button v-for="r in roles" :key="r"
                @click="masuk(r)"
                class="h-10 rounded-lg px-4 text-xs font-black text-white transition-transform hover:scale-105"
                :style="{ background: ROLE_COLOR[r] }">
                Masuk sebagai {{ ROLE_LABEL[r] }}
            </button>
        </div>
        <button @click="back" class="mt-3 text-xs font-bold text-white/40 hover:text-white">← Kembali ke Dashboard</button>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { ROLE_COLOR, ROLE_LABEL, loginAs, useAuthMock } from '@/composables/useAuthMock';

const props = defineProps({ page: { type: String, default: 'ini' }, needed: { type: Array, default: () => [] } });
const auth = useAuthMock();
const roles = ['kasir', 'admin', 'super admin'];
const myLabel = computed(() => auth.roleLabel.value);
const myColor = computed(() => ROLE_COLOR[auth.role.value]);

function masuk(r) {
    loginAs(r);
    router.visit('/dashboard');
}
function back() {
    router.visit('/dashboard');
}
</script>
