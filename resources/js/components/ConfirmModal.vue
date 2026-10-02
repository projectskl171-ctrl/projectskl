<template>
    <Teleport to="body">
        <div v-if="show" class="fixed inset-0 z-[200] flex items-center justify-center bg-black/70 p-4 backdrop-blur-sm">
            <div class="w-full max-w-sm rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-[#111] p-5">
                <h3 class="text-sm font-black" :class="danger ? 'text-rose-700 dark:text-rose-300' : ''">{{ title }}</h3>
                <p class="mt-2 text-xs leading-relaxed whitespace-pre-line text-slate-600 dark:text-white/70">{{ message }}</p>
                <div class="mt-4 flex gap-2">
                    <button v-if="!hideCancel" @click="$emit('cancel')" class="h-10 flex-1 cursor-pointer rounded-lg bg-slate-900/[0.04] dark:bg-white/5 text-sm font-bold">{{ cancelLabel }}</button>
                    <button @click="$emit('confirm')" :disabled="loading" class="h-10 flex-1 cursor-pointer rounded-lg text-sm font-black text-white disabled:opacity-50" :class="danger ? 'bg-rose-500 hover:bg-rose-400' : 'bg-emerald-500 hover:bg-emerald-400'">{{ loading ? 'Memproses…' : confirmLabel }}</button>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<script setup>
defineProps({
    show: { type: Boolean, default: false },
    title: { type: String, default: 'Yakin?' },
    message: { type: String, default: '' },
    confirmLabel: { type: String, default: 'Ya' },
    cancelLabel: { type: String, default: 'Batal' },
    danger: { type: Boolean, default: false },
    loading: { type: Boolean, default: false },
    hideCancel: { type: Boolean, default: false },
});
defineEmits(['confirm', 'cancel']);
</script>
