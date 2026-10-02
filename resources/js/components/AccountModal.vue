<template>
    <Teleport to="body">
        <div v-if="show" class="fixed inset-0 z-[200] flex items-center justify-center bg-black/70 p-4 backdrop-blur-sm">
            <div class="w-full max-w-sm rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-[#111] p-5">
                <h3 class="text-sm font-black">{{ mode === 'logout' ? 'Keluar akun?' : 'Beralih akun' }}</h3>

                <!-- Akun saat ini (paling atas) -->
                <div v-if="current" class="mt-3 flex items-center gap-3 rounded-lg border border-emerald-500/30 bg-emerald-500/[0.06] px-3 py-2.5">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg text-xs font-black text-white" :style="{ background: colorOf(current.role) }">
                        {{ current.inisial }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-xs font-black">{{ current.nama_lengkap }}</p>
                        <p class="truncate font-mono text-[10px] text-slate-500 dark:text-white/40">@{{ current.username }} • {{ labelOf(current.role) }}</p>
                    </div>
                    <span class="shrink-0 rounded bg-emerald-500/15 px-1.5 py-0.5 text-[9px] font-black text-emerald-700 dark:text-emerald-300">AKTIF</span>
                </div>

                <!-- Maks 2 akun lain yang pernah login -->
                <p class="mt-3 text-[10px] font-bold tracking-wider text-slate-400 dark:text-white/30 uppercase">Akun lain di perangkat ini ({{ others.length }}/2)</p>
                <div v-if="others.length" class="mt-1.5 space-y-1.5">
                    <button v-for="a in others" :key="a.id_user" @click="$emit('switch', a)"
                        class="flex w-full cursor-pointer items-center gap-3 rounded-lg border border-slate-200 dark:border-white/[0.06] bg-white dark:bg-black/30 px-3 py-2.5 text-left hover:border-emerald-500/40 hover:bg-emerald-500/[0.06]">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-[11px] font-black text-white" :style="{ background: colorOf(a.role) }">
                            {{ a.inisial }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-xs font-bold">{{ a.nama_lengkap }}</p>
                            <p class="truncate font-mono text-[10px] text-slate-500 dark:text-white/40">@{{ a.username }} • {{ labelOf(a.role) }}</p>
                        </div>
                        <span class="shrink-0 text-[10px] font-black text-emerald-700 dark:text-emerald-400">Pakai →</span>
                    </button>
                </div>
                <p v-else class="mt-1.5 rounded-lg border border-dashed border-slate-200 dark:border-white/10 py-4 text-center text-[11px] text-slate-400 dark:text-white/30">Belum ada akun lain. Login dengan akun lain dulu.</p>
                <p class="mt-2 text-[10px] leading-relaxed text-slate-400 dark:text-white/30">Klik akun lain untuk beralih.</p>

                <div class="mt-4 flex gap-2">
                    <button @click="$emit('close')" class="h-10 flex-1 cursor-pointer rounded-lg bg-slate-900/[0.04] dark:bg-white/5 text-sm font-bold">{{ mode === 'logout' ? 'Batal' : 'Tutup' }}</button>
                    <button v-if="mode === 'logout'" @click="$emit('logout')" :disabled="loading" class="h-10 flex-1 cursor-pointer rounded-lg bg-rose-500 text-sm font-black text-white hover:bg-rose-400 disabled:opacity-50">{{ loading ? 'Keluar…' : 'Logout' }}</button>
                </div>
                <p v-if="mode === 'logout'" class="mt-2 text-center text-[10px] font-bold text-rose-600 dark:text-rose-400">⚠️ Akun ini akan keluar & sesi berakhir.</p>
            </div>
        </div>
    </Teleport>
</template>

<script setup>
import { ROLE_COLOR, ROLE_LABEL } from '@/composables/useAuthMock';

defineProps({
    show: { type: Boolean, default: false },
    mode: { type: String, default: 'logout' }, // 'logout' | 'switch'
    current: { type: Object, default: null },
    others: { type: Array, default: () => [] },
    loading: { type: Boolean, default: false },
});
defineEmits(['close', 'logout', 'switch']);

const colorOf = (role) => ROLE_COLOR[role] ?? '#64748b';
const labelOf = (role) => ROLE_LABEL[role] ?? role;
</script>
