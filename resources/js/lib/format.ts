export const formatRupiah = (n: number | string): string => {
    const v = typeof n === 'string' ? parseFloat(n) || 0 : n || 0;
    return 'Rp ' + Math.round(v).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
};

export const formatRupiahShort = (n: number): string => {
    if (Math.abs(n) >= 1_000_000) return 'Rp ' + (n / 1_000_000).toFixed(1).replace('.', ',') + ' Jt';
    if (Math.abs(n) >= 1_000) return 'Rp ' + Math.round(n / 1_000) + 'rb';
    return formatRupiah(n);
};

export const formatDate = (iso: string | Date): string => {
    const d = typeof iso === 'string' ? new Date(iso) : iso;
    return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
};

export const formatDateTime = (iso: string | Date): string => {
    const d = typeof iso === 'string' ? new Date(iso) : iso;
    return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) +
        ' ' + d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
};

/** YYYY-MM-DD key untuk filter harian */
export const dayKey = (iso: string | Date): string => {
    const d = typeof iso === 'string' ? new Date(iso) : iso;
    return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
};

export const todayKey = (): string => dayKey(new Date());

export const uid = (p = 'ID'): string =>
    `${p}-${Date.now().toString(36).toUpperCase()}${Math.floor(Math.random() * 99)}`;
