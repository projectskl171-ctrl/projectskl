<template>
    <Teleport to="body">
        <main
            :style="themeVars[theme]"
            class="fixed inset-0 z-[9999] overflow-hidden bg-[var(--bg-primary)] font-sans text-[var(--text-primary)]"
            @mousemove="handleGlobalMouseMove"
        >
            <div class="absolute top-6 right-6 z-30">
                <motion.button
                    :while-hover="{ scale: 1.05 }"
                    :while-tap="{ scale: 0.95 }"
                    class="flex items-center justify-center rounded-full border-[var(--border)] bg-black/20 p-2.5 text-[var(--text-secondary)] backdrop-blur-md transition-all hover:border-green-400/30 hover:text-[var(--text-primary)]"
                    @click="toggleTheme"
                >
                    {{ theme === 'dark' ? '☀️' : '🌙' }}
                </motion.button>
            </div>

            <div
                class="absolute inset-0 z-0 bg-gradient-to-br from-[#021508] via-[#083317] to-[#011206]"
            />
            <motion.div
                :style="{ x: blobX, y: blobY }"
                class="absolute top-[-25%] right-[-10%] z-0 h-[55vw] w-[55vw] rounded-full bg-emerald-400/25 blur-[140px]"
            />
            <motion.div
                :style="{ x: blobX, y: blobY }"
                class="absolute bottom-[-25%] left-[5%] z-0 h-[45vw] w-[45vw] rounded-full bg-green-400/20 blur-[130px]"
            />
            <div
                class="pointer-events-none absolute inset-0 z-0 bg-[linear-gradient(rgba(134,239,172,0.07)_1px,transparent_1px),linear-gradient(90deg,rgba(134,239,172,0.07)_1px,transparent_1px)] [mask-image:radial-gradient(ellipse_at_center,black_30%,transparent_80%)] bg-[size:64px_64px]"
            />

            <div
                class="pointer-events-none absolute inset-0 z-0 flex items-center justify-center overflow-hidden select-none"
            >
                <motion.div
                    class="flex flex-col items-center text-[18vw] leading-none font-black tracking-tighter whitespace-nowrap text-transparent opacity-60"
                    :style="{ WebkitTextStroke: '2px rgba(134,239,172,0.15)' }"
                    :animate="{ x: [-15, 15, -15] }"
                    :transition="{
                        duration: 12,
                        repeat: Infinity,
                        ease: 'easeInOut',
                    }"
                >
                    <span>KASIR</span>
                    <span class="mt-[-8vw]">KU</span>
                </motion.div>
            </div>

            <ParticlesEngine />

            <div class="absolute inset-0 z-10 flex">
                <motion.div
                    :initial="false"
                    :animate="{ left: '50%' }"
                    :transition="{ duration: 0 }"
                    class="absolute top-0 flex h-full w-[50vw] items-center justify-center p-12"
                >
                    <AnimatePresence mode="wait">
                        <motion.div
                            :key="'words'"
                            :initial="{ opacity: 0, scale: 0.96 }"
                            :animate="{ opacity: 1, scale: 1 }"
                            :exit="{ opacity: 0, scale: 0.96 }"
                            :transition="{ duration: 0.25, ease: EASE_FAST }"
                            class="relative z-10 flex max-w-lg flex-col items-center gap-6 text-center"
                        >
                            <div
                                class="inline-flex items-center gap-2 rounded-full border border-green-300/30 bg-green-500/10 px-5 py-2 text-xs font-bold tracking-widest text-green-300 uppercase shadow-[0_0_20px_rgba(34,197,94,0.15)] backdrop-blur-md"
                            >
                                <span class="relative flex h-2 w-2">
                                    <span
                                        class="absolute inline-flex h-full w-full animate-ping rounded-full bg-green-400 opacity-75"
                                    />
                                    <span
                                        class="relative inline-flex h-2 w-2 rounded-full bg-green-400"
                                    />
                                </span>
                                {{
                                    'KasirKu • POS Kasir'
                                }}
                            </div>

                            <h3
                                class="text-5xl leading-[1.05] font-black tracking-tighter whitespace-pre-line !text-white text-[var(--text-primary)] drop-shadow-[0_4px_30px_rgba(0,0,0,0.5)] lg:text-6xl"
                            >
                                {{ 'Jualan\nMakin Mudah' }}
                            </h3>

                            <div
                                class="relative mt-1 flex h-[75px] w-full items-center justify-center"
                            >
                                <AnimatePresence mode="wait">
                                    <motion.div
                                        :key="quoteIdx"
                                        :initial="{ opacity: 0, y: 10 }"
                                        :animate="{ opacity: 1, y: 0 }"
                                        :exit="{ opacity: 0, y: -10 }"
                                        :transition="{ duration: 0.4 }"
                                        class="absolute text-center"
                                    >
                                        <p
                                            class="text-sm leading-relaxed text-green-100/90 italic lg:text-base"
                                        >
                                            "{{ QUOTES[quoteIdx].text }}"
                                        </p>
                                        <p
                                            class="mt-2 text-xs font-bold tracking-widest text-green-400 uppercase"
                                        >
                                            — {{ QUOTES[quoteIdx].author }}
                                        </p>
                                    </motion.div>
                                </AnimatePresence>
                            </div>

                        </motion.div>
                    </AnimatePresence>
                </motion.div>
                <motion.div
                    :initial="false"
                    :animate="{ left: '0%' }"
                    :transition="{ duration: 0 }"
                    class="absolute top-0 z-20 h-full w-[50vw] bg-[var(--bg-panel)]"
                >
                    <div
                        class="pointer-events-none absolute top-0 h-full w-[100px] -right-[99px]"
                    >
                        <svg
                            class="h-full w-full"
                            viewBox="0 0 100 1000"
                            preserveAspectRatio="none"
                        >
                            <motion.path
                                fill="var(--wave-color)"
                                :animate="{ d: [WAVE_A, WAVE_B, WAVE_A] }"
                                :transition="{
                                    duration: 6,
                                    repeat: Infinity,
                                    ease: 'easeInOut',
                                }"
                            />
                        </svg>
                    </div>

                    <div
                        class="absolute inset-0 z-10 flex items-center justify-center px-8 py-10 lg:px-16"
                    >
                        <AnimatePresence mode="wait">
                            <motion.div
                                :key="'login'"
                                :initial="{
                                    opacity: 0,
                                    x: 20,
                                }"
                                :animate="{ opacity: 1, x: 0 }"
                                :exit="{ opacity: 0, x: -20 }"
                                :transition="{
                                    duration: 0.25,
                                    ease: EASE_FAST,
                                }"
                                class="flex w-full max-w-md flex-col justify-center"
                            >
                                <div class="mb-7">
                                    <h2
                                        class="flex items-center gap-3 text-4xl font-black tracking-tight text-[var(--text-primary)]"
                                    >
                                        {{ 'Selamat Datang' }}
                                        <div
                                            class="h-2.5 w-2.5 animate-pulse rounded-full bg-green-400 shadow-[0_0_10px_#4ade80]"
                                        />
                                    </h2>
                                    <p
                                        class="mt-2 text-sm font-medium text-[var(--text-secondary)]"
                                    >
                                        {{
                                            'Masuk untuk mengelola kasir tokomu.'
                                        }}
                                    </p>
                                </div>

                                <div
                                    v-if="errorMessage"
                                    class="mb-4 rounded-xl border border-red-400/20 bg-red-500/10 p-3 text-sm font-medium text-red-400"
                                >
                                    {{ errorMessage }}
                                </div>

                                <form
                                    class="flex flex-col gap-5"
                                    @submit.prevent="handleLogin"
                                >
                                    <FloatingInput
                                        v-model="formState.username"
                                        type="text"
                                        label="Username"
                                        :icon="IconUser"
                                    />
                                    <FloatingInput
                                        v-model="formState.password"
                                        type="password"
                                        label="Password"
                                        :icon="IconLock"
                                    />

                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <label
                                            class="group flex cursor-pointer items-center gap-2"
                                        >
                                            <div
                                                class="relative flex h-4 w-4 items-center justify-center rounded border border-[var(--border)] bg-[var(--input-bg)] transition-colors group-hover:border-green-400/50"
                                            >
                                                <input
                                                    type="checkbox"
                                                    class="absolute cursor-pointer opacity-0"
                                                    :checked="formState.remember"
                                                    @change="
                                                        handleInput(
                                                            'remember',
                                                            $event.target
                                                                .checked,
                                                        )
                                                    "
                                                />
                                                <svg
                                                    v-if="formState.remember"
                                                    class="h-3 w-3 text-green-400"
                                                    fill="none"
                                                    viewBox="0 0 24 24"
                                                    stroke="currentColor"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="3"
                                                        d="M5 13l4 4L19 7"
                                                    />
                                                </svg>
                                            </div>
                                            <span
                                                class="text-xs text-[var(--text-secondary)] transition-colors group-hover:text-[var(--text-primary)]"
                                                >Remember me</span
                                            >
                                        </label>
                                        <a
                                            href="#"
                                            class="text-xs font-bold text-green-400 transition-colors hover:text-green-300"
                                            >Forgot Password?</a
                                        >
                                    </div>

                                    <MagneticButton
                                        type="submit"
                                        :disabled="isLoading"
                                        class="group relative mt-2 w-full overflow-hidden rounded-2xl bg-gradient-to-r from-green-400 via-emerald-400 to-green-500 py-4 text-sm font-black tracking-widest text-black uppercase transition-all duration-300 hover:shadow-[0_0_35px_rgba(74,222,128,0.5)] disabled:cursor-not-allowed disabled:opacity-70"
                                    >
                                        <span
                                            class="relative z-10 flex items-center justify-center gap-2"
                                        >
                                            {{
                                                isLoading
                                                    ? 'Memproses...'
                                                    : 'Masuk'
                                            }}
                                            <svg
                                                v-if="!isLoading"
                                                class="h-4 w-4"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2.5"
                                                    d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"
                                                />
                                            </svg>
                                        </span>
                                    </MagneticButton>

                                    <div class="mt-1 rounded-2xl border border-green-400/20 bg-green-500/[0.06] px-4 py-3 text-center">
                                        <p class="text-[11px] font-black tracking-[0.2em] text-green-400/80 uppercase">
                                            Akun demo
                                        </p>
                                        <p class="mt-1 font-mono text-[11px] text-[var(--text-secondary)]">
                                            sch001_kasir • sch001_admin • sch001_superadmin
                                        </p>
                                        <p class="mt-0.5 text-[10px] text-[var(--text-secondary)] opacity-70">
                                            password: 123
                                        </p>
                                    </div>
                                </form>
                            </motion.div>
                        </AnimatePresence>
                    </div>
                </motion.div>
            </div>
            <motion.div
                class="pointer-events-none absolute inset-0 z-40 mix-blend-overlay"
                :style="{ background: spotlightBg }"
            />
        </main>
    </Teleport>
</template>

<script setup>
import {
    ref,
    reactive,
    onMounted,
    onUnmounted,
    defineComponent,
    h,
} from 'vue';
import { router as inertiaRouter } from '@inertiajs/vue3';
import { loginAsMockUser } from '@/composables/useAuthMock';
import { apiLogin, apiMe } from '@/lib/api';
import {
    motion,
    AnimatePresence,
    useMotionValue,
    useSpring,
    useTransform,
} from 'motion-v';

/* ================================================== */
/* KONFIG VISUAL */
/* ================================================== */
const WAVE_A =
    'M0,-200 L60,-200 C120,150 0,400 80,750 C130,950 20,1050 50,1200 L0,1200 Z';
const WAVE_B =
    'M0,-200 L40,-200 C0,150 120,400 30,750 C-10,950 90,1050 60,1200 L0,1200 Z';
const EASE_FAST = [0.16, 1, 0.3, 1];

const QUOTES = [
    {
        text: 'Kasir cepat, antrean pendek, pelanggan senang.',
        author: 'KasirKu POS',
    },
    {
        text: 'Stok tercatat, omzet terpantau, usaha tenang.',
        author: 'KasirKu POS',
    },
    {
        text: 'Satu aplikasi untuk kasir, stok, dan laporan.',
        author: 'KasirKu POS',
    },
];

/* ================================================== */
/* ICONS */
/* ================================================== */
const baseSvg = (size) => ({
    class: size,
    fill: 'none',
    viewBox: '0 0 24 24',
    stroke: 'currentColor',
});

const IconUser = defineComponent({
    name: 'IconUser',
    setup: () => () =>
        h('svg', baseSvg('w-5 h-5'), [
            h('path', {
                strokeLinecap: 'round',
                strokeLinejoin: 'round',
                strokeWidth: 1.7,
                d: 'M16 7a4 4 0 11-8 0 4 4 0 018 0z',
            }),
            h('path', {
                strokeLinecap: 'round',
                strokeLinejoin: 'round',
                strokeWidth: 1.7,
                d: 'M5 21a7 7 0 0114 0',
            }),
        ]),
});

const IconLock = defineComponent({
    name: 'IconLock',
    setup: () => () =>
        h('svg', baseSvg('w-5 h-5'), [
            h('rect', {
                x: 4,
                y: 10,
                width: 16,
                height: 11,
                rx: 2,
                stroke: 'currentColor',
                strokeWidth: 1.7,
            }),
            h('path', {
                strokeLinecap: 'round',
                strokeLinejoin: 'round',
                strokeWidth: 1.7,
                d: 'M8 10V7a4 4 0 018 0v3',
            }),
        ]),
});

const IconEye = defineComponent({
    name: 'IconEye',
    setup: () => () =>
        h('svg', baseSvg('w-5 h-5'), [
            h('path', {
                strokeLinecap: 'round',
                strokeLinejoin: 'round',
                strokeWidth: 1.7,
                d: 'M15 12a3 3 0 11-6 0 3 3 0 016 0z',
            }),
            h('path', {
                strokeLinecap: 'round',
                strokeLinejoin: 'round',
                strokeWidth: 1.7,
                d: 'M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z',
            }),
        ]),
});

const IconEyeOff = defineComponent({
    name: 'IconEyeOff',
    setup: () => () =>
        h('svg', baseSvg('w-5 h-5'), [
            h('path', {
                strokeLinecap: 'round',
                strokeLinejoin: 'round',
                strokeWidth: 1.7,
                d: 'M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029',
            }),
            h('path', {
                strokeLinecap: 'round',
                strokeLinejoin: 'round',
                strokeWidth: 1.7,
                d: 'M9.878 9.878a3 3 0 104.243 4.243',
            }),
            h('path', {
                strokeLinecap: 'round',
                strokeLinejoin: 'round',
                strokeWidth: 1.7,
                d: 'M3 3l18 18',
            }),
            h('path', {
                strokeLinecap: 'round',
                strokeLinejoin: 'round',
                strokeWidth: 1.7,
                d: 'M14.12 14.12a3 3 0 01-4.24-4.24',
            }),
            h('path', {
                strokeLinecap: 'round',
                strokeLinejoin: 'round',
                strokeWidth: 1.7,
                d: 'M6.53 6.53A10.04 10.04 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.02 10.02 0 01-4.043 5.25',
            }),
        ]),
});

/* ================================================== */
/* MagneticButton */
/* ================================================== */
const MagneticButton = defineComponent({
    name: 'MagneticButton',
    inheritAttrs: false,
    props: {
        disabled: { type: Boolean, default: false },
        type: { type: String, default: 'button' },
    },
    emits: ['click'],
    setup(props, { slots, emit, attrs }) {
        const x = useMotionValue(0);
        const y = useMotionValue(0);
        const springX = useSpring(x, { stiffness: 200, damping: 15 });
        const springY = useSpring(y, { stiffness: 200, damping: 15 });

        const handleMouseMove = (e) => {
            if (props.disabled) return;
            const rect = e.currentTarget.getBoundingClientRect();
            const cx = rect.left + rect.width / 2;
            const cy = rect.top + rect.height / 2;
            x.set((e.clientX - cx) * 0.15);
            y.set((e.clientY - cy) * 0.15);
        };
        const handleMouseLeave = () => {
            x.set(0);
            y.set(0);
        };

        return () =>
            h(
                motion.button,
                {
                    ...attrs,
                    type: props.type,
                    disabled: props.disabled,
                    onClick: (e) => emit('click', e),
                    onMousemove: handleMouseMove,
                    onMouseleave: handleMouseLeave,
                    style: { ...attrs.style, x: springX, y: springY },
                    whileHover: { scale: props.disabled ? 1 : 1.02 },
                    whileTap: { scale: props.disabled ? 1 : 0.97 },
                },
                () => slots.default?.(),
            );
    },
});

/* ================================================== */
/* FloatingInput */
/* ================================================== */
const FloatingInput = defineComponent({
    name: 'FloatingInput',
    props: {
        type: { type: String, required: true },
        label: { type: String, required: true },
        icon: { type: [Object, Function], required: true },
        modelValue: { type: String, default: '' },
    },
    emits: ['update:modelValue'],
    setup(props, { emit }) {
        const focused = ref(false);
        const showPw = ref(false);

        const isPassword = () => props.type === 'password';
        const actualType = () =>
            isPassword() ? (showPw.value ? 'text' : 'password') : props.type;
        const isActive = () =>
            focused.value || (props.modelValue?.length || 0) > 0;

        return () =>
            h('div', { class: 'relative group w-full' }, [
                h(
                    'div',
                    {
                        class: 'absolute left-3 top-1/2 -translate-y-1/2 w-9 h-9 rounded-xl flex items-center justify-center text-[var(--text-secondary)] bg-[var(--input-bg)] border border-[var(--input-border)] group-focus-within:text-green-400 group-focus-within:bg-green-400/10 group-focus-within:border-green-400/20 transition-all duration-200 pointer-events-none z-10',
                    },
                    [h(props.icon)],
                ),

                h('input', {
                    type: actualType(),
                    value: props.modelValue,
                    onInput: (e) => emit('update:modelValue', e.target.value),
                    onFocus: () => {
                        focused.value = true;
                    },
                    onBlur: () => {
                        focused.value = false;
                    },
                    class: 'w-full h-[58px] bg-[var(--input-bg)] border border-[var(--input-border)] rounded-2xl pl-14 pr-12 text-sm text-[var(--text-primary)] outline-none backdrop-blur-sm hover:bg-[var(--input-hover-bg)] focus:border-green-400/80 focus:bg-[var(--input-focus-bg)] focus:ring-2 focus:ring-green-400/15 transition-all duration-200',
                }),

                h(
                    'label',
                    {
                        class: `absolute left-14 cursor-text pointer-events-none transition-all duration-200 ${
                            isActive()
                                ? '-top-2.5 text-[11px] bg-[var(--label-active-bg)] px-2 text-green-400 font-bold tracking-wider rounded-full border border-green-400/30'
                                : 'top-1/2 -translate-y-1/2 text-sm text-[var(--text-secondary)]'
                        }`,
                    },
                    props.label,
                ),

                isPassword()
                    ? h(
                          'button',
                          {
                              type: 'button',
                              onClick: () => {
                                  showPw.value = !showPw.value;
                              },
                              class: 'absolute inset-y-0 right-0 pr-4 flex items-center text-[var(--text-secondary)] hover:text-green-400 transition-colors',
                          },
                          [showPw.value ? h(IconEyeOff) : h(IconEye)],
                      )
                    : null,
            ]);
    },
});

/* ================================================== */
/* Particles Engine */
/* ================================================== */
const FIREFLY_CONFIG = [
    { size: 3.1, left: '12%', top: '18%', dur: 9, delay: 0.5 },
    { size: 2.8, left: '28%', top: '72%', dur: 11, delay: 1.2 },
    { size: 3.6, left: '43%', top: '31%', dur: 8, delay: 0.2 },
    { size: 4.2, left: '57%', top: '84%', dur: 12, delay: 1.8 },
    { size: 2.7, left: '68%', top: '15%', dur: 10, delay: 0.9 },
    { size: 3.8, left: '79%', top: '56%', dur: 13, delay: 1.5 },
    { size: 2.4, left: '91%', top: '27%', dur: 9, delay: 0.4 },
    { size: 4.0, left: '7%', top: '63%', dur: 11, delay: 1.1 },
    { size: 3.3, left: '36%', top: '93%', dur: 10, delay: 2.0 },
    { size: 2.6, left: '52%', top: '48%', dur: 12, delay: 0.7 },
    { size: 4.1, left: '84%', top: '78%', dur: 8, delay: 1.4 },
    { size: 3.0, left: '19%', top: '42%', dur: 13, delay: 0.3 },
];

const LEAF_CONFIG = [
    { left: '5%', delay: 0, dur: 14, scale: 0.68, rotate: 40 },
    { left: '23%', delay: 2, dur: 17, scale: 0.58, rotate: 120 },
    { left: '41%', delay: 4, dur: 15, scale: 0.72, rotate: 200 },
    { left: '63%', delay: 1, dur: 18, scale: 0.55, rotate: 280 },
    { left: '78%', delay: 5, dur: 16, scale: 0.74, rotate: 320 },
    { left: '92%', delay: 3, dur: 19, scale: 0.62, rotate: 160 },
];

const ParticlesEngine = defineComponent({
    name: 'ParticlesEngine',
    setup: () => () =>
        h(
            'div',
            {
                class: 'absolute inset-0 overflow-hidden pointer-events-none z-[1]',
            },
            [
                ...FIREFLY_CONFIG.map((f, i) =>
                    h(motion.div, {
                        key: `fly-${i}`,
                        class: 'absolute rounded-full bg-green-300 blur-[1px]',
                        style: {
                            left: f.left,
                            top: f.top,
                            width: `${f.size}px`,
                            height: `${f.size}px`,
                        },
                        animate: {
                            y: [0, -30, 0],
                            x: [0, 20, 0],
                            opacity: [0.2, 0.9, 0.2],
                        },
                        transition: {
                            duration: f.dur,
                            repeat: Infinity,
                            ease: 'easeInOut',
                            delay: f.delay,
                        },
                    }),
                ),
                ...LEAF_CONFIG.map((l, i) =>
                    h(
                        motion.svg,
                        {
                            key: `leaf-${i}`,
                            viewBox: '0 0 24 24',
                            fill: 'currentColor',
                            class: 'absolute text-green-400/15',
                            style: {
                                left: l.left,
                                width: `${22 * l.scale}px`,
                                height: `${22 * l.scale}px`,
                                top: '-5%',
                            },
                            animate: {
                                y: ['0vh', '105vh'],
                                x: [0, 40, -40, 0],
                                rotate: [l.rotate, l.rotate + 360],
                                opacity: [0, 0.8, 0],
                            },
                            transition: {
                                duration: l.dur,
                                repeat: Infinity,
                                ease: 'linear',
                                delay: l.delay,
                            },
                        },
                        [
                            h('path', {
                                d: 'M17.5 22c.8 0 1.5-.7 1.5-1.5 0-3.6-1.5-7-4.2-9.6C12.1 8.2 8.7 6.8 5 6.8c-.8 0-1.5.7-1.5 1.5 0 3.6 1.5 7 4.2 9.6 2.7 2.7 6.1 4.1 9.8 4.1zm-8.8-11c1.9-.3 3.8.3 5.3 1.8 1.5 1.5 2.1 3.4 1.8 5.3-2.3-1.4-4.5-3.6-5.9-5.9-1.3-2.3-1.6-4.9-.8-7.2.3-.9.7-1.7 1.3-2.5-1.9 1-3.6 2.5-4.7 4.4-1.2 2-1.7 4.4-1.4 6.8 2.2-.8 4.1-2 5.4-3.7z',
                            }),
                        ],
                    ),
                ),
            ],
        ),
});

/* ================================================== */
/* STATE (login saja — tanpa mode signup) */
/* ================================================== */
const quoteIdx = ref(0);
const theme = ref('dark');

const formState = reactive({
    username: '',
    password: '',
    remember: false,
});

const errorMessage = ref(null);
const isLoading = ref(false);

/* ================================================== */
/* THEME */
/* ================================================== */
onMounted(() => {
    const savedTheme = localStorage.getItem('site-theme');
    if (savedTheme === 'white') {
        theme.value = 'light';
        document.body.classList.add('light-theme');
    } else {
        theme.value = 'dark';
        document.body.classList.remove('light-theme');
    }
});

const toggleTheme = () => {
    const newTheme = theme.value === 'dark' ? 'light' : 'dark';
    theme.value = newTheme;
    if (newTheme === 'light') {
        document.body.classList.add('light-theme');
        localStorage.setItem('site-theme', 'white');
    } else {
        document.body.classList.remove('light-theme');
        localStorage.setItem('site-theme', 'dark');
    }
};

/* ================================================== */
/* MOUSE FOLLOW */
/* ================================================== */
const mx = useMotionValue(50);
const my = useMotionValue(50);
const smx = useSpring(mx, { stiffness: 60, damping: 25 });
const smy = useSpring(my, { stiffness: 60, damping: 25 });
const blobX = useTransform(smx, [0, 100], [-60, 60]);
const blobY = useTransform(smy, [0, 100], [-40, 40]);

const spotlightBg = ref(
    'radial-gradient(700px circle at 50% 50%, rgba(74,222,128,0.12), transparent 70%)',
);

const handleGlobalMouseMove = (e) => {
    const xPct = (e.clientX / window.innerWidth) * 100;
    const yPct = (e.clientY / window.innerHeight) * 100;
    mx.set(xPct);
    my.set(yPct);
    spotlightBg.value = `radial-gradient(700px circle at ${xPct}% ${yPct}%, rgba(74,222,128,0.12), transparent 70%)`;
};

/* ================================================== */
/* QUOTE ROTATION */
/* ================================================== */
let quoteTimer = null;
onMounted(() => {
    quoteTimer = setInterval(() => {
        quoteIdx.value = (quoteIdx.value + 1) % QUOTES.length;
    }, 4000);
});
onUnmounted(() => {
    if (quoteTimer) clearInterval(quoteTimer);
});

/* ================================================== */
/* FORM HANDLER (login username → role dari tb_user) */
/* ================================================== */
const handleInput = (key, val) => {
    formState[key] = val;
};

/* ================================================== */
/* THEME VARS */
/* ================================================== */
const themeVars = {
    dark: {
        '--bg-primary': '#020402',
        '--bg-panel': '#050505',
        '--text-primary': '#ffffff',
        '--text-secondary': '#a3a3a3',
        '--border': 'rgba(255,255,255,0.1)',
        '--input-bg': 'rgba(255,255,255,0.035)',
        '--input-border': 'rgba(255,255,255,0.1)',
        '--input-hover-bg': 'rgba(255,255,255,0.05)',
        '--input-focus-bg': 'rgba(255,255,255,0.07)',
        '--label-active-bg': '#050505',
        '--wave-color': '#050505',
        '--green-text': '#4ade80',
    },
    light: {
        '--bg-primary': '#f0fdf4',
        '--bg-panel': '#ffffff',
        '--text-primary': '#111827',
        '--text-secondary': '#4b5563',
        '--border': 'rgba(0,0,0,0.1)',
        '--input-bg': 'rgba(0,0,0,0.03)',
        '--input-border': 'rgba(0,0,0,0.15)',
        '--input-hover-bg': 'rgba(0,0,0,0.05)',
        '--input-focus-bg': 'rgba(0,0,0,0.07)',
        '--label-active-bg': '#ffffff',
        '--wave-color': '#ffffff',
        '--green-text': '#059669',
    },
};

/* ================================================== */
/* AUTH HANDLER — login nyata via backend (tb_user). */
/* Session dibuat server; peran diambil dari /api/auth/me agar sidebar, */
/* matriks akses, dan RoleDenied yang sudah ada tetap bekerja. */
/* ================================================== */
const handleLogin = async (e) => {
    e.preventDefault();
    isLoading.value = true;
    errorMessage.value = null;

    const uname = formState.username.trim();
    if (!uname || !formState.password.trim()) {
        errorMessage.value = 'Username dan password wajib diisi.';
        isLoading.value = false;
        return;
    }

    try {
        await apiLogin(uname, formState.password, formState.remember);
        const me = await apiMe();
        const u = me.data;
        loginAsMockUser({
            id_user: u.id_user,
            username: u.username,
            nama_lengkap: u.nama_lengkap,
            role: u.role ?? 'kasir',
        });
        inertiaRouter.visit('/dashboard');
    } catch (err) {
        errorMessage.value =
            err?.errors?.username?.[0] ||
            err?.message ||
            'Login gagal. Periksa koneksi ke server.';
    } finally {
        isLoading.value = false;
    }
};
</script>