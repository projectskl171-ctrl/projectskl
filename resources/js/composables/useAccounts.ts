/* =====================================================================
   RECENT ACCOUNTS — daftar akun yang pernah login di browser ini.
   Dipakai modal Logout & Beralih Akun ala ChatGPT (maks 3 tersimpan,
   ditampilkan: akun saat ini di atas + maks 2 akun lain).
   Hanya menyimpan identitas (tanpa password); beralih akun tetap
   lewat logout + prefill username di AuthPage.
   ===================================================================== */
import { computed, reactive } from 'vue';

export interface RecentAccount {
    id_user: number;
    username: string;
    nama_lengkap: string;
    role: string;
    inisial: string;
    lastLogin: string;
}

const LS_KEY = 'kasirku_accounts_v1';
const MAX = 3;

const state = reactive<{ list: RecentAccount[] }>({ list: [] });
let booted = false;

function boot() {
    if (booted) return;
    booted = true;
    try {
        const raw = localStorage.getItem(LS_KEY);
        if (raw) {
            const parsed = JSON.parse(raw);
            if (Array.isArray(parsed)) state.list = parsed.filter((a) => a && a.username).slice(0, MAX);
        }
    } catch { /* abaikan */ }
}

function persist() {
    try {
        localStorage.setItem(LS_KEY, JSON.stringify(state.list.slice(0, MAX)));
    } catch { /* abaikan */ }
}

function inisialOf(nama: string): string {
    return (nama || '?').split(' ').map((w) => w[0]).filter(Boolean).slice(0, 2).join('').toUpperCase() || '?';
}

/** Catat akun yang berhasil login (dipanggil tiap login/sync sukses). */
export function recordLogin(u: { id_user: number; username: string; nama_lengkap: string; role: string }) {
    boot();
    const item: RecentAccount = {
        id_user: u.id_user,
        username: u.username,
        nama_lengkap: u.nama_lengkap,
        role: u.role,
        inisial: inisialOf(u.nama_lengkap),
        lastLogin: new Date().toISOString(),
    };
    state.list = [item, ...state.list.filter((a) => Number(a.id_user) !== Number(u.id_user))].slice(0, MAX);
    persist();
}

/** Akun lain selain shownId (maks 2) untuk modal logout/switch. */
export function otherAccounts(shownId?: number | null): RecentAccount[] {
    boot();
    return state.list.filter((a) => Number(a.id_user) !== Number(shownId)).slice(0, 2);
}

export function useAccounts() {
    boot();
    const list = computed(() => state.list);
    return { list, recordLogin, otherAccounts };
}
