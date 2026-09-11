<template>
    <Teleport to="body">
        <main
            :style="themeVars[theme]"
            class="fixed inset-0 z-[9999] overflow-hidden bg-[var(--bg-primary)] font-sans text-[var(--text-primary)]"
            @mousemove="handleGlobalMouseMove"
        >
            <div class="absolute top-6 left-6 z-30">
                <MagneticButton
                    type="button"
                    class="group flex items-center gap-2 rounded-full border-[var(--border)] bg-black/20 px-4 py-2.5 text-xs font-bold tracking-wide text-[var(--text-secondary)] backdrop-blur-md transition-all duration-200 hover:border-green-400/30 hover:bg-green-400/10 hover:text-[var(--text-primary)]"
                    @click="router.push('/')"
                >
                    <svg
                        class="h-4 w-4 transition-transform duration-200 group-hover:-translate-x-1"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M15 19l-7-7 7-7"
                        />
                    </svg>
                    Back to Home
                </MagneticButton>
            </div>

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
                    <span>GREEN</span>
                    <span class="mt-[-8vw]">ECO</span>
                </motion.div>
            </div>

            <ParticlesEngine />

            <div class="absolute inset-0 z-10 flex">
                <motion.div
                    :initial="false"
                    :animate="{ left: greenPanelLeft }"
                    :transition="{ duration: 0 }"
                    class="absolute top-0 flex h-full w-[50vw] items-center justify-center p-12"
                >
                    <AnimatePresence mode="wait">
                        <motion.div
                            v-if="!isSweeping"
                            :key="activeForm"
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
                                    isSignup
                                        ? 'Already a member?'
                                        : 'New to the movement?'
                                }}
                            </div>

                            <h3
                                class="text-5xl leading-[1.05] font-black tracking-tighter whitespace-pre-line !text-white text-[var(--text-primary)] drop-shadow-[0_4px_30px_rgba(0,0,0,0.5)] lg:text-6xl"
                            >
                                {{
                                    isSignup
                                        ? 'Welcome\nBack!'
                                        : 'Join The\nFuture'
                                }}
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

                            <MagneticButton
                                class="group mt-4 flex items-center gap-3 rounded-full border border-green-400/50 bg-emerald-500/20 px-12 py-4 text-sm font-black tracking-widest !text-white text-[var(--text-primary)] uppercase shadow-[0_0_30px_rgba(34,197,94,0.25)] backdrop-blur-md transition-all duration-300 hover:bg-green-400 hover:text-black"
                                @click="
                                    handleSwitchMode(
                                        isSignup ? 'login' : 'signup',
                                    )
                                "
                            >
                                {{ isSignup ? 'Log In Now' : 'Create Account' }}
                                <svg
                                    class="h-4 w-4 transition-transform group-hover:translate-x-1"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2.5"
                                        d="M14 5l7 7m0 0l-7 7m7-7H3"
                                    />
                                </svg>
                            </MagneticButton>
                        </motion.div>
                    </AnimatePresence>
                </motion.div>
                <motion.div
                    :initial="false"
                    :animate="{ left: blackPanelLeft }"
                    :transition="{ duration: 0 }"
                    class="absolute top-0 z-20 h-full w-[50vw] bg-[var(--bg-panel)]"
                >
                    <div
                        class="pointer-events-none absolute top-0 h-full w-[100px]"
                        :class="
                            isSignup
                                ? '-right-[99px]'
                                : '-left-[99px] scale-x-[-1]'
                        "
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
                                v-if="!isSweeping"
                                :key="activeForm"
                                :initial="{
                                    opacity: 0,
                                    x: isSignup ? 20 : -20,
                                }"
                                :animate="{ opacity: 1, x: 0 }"
                                :exit="{ opacity: 0, x: isSignup ? -20 : 20 }"
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
                                        {{
                                            isSignup
                                                ? 'Get Started'
                                                : 'Welcome Back'
                                        }}
                                        <div
                                            class="h-2.5 w-2.5 animate-pulse rounded-full bg-green-400 shadow-[0_0_10px_#4ade80]"
                                        />
                                    </h2>
                                    <p
                                        class="mt-2 text-sm font-medium text-[var(--text-secondary)]"
                                    >
                                        {{
                                            isSignup
                                                ? 'Start your eco-journey and make an impact.'
                                                : 'Sign in to continue your green mission.'
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
                                    v-if="isSignup"
                                    class="flex flex-col gap-4"
                                    @submit.prevent="handleSignup"
                                >
                                    <FloatingInput
                                        v-model="formState.name"
                                        type="text"
                                        label="Nama Lengkap"
                                        :icon="IconUser"
                                    />
                                    <FloatingInput
                                        v-model="formState.username"
                                        type="text"
                                        label="Username"
                                        :icon="IconUser"
                                    />
                                    <FloatingInput
                                        v-model="formState.email"
                                        type="email"
                                        label="Email Address"
                                        :icon="IconMail"
                                    />
                                    <FloatingInput
                                        v-model="formState.password"
                                        type="password"
                                        label="Create Password"
                                        :icon="IconLock"
                                    />

                                    <div class="mt-1 grid grid-cols-2 gap-3">
                                        <button
                                            type="button"
                                            class="group relative flex h-[92px] flex-col items-center justify-center gap-2 overflow-hidden rounded-2xl border transition-all duration-200"
                                            :class="
                                                role === 'user'
                                                    ? 'border-green-400/80 bg-green-500/10 text-green-300 shadow-[0_0_25px_rgba(34,197,94,0.12)]'
                                                    : 'border-[var(--border)] bg-[var(--input-bg)] text-[var(--text-secondary)] hover:bg-[var(--input-hover-bg)] hover:text-[var(--text-primary)]'
                                            "
                                            @click="role = 'user'"
                                        >
                                            <div
                                                class="flex h-10 w-10 items-center justify-center rounded-xl transition-all"
                                                :class="
                                                    role === 'user'
                                                        ? 'bg-green-400/15 text-green-400'
                                                        : 'bg-[var(--input-bg)] text-[var(--text-secondary)] group-hover:text-[var(--text-primary)]'
                                                "
                                            >
                                                <IconUser />
                                            </div>
                                            <span
                                                class="text-[11px] font-black tracking-[0.18em] uppercase"
                                                >User</span
                                            >
                                            <motion.div
                                                v-if="role === 'user'"
                                                layoutId="roleActive"
                                                class="absolute right-5 bottom-0 left-5 h-[2px] rounded-full bg-green-400"
                                            />
                                        </button>

                                        <button
                                            type="button"
                                            class="group relative flex h-[92px] flex-col items-center justify-center gap-2 overflow-hidden rounded-2xl border transition-all duration-200"
                                            :class="
                                                role === 'umkm'
                                                    ? 'border-green-400/80 bg-green-500/10 text-green-300 shadow-[0_0_25px_rgba(34,197,94,0.12)]'
                                                    : 'border-[var(--border)] bg-[var(--input-bg)] text-[var(--text-secondary)] hover:bg-[var(--input-hover-bg)] hover:text-[var(--text-primary)]'
                                            "
                                            @click="role = 'umkm'"
                                        >
                                            <div
                                                class="flex h-10 w-10 items-center justify-center rounded-xl transition-all"
                                                :class="
                                                    role === 'umkm'
                                                        ? 'bg-green-400/15 text-green-400'
                                                        : 'bg-[var(--input-bg)] text-[var(--text-secondary)] group-hover:text-[var(--text-primary)]'
                                                "
                                            >
                                                <IconStore />
                                            </div>
                                            <span
                                                class="text-[11px] font-black tracking-[0.18em] uppercase"
                                                >UMKM</span
                                            >
                                            <motion.div
                                                v-if="role === 'umkm'"
                                                layoutId="roleActive"
                                                class="absolute right-5 bottom-0 left-5 h-[2px] rounded-full bg-green-400"
                                            />
                                        </button>
                                    </div>

                                    <label
                                        class="group mt-1 flex cursor-pointer items-start gap-3"
                                    >
                                        <div
                                            class="relative mt-0.5 flex h-5 w-5 items-center justify-center rounded-md border border-[var(--border)] bg-[var(--input-bg)] transition-colors group-hover:border-green-400/50"
                                        >
                                            <input
                                                type="checkbox"
                                                class="absolute cursor-pointer opacity-0"
                                                :checked="formState.remember"
                                                @change="
                                                    handleInput(
                                                        'remember',
                                                        $event.target.checked,
                                                    )
                                                "
                                            />
                                            <motion.svg
                                                v-if="formState.remember"
                                                :initial="{ scale: 0 }"
                                                :animate="{ scale: 1 }"
                                                class="h-3.5 w-3.5 text-green-400"
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
                                            </motion.svg>
                                        </div>
                                        <p
                                            class="text-xs leading-relaxed text-[var(--text-secondary)]"
                                        >
                                            I agree to the
                                            <span
                                                class="text-green-400 hover:underline"
                                                >Terms of Service</span
                                            >
                                            and
                                            <span
                                                class="text-green-400 hover:underline"
                                                >Privacy Policy</span
                                            >.
                                        </p>
                                    </label>

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
                                                    : 'Create Account'
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
                                                    d="M17 8l4 4m0 0l-4 4m4-4H3"
                                                />
                                            </svg>
                                        </span>
                                    </MagneticButton>
                                </form>

                                <form
                                    v-else
                                    class="flex flex-col gap-5"
                                    @submit.prevent="handleLogin"
                                >
                                    <FloatingInput
                                        v-model="formState.email"
                                        type="email"
                                        label="Email"
                                        :icon="IconMail"
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
                                                    : 'Sign In Securely'
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
                                </form>
                            </motion.div>
                        </AnimatePresence>
                    </div>
                </motion.div>
            </div>
            <div
                class="pointer-events-none absolute inset-0 z-50 overflow-hidden"
            >
                <AnimatePresence>
                    <motion.div
                        v-if="isSweeping"
                        :initial="{
                            x: sweepDir === 'ltr' ? '-100vw' : '100vw',
                        }"
                        :animate="{ x: '0vw' }"
                        :exit="{
                            x: sweepDir === 'ltr' ? '100vw' : '-100vw',
                        }"
                        :transition="{ duration: 0.35, ease: EASE_FAST }"
                        class="absolute top-0 flex h-full w-full items-center justify-center overflow-visible bg-gradient-to-br from-emerald-500 via-green-500 to-emerald-700 shadow-[0_0_100px_rgba(34,197,94,0.8)]"
                    >
                        <div
                            class="absolute inset-0 bg-white/10 backdrop-blur-md"
                        />
                        <div
                            class="pointer-events-none absolute top-0 h-full w-[120px]"
                            :class="
                                sweepDir === 'ltr'
                                    ? '-right-[119px]'
                                    : '-left-[119px] scale-x-[-1]'
                            "
                        >
                            <svg
                                class="h-full w-full overflow-visible"
                                viewBox="0 0 100 1000"
                                preserveAspectRatio="none"
                            >
                                <path fill="#22c55e" :d="SWEEP_WAVE" />
                            </svg>
                        </div>
                    </motion.div>
                </AnimatePresence>
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
    computed,
    onMounted,
    onUnmounted,
    watch,
    defineComponent,
    h,
} from 'vue';
import { router as inertiaRouter, usePage } from '@inertiajs/vue3';
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
const SWEEP_WAVE =
    'M0,-200 L80,-200 C150,200 -20,500 90,800 C160,1000 30,1100 70,1200 L0,1200 Z';
const EASE_FAST = [0.16, 1, 0.3, 1];

const QUOTES = [
    {
        text: 'Supporting local UMKM creates a sustainable future for our community.',
        author: 'Eco Daily',
    },
    {
        text: 'Small green steps lead to giant leaps for our planet.',
        author: 'Green Earth',
    },
    {
        text: 'Every eco-friendly purchase is a vote for the world you want.',
        author: 'Sustainability Now',
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

const IconMail = defineComponent({
    name: 'IconMail',
    setup: () => () =>
        h('svg', baseSvg('w-5 h-5'), [
            h('path', {
                strokeLinecap: 'round',
                strokeLinejoin: 'round',
                strokeWidth: 1.7,
                d: 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8',
            }),
            h('rect', {
                x: 3,
                y: 5,
                width: 18,
                height: 14,
                rx: 2,
                stroke: 'currentColor',
                strokeWidth: 1.7,
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

const IconStore = defineComponent({
    name: 'IconStore',
    setup: () => () =>
        h('svg', baseSvg('w-6 h-6'), [
            h('path', {
                strokeLinecap: 'round',
                strokeLinejoin: 'round',
                strokeWidth: 1.7,
                d: 'M3 10.5L5 4h14l2 6.5',
            }),
            h('path', {
                strokeLinecap: 'round',
                strokeLinejoin: 'round',
                strokeWidth: 1.7,
                d: 'M4 10v10h16V10',
            }),
            h('path', {
                strokeLinecap: 'round',
                strokeLinejoin: 'round',
                strokeWidth: 1.7,
                d: 'M9 20v-5h6v5',
            }),
            h('path', {
                strokeLinecap: 'round',
                strokeLinejoin: 'round',
                strokeWidth: 1.7,
                d: 'M3 10c0 1.1.9 2 2 2s2-.9 2-2c0 1.1.9 2 2 2s2-.9 2-2c0 1.1.9 2 2 2s2-.9 2-2c0 1.1.9 2 2 2s2-.9 2-2c0 1.1.9 2 2-2',
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
/* ROUTER (Inertia shim) */
/* ================================================== */
const page = usePage();

const parseLocation = (url) => {
    const [path, search = ''] = String(url || '/').split('?');
    const query = Object.fromEntries(new URLSearchParams(search));
    return { path: path || '/', query };
};

const route = computed(() => parseLocation(page.url));

const router = {
    push: (to) => inertiaRouter.visit(to),
    replace: (to) => {
        if (to && typeof to === 'object') {
            const query = new URLSearchParams(to.query || {}).toString();
            inertiaRouter.visit(query ? `${to.path}?${query}` : to.path, {
                replace: true,
            });
            return;
        }
        inertiaRouter.visit(to, { replace: true });
    },
};

const pathname = computed(() => route.value.path);
const queryMode = computed(() => route.value.query.mode);

/* ================================================== */
/* STATE */
/* ================================================== */
const activeForm = ref('signup');
const isSweeping = ref(false);
const sweepDir = ref('ltr');
const role = ref('user');
const quoteIdx = ref(0);
const theme = ref('dark');

const formState = reactive({
    name: '',
    username: '',
    email: '',
    password: '',
    remember: false,
});

const errorMessage = ref(null);
const isLoading = ref(false);

/* ================================================== */
/* API STUB */
/* ================================================== */
const supabase = {
    auth: {
        signInWithOtp: async (opts) => {
            // # API dihapus
            console.log('# signInWithOtp', opts);
            return { error: null };
        },
        signInWithPassword: async (opts) => {
            // # API dihapus
            console.log('# signInWithPassword', opts);
            return { data: { user: null }, error: null };
        },
        signOut: async () => {
            // # API dihapus
            console.log('# signOut');
        },
    },
    from: (table) => ({
        select: () => ({
            eq: () => ({
                single: async () => {
                    // # API dihapus
                    console.log('# select', table);
                    return { data: null };
                },
            }),
        }),
        insert: async (data) => {
            // # API dihapus
            console.log('# insert', table, data);
            return { error: null };
        },
    }),
};

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
/* SYNC MODE DENGAN QUERY */
/* ================================================== */
watch(
    queryMode,
    (newMode) => {
        if (newMode === 'login' && activeForm.value !== 'login')
            activeForm.value = 'login';
        else if (newMode === 'signup' && activeForm.value !== 'signup')
            activeForm.value = 'signup';
    },
    { immediate: true },
);

/* ================================================== */
/* SWITCH MODE */
/* ================================================== */
const handleSwitchMode = (targetMode) => {
    if (targetMode === activeForm.value || isSweeping.value) return;
    isSweeping.value = true;
    sweepDir.value = targetMode === 'login' ? 'ltr' : 'rtl';
    setTimeout(() => {
        activeForm.value = targetMode;
        router.replace({ path: pathname.value, query: { mode: targetMode } });
        setTimeout(() => {
            isSweeping.value = false;
        }, 350);
    }, 250);
};

/* ================================================== */
/* FORM HANDLER */
/* ================================================== */
const handleInput = (key, val) => {
    formState[key] = val;
};

const isSignup = computed(() => activeForm.value === 'signup');
const blackPanelLeft = computed(() => (isSignup.value ? '0%' : '50%'));
const greenPanelLeft = computed(() => (isSignup.value ? '50%' : '0%'));

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
/* AUTH HANDLERS */
/* ================================================== */
const handleSignup = async (e) => {
    e.preventDefault();
    isLoading.value = true;
    errorMessage.value = null;

    if (formState.name.trim().length < 1) {
        errorMessage.value = 'Nama lengkap wajib diisi.';
        isLoading.value = false;
        return;
    }
    if (
        formState.username.trim().length < 8 ||
        formState.username.trim().length > 22
    ) {
        errorMessage.value = 'Username harus 8-22 karakter.';
        isLoading.value = false;
        return;
    }
    if (!/^[a-zA-Z0-9_]+$/.test(formState.username.trim())) {
        errorMessage.value =
            'Username hanya boleh huruf, angka, dan underscore.';
        isLoading.value = false;
        return;
    }
    if (!/^\S+@\S+\.\S+$/.test(formState.email.trim())) {
        errorMessage.value = 'Format email tidak valid.';
        isLoading.value = false;
        return;
    }
    if (formState.password.length < 8) {
        errorMessage.value = 'Password minimal 8 karakter.';
        isLoading.value = false;
        return;
    }

    localStorage.setItem(
        'pending_profile',
        JSON.stringify({
            name: formState.name,
            username: formState.username,
            role: role.value,
            email: formState.email,
        }),
    );

    try {
        // # API dihapus
        const resp = await supabase.auth.signInWithOtp({
            email: formState.email,
            options: { shouldCreateUser: true },
        });
        console.log('signInWithOtp response:', resp);
        const { error } = resp;
        if (error) throw error;

        router.push(`/auth/otp?email=${encodeURIComponent(formState.email)}`);
    } catch (err) {
        errorMessage.value = err?.message || 'Terjadi kesalahan';
    } finally {
        isLoading.value = false;
    }
};

const handleLogin = async (e) => {
    e.preventDefault();
    isLoading.value = true;
    errorMessage.value = null;

    if (!formState.email.trim() || !formState.password.trim()) {
        errorMessage.value = 'Email dan password wajib diisi.';
        isLoading.value = false;
        return;
    }

    try {
        // # API dihapus
        const { data, error } = await supabase.auth.signInWithPassword({
            email: formState.email,
            password: formState.password,
        });

        if (error) throw error;

        const user = data.user;
        if (!user) throw new Error('User tidak ditemukan');

        if (!user.email_confirmed_at) {
            await supabase.auth.signOut();
            localStorage.setItem('pending_email', formState.email);
            router.push(
                `/auth/otp?email=${encodeURIComponent(formState.email)}`,
            );
            return;
        }

        let { data: profile } = await supabase
            .from('profiles')
            .select('*')
            .eq('id', user.id)
            .single();

        if (!profile) {
            const { error: insertError } = await supabase
                .from('profiles')
                .insert({
                    id: user.id,
                    username: user.user_metadata?.username || user.email,
                    name: user.user_metadata?.name || user.email,
                    role: user.user_metadata?.role || 'user',
                    is_active: true,
                    email_verified_at: new Date().toISOString(),
                });
            if (insertError) throw insertError;

            const { data: newProfile } = await supabase
                .from('profiles')
                .select('*')
                .eq('id', user.id)
                .single();
            profile = newProfile;
        }

        if (profile?.role === 'admin') router.push('/dashboard/admin');
        else if (profile?.role === 'umkm') {
            const { data: umkmData } = await supabase
                .from('umkm')
                .select('id')
                .eq('user_id', user.id)
                .single();
            if (!umkmData) router.push('/auth/profile');
            else router.push('/dashboard/umkm');
        } else {
            router.push('/dashboard/user');
        }
    } catch (err) {
        errorMessage.value = err?.message || 'Login gagal';
    } finally {
        isLoading.value = false;
    }
};
</script>