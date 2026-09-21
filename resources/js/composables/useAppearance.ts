import type { ComputedRef, Ref } from 'vue';
import { computed, onMounted, ref } from 'vue';
import type { Appearance, ResolvedAppearance } from '@/types';

export type { Appearance, ResolvedAppearance };

export type UseAppearanceReturn = {
    appearance: Ref<Appearance>;
    resolvedAppearance: ComputedRef<ResolvedAppearance>;
    updateAppearance: (value: Appearance) => void;
};

export function updateTheme(value: Appearance): void {
    if (typeof window === 'undefined') {
        return;
    }

    if (value === 'system') {
        const mediaQueryList = window.matchMedia(
            '(prefers-color-scheme: dark)',
        );
        const systemTheme = mediaQueryList.matches ? 'dark' : 'light';

        document.documentElement.classList.toggle(
            'dark',
            systemTheme === 'dark',
        );
    } else {
        document.documentElement.classList.toggle('dark', value === 'dark');
    }
}

const setCookie = (name: string, value: string, days = 365) => {
    if (typeof document === 'undefined') {
        return;
    }

    const maxAge = days * 24 * 60 * 60;

    document.cookie = `${name}=${value};path=/;max-age=${maxAge};SameSite=Lax`;
};

const mediaQuery = () => {
    if (typeof window === 'undefined') {
        return null;
    }

    return window.matchMedia('(prefers-color-scheme: dark)');
};

const getStoredAppearance = () => {
    if (typeof window === 'undefined') {
        return null;
    }

    return localStorage.getItem('appearance') as Appearance | null;
};

const prefersDark = (): boolean => {
    if (typeof window === 'undefined') {
        return false;
    }

    return window.matchMedia('(prefers-color-scheme: dark)').matches;
};

const handleSystemThemeChange = () => {
    const currentAppearance = getStoredAppearance();

    updateTheme(currentAppearance || 'system');
};

/** Kunci tunggal tema KasirKu. Bertahan di localStorage walau browser ditutup. */
export const THEME_KEY = 'appearance';

type SimpleTheme = 'light' | 'dark';

/** Migrasi sekali dari kunci lama (site-theme / kasirku_theme_v1). */
function migrateLegacyTheme(): SimpleTheme | null {
    if (typeof window === 'undefined') {
        return null;
    }

    try {
        const legacy =
            localStorage.getItem('site-theme') ||
            localStorage.getItem('kasirku_theme_v1');
        if (legacy === 'white' || legacy === 'terang' || legacy === 'light')
            return 'light';
        if (legacy === 'dark' || legacy === 'gelap') return 'dark';
    } catch {
        /* abaikan */
    }

    return null;
}

export function getSavedTheme(): Appearance {
    return getStoredAppearance() ?? migrateLegacyTheme() ?? 'dark';
}

export function initializeTheme(): void {
    if (typeof window === 'undefined') {
        return;
    }

    // Default KasirKu = dark agar stabil di semua OS; pilihan user
    // (light/dark/system) diingat permanen via localStorage + cookie.
    updateTheme(getSavedTheme());

    // Set up system theme change listener...
    mediaQuery()?.addEventListener('change', handleSystemThemeChange);
}

const appearance = ref<Appearance>('system');

export function useAppearance(): UseAppearanceReturn {
    onMounted(() => {
        appearance.value = getSavedTheme();
    });

    const resolvedAppearance = computed<ResolvedAppearance>(() => {
        if (appearance.value === 'system') {
            return prefersDark() ? 'dark' : 'light';
        }

        return appearance.value;
    });

    function updateAppearance(value: Appearance) {
        appearance.value = value;

        // Store in localStorage for client-side persistence...
        localStorage.setItem('appearance', value);

        // Store in cookie for SSR...
        setCookie('appearance', value);

        updateTheme(value);
    }

    return {
        appearance,
        resolvedAppearance,
        updateAppearance,
    };
}
