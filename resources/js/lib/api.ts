/* =====================================================================
   API CLIENT — fetch wrapper session-based (same-origin) untuk backend
   Laravel (routes/api.php). CSRF diambil dari cookie XSRF-TOKEN.
   Dipakai AuthPage (login/logout) dan dapat dipakai bertahap oleh
   halaman lain tanpa mengubah komponen/desain yang sudah ada.
   ===================================================================== */

export interface ApiError extends Error {
    status: number;
    errors?: Record<string, string[]>;
}

function xsrfToken(): string {
    const m = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]*)/);
    return m ? decodeURIComponent(m[1]) : '';
}

export async function apiFetch<T = any>(path: string, options: RequestInit = {}): Promise<T> {
    const res = await fetch(path, {
        credentials: 'same-origin',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-XSRF-TOKEN': xsrfToken(),
            ...(options.headers || {}),
        },
        ...options,
    });

    const isJson = (res.headers.get('content-type') || '').includes('json');
    const body = isJson ? await res.json().catch(() => ({})) : {};

    if (!res.ok) {
        const err = new Error(
            (body as any)?.message || `Request gagal (${res.status}).`,
        ) as ApiError;
        err.status = res.status;
        err.errors = (body as any)?.errors;
        throw err;
    }

    return body as T;
}

export const apiLogin = (username: string, password: string, remember = false) =>
    apiFetch<{ message: string; data: any }>('/api/auth/login', {
        method: 'POST',
        body: JSON.stringify({ username, password, remember }),
    });

export const apiLogout = () => apiFetch<{ message: string }>('/api/auth/logout', { method: 'POST' });

export const apiMe = () => apiFetch<{ data: any }>('/api/auth/me');
