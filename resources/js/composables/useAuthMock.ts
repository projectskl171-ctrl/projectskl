/* =====================================================================
   AUTH MOCK — simulasi login 3 peran, FRONTEND ONLY (tanpa backend).
   Teman backend nanti mengganti ini dengan session/sanctum asli:
     - loginAs()  -> POST /login (Laravel Auth, session +_tb_user_)
     - user/role  -> usePage().props.auth.user (dari HandleInertiaRequests)
     - logout()   -> POST /logout
   Selama BE belum jadi, peran disimpan di localStorage 'kasirku_role_v1'.
   ===================================================================== */
import { computed, reactive } from 'vue';
import { apiFetch } from '@/lib/api';
import { recordLogin } from '@/composables/useAccounts';

export type MockRole = 'kasir' | 'admin' | 'super admin';

export interface MockUser {
    id_user: number;
    username: string;
    nama_lengkap: string;
    role: MockRole;
    inisial: string;
    /** id_sekolah dari session server (/api/auth/me). Dipakai scope laporan & data. */
    id_sekolah?: number | null;
}

/* Akun fallback offline — mirror tb_user + roles (superadmin global tanpa sekolah) */
const ACCOUNTS: Record<MockRole, MockUser> = {
    kasir: { id_user: 3, username: 'smkn1_kasir01', nama_lengkap: 'Dedi Kurniawan', role: 'kasir', inisial: 'DK', id_sekolah: 1 },
    admin: { id_user: 2, username: 'smkn1_admin01', nama_lengkap: 'Sari Puspita', role: 'admin', inisial: 'SP', id_sekolah: 1 },
    'super admin': { id_user: 1, username: 'superadmin', nama_lengkap: 'Administrator', role: 'super admin', inisial: 'AD', id_sekolah: null },
};

export const ROLE_LABEL: Record<MockRole, string> = {
    kasir: 'Kasir',
    admin: 'Admin',
    'super admin': 'Super Admin',
};

export const ROLE_SCOPE: Record<MockRole, string> = {
    kasir: 'mari lakukan transaksi untuk hari ini!',
    admin: 'kelola operasional harian dan kasir sekolahmu!',
    'super admin': 'kontrol jaringan, sekolah, dan akses',
};

export const ROLE_COLOR: Record<MockRole, string> = {
    kasir: '#3b82f6',
    admin: '#8b5cf6',
    'super admin': '#10b981',
};

/* Matriks akses menu per peran (key = href sidebar).
   kasir: Dashboard, Transaksi, Riwayat Transaksi, Pelanggan, Notifikasi, Settings.
   admin: Dashboard, Produk, Pembelian, Supplier, User, Laporan, Notifikasi, Settings.
   super admin: Dashboard, Sekolah, User, Laporan, Notifikasi, Settings. */
const MATRIX: Record<MockRole, string[]> = {
    kasir: ['/dashboard', '/transaksi', '/riwayat-transaksi', '/pelanggan', '/notifikasi', '/settings'],
    admin: ['/dashboard', '/produk', '/pembelian', '/supplier', '/user', '/laporan', '/notifikasi', '/settings'],
    'super admin': ['/dashboard', '/sekolah', '/user', '/laporan', '/notifikasi', '/settings'],
};

/* Tidak ada halaman read-only lagi: kasir boleh CRUD pelanggan.
   (admin di /user tetap dibatasi hanya kelola akun kasir — lihat User.vue) */
const READ_ONLY: Partial<Record<string, MockRole[]>> = {};

const LS_KEY = 'kasirku_role_v1';

const state = reactive<{ user: MockUser | null }>({ user: null });
let booted = false;

function boot() {
    if (booted) return;
    booted = true;
    try {
        const raw = localStorage.getItem(LS_KEY);
        if (raw) {
            const parsed = JSON.parse(raw) as MockUser;
            if (parsed && ACCOUNTS[parsed.role as MockRole]) state.user = parsed;
        }
    } catch { /* abaikan */ }
    // default demo: super admin biar semua halaman bisa dicek langsung
    if (!state.user) state.user = ACCOUNTS['super admin'];
}

export function loginAs(role: MockRole): MockUser {
    boot();
    state.user = ACCOUNTS[role];
    try {
        localStorage.setItem(LS_KEY, JSON.stringify(state.user));
    } catch { /* abaikan */ }
    return state.user;
}

export function logoutMock() {
    state.user = null;
    try {
        localStorage.removeItem(LS_KEY);
    } catch { /* abaikan */ }
}

/** Login sebagai user tb_user spesifik (dipakai halaman login username).
    Backend nanti: POST /login lalu refresh usePage().props.auth.user. */
export function loginAsMockUser(u: { id_user: number; username: string; nama_lengkap: string; role: MockRole; id_sekolah?: number | null }): MockUser {
    boot();
    const inisial = u.nama_lengkap.split(' ').map((w) => w[0]).filter(Boolean).slice(0, 2).join('').toUpperCase() || '?';
    // Pertahankan id_sekolah lama hanya bila login baru TIDAK membawanya sama
    // sekali (undefined). Null eksplisit (superadmin global) wajib menang.
    const prevSchool = state.user && Number(state.user.id_user) === Number(u.id_user) ? state.user.id_sekolah : undefined;
    const id_sekolah = u.id_sekolah !== undefined ? u.id_sekolah : (prevSchool ?? null);
    state.user = { id_user: u.id_user, username: u.username, nama_lengkap: u.nama_lengkap, role: u.role, inisial, id_sekolah };
    try {
        localStorage.setItem(LS_KEY, JSON.stringify(state.user));
    } catch { /* abaikan */ }
    // Catat ke daftar akun perangkat (logout/switch ala ChatGPT, tanpa password).
    try {
        recordLogin({ id_user: u.id_user, username: u.username, nama_lengkap: u.nama_lengkap, role: u.role });
    } catch { /* abaikan */ }
    return state.user;
}

/**
 * Sinkron state peran frontend dengan SESSION server (/api/auth/me).
 * Ini sumber kebenaran: memperbaiki localStorage basi (mis. peran lama
 * dari login sebelumnya / tab lain) yang membuat sidebar menampilkan menu
 * yang sesungguhnya ditolak backend (mental ke dashboard).
 * @returns 'ok' | 'unauthorized' | 'offline'
 */
export async function syncAuthFromServer(): Promise<'ok' | 'unauthorized' | 'offline'> {
    boot();
    try {
        const me = await apiFetch<{ data: any }>('/api/auth/me');
        const u = me?.data;
        if (!u || !u.id_user || !ACCOUNTS[u.role as MockRole]) return 'unauthorized';
        loginAsMockUser({
            id_user: u.id_user,
            username: u.username,
            nama_lengkap: u.nama_lengkap,
            role: u.role as MockRole,
            id_sekolah: u.id_sekolah ?? u.sekolah?.id_sekolah ?? null,
        });
        return 'ok';
    } catch (err: any) {
        if (err?.status === 401) {
            logoutMock();
            return 'unauthorized';
        }
        return 'offline'; // jaringan mati: pertahankan state lama, jangan logout paksa
    }
}

/** Update nama & username user yang sedang login (dipakai Settings → Profil).
    Backend nanti: PATCH /api/users/{id} lalu refresh usePage().props.auth.user. */
export function updateMockProfile(nama: string, username: string) {
    boot();
    if (!state.user) return;
    const inisial = nama.split(' ').map((w) => w[0]).filter(Boolean).slice(0, 2).join('').toUpperCase() || '?';
    state.user = { ...state.user, nama_lengkap: nama, username, inisial };
    try {
        localStorage.setItem(LS_KEY, JSON.stringify(state.user));
    } catch { /* abaikan */ }
}

export function useAuthMock() {
    boot();
    const user = computed(() => state.user);
    const role = computed<MockRole>(() => state.user?.role ?? 'kasir');
    const roleLabel = computed(() => (state.user ? ROLE_LABEL[state.user.role] : '—'));
    const can = (href: string): boolean => MATRIX[role.value]?.includes(href) ?? false;
    const canEdit = (href: string): boolean => {
        if (!can(href)) return false;
        return !(READ_ONLY[href]?.includes(role.value));
    };
    return { user, role, roleLabel, can, canEdit, loginAs, logoutMock };
}
