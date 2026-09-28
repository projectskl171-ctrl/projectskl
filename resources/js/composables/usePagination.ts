import { computed, ref, watch } from 'vue';

/**
 * Pagination generik: beri computed `filtered`, dapatkan `paged` + info.
 * Reset otomatis ke halaman 1 setiap filter berubah (via deps).
 */
export function usePagination<T>(source: () => T[], perPage = 10, deps: any[] = []) {
    const page = ref(1);
    const filtered = computed(() => source() ?? []);
    const totalPages = computed(() => Math.max(1, Math.ceil(filtered.value.length / perPage)));
    const paged = computed(() => {
        if (page.value > totalPages.value) page.value = totalPages.value;
        const start = (page.value - 1) * perPage;
        return filtered.value.slice(start, start + perPage);
    });

    watch(deps.length ? deps : [filtered], () => { page.value = 1; });

    return { page, totalPages, paged, filtered };
}
