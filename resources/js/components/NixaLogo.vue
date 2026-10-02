<script setup>
defineOptions({ inheritAttrs: false });
defineProps({ cls: { type: String, default: 'h-9 w-9' } });
</script>

<template>
    <svg viewBox="0 0 48 48" fill="none" :class="cls" role="img" aria-label="NIXA">
        <defs>
            <linearGradient id="nixa-tile" x1="6" y1="4" x2="42" y2="44" gradientUnits="userSpaceOnUse">
                <stop offset="0" stop-color="#10b981" />
                <stop offset="0.55" stop-color="#0d9488" />
                <stop offset="1" stop-color="#0ea5e9" />
            </linearGradient>
            <linearGradient id="nixa-shine" x1="8" y1="4" x2="30" y2="20" gradientUnits="userSpaceOnUse">
                <stop offset="0" stop-color="#ffffff" stop-opacity="0.55" />
                <stop offset="1" stop-color="#ffffff" stop-opacity="0" />
            </linearGradient>
        </defs>
        <rect x="2" y="2" width="44" height="44" rx="13" fill="url(#nixa-tile)" />
        <rect x="2" y="2" width="44" height="44" rx="13" fill="url(#nixa-shine)" />
        <!-- N perkasa -->
        <path d="M15 34V14l18 20V14" stroke="#ffffff" stroke-width="5" stroke-linecap="round" stroke-linejoin="round" />
        <!-- koin emas + garis kas -->
        <circle cx="34.5" cy="13.5" r="3.4" fill="#fbbf24" stroke="#fff7ed" stroke-width="1.4" />
        <rect x="14" y="37.5" width="20" height="2.6" rx="1.3" fill="#ffffff" opacity="0.85" />
    </svg>
</template>
