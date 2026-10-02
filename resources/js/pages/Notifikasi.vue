<template>
    <RoleDenied v-if="!auth.can('/notifikasi')" page="Notifikasi" :needed="['Kasir', 'Admin', 'Super Admin']" />
    <div v-else class="space-y-4">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
            <div class="flex flex-wrap gap-1.5">
                <button v-for="f in filters" :key="f.key" @click="fil = f.key"
                    :class="fil === f.key ? 'bg-rose-500 text-white' : 'bg-slate-900/[0.04] dark:bg-white/5 text-slate-500 dark:text-white/50 hover:bg-slate-900/5 dark:hover:bg-white/10'"
                    class="h-9 cursor-pointer rounded-lg px-3 text-xs font-black">{{ f.label }}</button>
            </div>
            <div class="flex items-center gap-2 sm:ml-auto">
                <p class="text-[11px] text-slate-400 dark:text-white/30">{{ filteredApi.length }} notifikasi • {{ unread }} baru</p>
                <button v-if="unread > 0" @click="readAll" class="h-8 cursor-pointer rounded-lg bg-slate-900/[0.04] dark:bg-white/5 px-3 text-[11px] font-bold text-slate-600 dark:text-white/70 hover:bg-slate-900/5 dark:hover:bg-white/10">Tandai semua dibaca</button>
            </div>
        </div>
        <p v-if="loadErr" class="rounded-lg bg-rose-500/10 px-3 py-2 text-[11px] font-bold text-rose-700 dark:text-rose-300">{{ loadErr }}</p>

        <div class="space-y-2">
            <button v-for="n in filteredApi" :key="n.id_notifikasi" @click="buka(n)"
                class="flex w-full cursor-pointer items-start gap-3 rounded-xl border px-4 py-3 text-left transition-colors"
                :class="!Number(n.is_read) ? 'border-emerald-500/30 bg-emerald-500/[0.05] hover:bg-emerald-500/[0.08]' : 'border-slate-200 dark:border-white/[0.06] bg-white dark:bg-white/[0.02] hover:border-emerald-500/30'">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-base" :class="iconCls(n.tipe)">{{ iconEmoji(n.tipe) }}</span>
                <span class="min-w-0 flex-1">
                    <span class="flex flex-wrap items-center gap-1.5 text-xs font-bold">{{ n.judul }}
                        <span v-if="!Number(n.is_read)" class="rounded bg-rose-500 px-1.5 py-px text-[9px] font-black text-white">BARU</span>
                        <span class="rounded bg-slate-900/[0.05] dark:bg-white/10 px-1.5 py-px font-mono text-[9px] font-bold text-slate-500 dark:text-white/40">{{ labelTipe(n.tipe) }}</span>
                    </span>
                    <span class="mt-0.5 block text-[11px] leading-relaxed text-slate-500 dark:text-white/50">{{ n.pesan }}</span>
                    <span class="mt-1 block text-[10px] text-slate-400 dark:text-white/30">{{ formatDateTime(n.created_at) }}{{ n.href ? ` • ${n.href}` : '' }}</span>
                </span>
                <span class="shrink-0 text-[11px] font-black text-emerald-700 dark:text-emerald-400">Buka →</span>
            </button>
            <p v-if="!filteredApi.length" class="rounded-xl border border-dashed border-slate-200 dark:border-white/10 py-10 text-center text-sm text-slate-400 dark:text-white/30">
                {{ unread ? 'Tidak ada notifikasi pada filter ini.' : 'Aman! Tidak ada peringatan. 🎉' }}
            </p>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import RoleDenied from '@/components/RoleDenied.vue';
import { hydrate, usePosStore } from '@/composables/usePosStore';
import { useAuthMock } from '@/composables/useAuthMock';
import { formatDateTime } from '@/lib/format';
import { apiFetch } from '@/lib/api';

defineOptions({ layout: DashboardLayout });
const props = defineProps({ penjualan: Array, barang: Array, pembelian: Array });
onMounted(() => {
    hydrate(props);
    load();
});
const store = usePosStore();
const auth = useAuthMock();

const notifs = ref([]);
const unread = ref(0);
const loadErr = ref('');
const fil = ref('all');

const filters = computed(() => {
    if (auth.role.value === 'kasir') {
        return [
            { key: 'all', label: 'Semua' },
            { key: 'unread', label: 'Belum dibaca' },
            { key: 'kredit', label: 'Kredit' },
            { key: 'omzet', label: 'Lainnya' },
        ];
    }
    if (auth.role.value === 'super admin') {
        return [
            { key: 'all', label: 'Semua' },
            { key: 'unread', label: 'Belum dibaca' },
            { key: 'sekolah', label: 'Langganan' },
        ];
    }
    return [
        { key: 'all', label: 'Semua' },
        { key: 'unread', label: 'Belum dibaca' },
        { key: 'stok', label: 'Stok' },
        { key: 'omzet', label: 'Lainnya' },
    ];
});
const filteredApi = computed(() => {
    let rows = [...notifs.value].sort((a, b) => b.id_notifikasi - a.id_notifikasi);
    if (fil.value === 'unread') rows = rows.filter((n) => !Number(n.is_read));
    else if (fil.value === 'kredit') rows = rows.filter((n) => String(n.tipe).startsWith('kredit'));
    else if (fil.value === 'omzet') rows = rows.filter((n) => String(n.tipe).startsWith('omzet'));
    else if (fil.value === 'stok') rows = rows.filter((n) => String(n.tipe).startsWith('stok'));
    else if (fil.value === 'sekolah') rows = rows.filter((n) => String(n.tipe).startsWith('sekolah'));
    return rows;
});

function iconEmoji(tipe) {
    if (String(tipe).startsWith('kredit')) return '💸';
    if (String(tipe).startsWith('omzet')) return '🎉';
    if (tipe === 'stok_habis') return '⛔';
    if (tipe === 'stok_menipis') return '⚠️';
    if (String(tipe).startsWith('sekolah')) return '🏫';
    return '🔔';
}
function iconCls(tipe) {
    if (String(tipe).startsWith('kredit')) return 'bg-orange-500/15 text-orange-700 dark:text-orange-300';
    if (String(tipe).startsWith('omzet')) return 'bg-emerald-500/15 text-emerald-700 dark:text-emerald-300';
    if (tipe === 'stok_habis') return 'bg-rose-500/15 text-rose-700 dark:text-rose-300';
    if (tipe === 'stok_menipis') return 'bg-amber-500/15 text-amber-700 dark:text-amber-300';
    if (String(tipe).startsWith('sekolah')) return 'bg-sky-500/15 text-sky-700 dark:text-sky-300';
    return 'bg-slate-500/15';
}
function labelTipe(tipe) {
    const map = {
        kredit_2hari: 'kredit • 2 hari',
        kredit_mingguan: 'kredit • mingguan',
        omzet_harian: 'omzet • harian',
        omzet_mingguan: 'omzet • mingguan',
        stok_menipis: 'stok • menipis',
        stok_habis: 'stok • habis',
        sekolah_hampir_30hari: 'sekolah • H-7',
        sekolah_30hari: 'sekolah • 30 hari',
    };
    return map[tipe] || tipe;
}
async function load() {
    loadErr.value = '';
    try {
        const json = await apiFetch('/api/notifikasi');
        notifs.value = json?.data ?? [];
        unread.value = Number(json?.unread ?? notifs.value.filter((n) => !Number(n.is_read)).length);
    } catch (e) {
        loadErr.value = e?.message || 'Gagal memuat notifikasi.';
    }
}
async function buka(n) {
    try {
        if (!Number(n.is_read)) {
            await apiFetch(`/api/notifikasi/${n.id_notifikasi}/read`, { method: 'POST' });
            n.is_read = 1;
            unread.value = Math.max(0, unread.value - 1);
        }
    } catch { /* tetap navigasi */ }
    if (n.href) router.visit(n.href);
}
async function readAll() {
    try {
        await apiFetch('/api/notifikasi/read-all', { method: 'POST' });
        notifs.value = notifs.value.map((n) => ({ ...n, is_read: 1 }));
        unread.value = 0;
    } catch (e) {
        loadErr.value = e?.message || 'Gagal menandai dibaca.';
    }
}
</script>
