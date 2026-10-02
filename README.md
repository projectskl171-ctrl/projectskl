# PROMPT MAKALAH NIXA — SEKALI JADI (COPY SELURUH ISI FILE INI KE CLAUDE)
# Cara pakai: blok semua teks di bawah garis, paste ke Claude, kirim.
# Setelah Claude menjawab, balas: "lanjutkan" atau minta revisi per BAB.
# Placeholder [ISI ...] wajib kamu isi manual sebelum makalah dicetak.
---
Tuliskan LAPORAN DOKUMENTASI PROYEK (makalah hasil akhir website) untuk
aplikasiku. Kerjakan langsung sampai tuntas dalam SATU jawaban, jangan
bertanya apa pun. Semua yang tidak kamu ketahui tulis sebagai placeholder
[ISI ...] agar bisa aku revisi manual setelahnya.

BAGIAN 1 — PERAN DAN MISIMU
1. Kamu adalah penulis dokumentasi teknis profesional.
2. Misimu: menghasilkan naskah makalah LENGKAP siap cetak (±20 halaman).
3. Bahasa: Bahasa Indonesia formal gaya laporan SMK.
4. Jangan pernah mengarang fitur yang tidak ada di daftar BAGIAN 4.
5. Setiap klaim angka harus memakai angka pada BAGIAN 5.

BAGIAN 2 — IDENTITAS APLIKASI (pakai persis seperti ini)
6. Nama aplikasi: NIXA.
7. Kepanjangan: "NIXA Is X-platform Accounting".
8. Jenis: Sistem Point of Sale (POS) + akuntansi ringan untuk koperasi
   dan kantin sekolah.
9. Logo: ubin gradien emerald–teal–sky dengan huruf N putih, koin emas,
   dan garis kas (jangan deskripsikan logo lain).
10. Tech stack: Laravel (PHP, backend + API berbasis session),
    Inertia.js, Vue 3 (frontend), MySQL (database), Tailwind CSS.
11. Tema: mode gelap dan mode terang, tersimpan permanen di browser.
12. Jumlah sekolah dummy: 4 (empat).
13. Nama sekolah: SMKN 1 Tasikmalaya (SCH001),
    SMKN 2 Tasikmalaya (SCH002), SMKN 3 Tasikmalaya (SCH003),
    SMKN 4 Tasikmalaya (SCH004).
14. Tiap sekolah punya minimal 10 user (2 admin + 8 kasir).
15. Semua password akun adalah: 123.
16. Tiga akun contoh: superadmin / 123 (Super Admin global tanpa sekolah),
    smkn1_admin01 / 123 (Admin SMKN 1 Tasikmalaya),
    smkn1_kasir01 / 123 (Kasir SMKN 1 Tasikmalaya).
17. Format username: smkn{N}_{role}{NN}, contoh smkn2_kasir01.
    Superadmin global berusername superadmin, superadmin02, superadmin03
    dan TIDAK memiliki keterangan sekolah apa pun (kosong).
18. Data hidup: aplikasi sudah dipakai ~14 hari (database terisi transaksi
    nyata, bukan kosong).

BAGIAN 3 — MATRIKS ROLE DAN MENU (tulis persis di BAB yang membahas role)
19. Kasir boleh membuka: Dashboard, Transaksi, Riwayat Transaksi,
    Pelanggan, Notifikasi, Settings.
20. Admin boleh membuka: Dashboard, Produk, Pembelian, Supplier, User
    (hanya mengelola akun kasir sekolahnya), Laporan, Notifikasi, Settings.
21. Super Admin boleh membuka: Dashboard, Sekolah, User (mengelola admin),
    Laporan, Notifikasi, Settings.
22. Role lain yang mencoba membuka halaman di luar wewenangnya mendapat
    403 / diarahkan ke dashboard (Role-Based Access Control).

BAGIAN 4 — DAFTAR FITUR YANG BENAR-BENAR ADA (hanya ini yang boleh dibahas)
23. Halaman login (username + password, ingat saya, prefill username saat
    beralih akun, logo NIXA + tulisan raksasa NIXA di latar).
24. Modal Logout ala ChatGPT: akun aktif di paling atas + badge AKTIF,
    maksimal 2 akun lain yang pernah login, tombol Logout merah + teks
    warning; klik akun lain = langsung beralih.
25. Menu Beralih Akun di sidebar tepat di bawah Settings, popup sama
    seperti logout tetapi TANPA tombol logout.
26. Dashboard kasir: sapaan "mari lakukan transaksi untuk hari ini!",
    kartu Transaksi Shift Saya, Omzet Saya, Rata-rata/Trx, tombol Buka
    Kasir, Data Pelanggan ("Kelola pelanggan daganganmu"), daftar
    Transaksi Shift Saya.
27. Dashboard admin & super admin: kartu Omzet Hari Ini, Transaksi Hari
    Ini, Stok Menipis, Pelanggan Terdaftar; grafik omzet 7 hari (khusus
    admin; super admin TIDAK punya grafik); tabel per-sekolah (super
    admin); panel Penjualan Terbaru; panel Kelola Akses (super admin).
28. Transaksi kasir: katalog produk realtime (stok 0 otomatis paling bawah
    + label STOK HABIS + tombol disabled), badge stok selalu tampil
    (Stok X / Sisa X / STOK HABIS), pencarian + filter kategori, klik
    tambah 1, tombol −/+, INPUT ANGKA jumlah langsung (untuk borongan,
    dijepit 1..stok), input diskon %, pilih pelanggan, pilih cara bayar
    (Tunai/QRIS/Transfer/Tempo), jenis Tunai vs Kredit.
29. Mode Tunai: ada input Nominal bayar + info Kembalian, tombol BAYAR.
30. Mode Kredit: input bayar DISEMBUNYIKAN, muncul info piutang, tombol
    SIMPAN KREDIT, tercatat sebagai belum bayar.
31. Tombol Batalkan Keranjang hanya muncul bila keranjang ada isinya.
32. Struk digital: nomor #TRX-XXXX, tanggal-jam, rincian item, TOTAL,
    badge TUNAI — LUNAS atau KREDIT — BELUM BAYAR (kredit menampilkan
    pelanggan + Dibayar + Sisa piutang), tombol Cetak + Tutup.
33. Riwayat Transaksi: tab Hari Ini / Semua / Penjualan Saya, pencarian,
    filter status + cara bayar, ringkasan omzet, buka detail, Cetak Ulang,
    Batalkan Transaksi via modal (stok dikembalikan).
34. Pelanggan: kartu per pelanggan (inisial, kelompok, telepon, alamat,
    jumlah transaksi, total belanja), tambah/edit/hapus via modal.
35. Produk (admin): tabel (barang+barcode, kategori, Beli → Jual, margin,
    stok), filter kategori + stok, tambah/edit via modal; HAPUS hanya
    boleh bila stok 0 dengan modal peringatan, produk terhapus hilang
    realtime dari kasir TANPA merusak riwayat transaksi.
36. Pembelian (admin): daftar draft vs selesai (expandable), tombol Tandai
    Selesai (stok +) dan Hapus draft via modal; form popup 3 tombol:
    Batal / Simpan Draft / Simpan & Selesaikan.
37. Supplier (admin): kartu (inisial, telepon, alamat, SKU dipasok,
    jumlah pembelian, nilai beli), tambah/edit/hapus via modal.
38. Sekolah (super admin): daftar + status + sisa hari langganan
    (contoh: Sisa 18 hari / Sisa 7 hari / Telat), tombol Edit,
    Perpanjang, Aktifkan/Nonaktifkan, Hapus; form tambah TANPA input
    kode (kode SCH-XXX otomatis); modal konfirmasi perpanjang yang
    menjelaskan stacking.
39. Langganan 30 hari: dimulai saat sekolah dibuat/diaktifkan; H-7 hari
    muncul peringatan; lewat 30 hari muncul tagihan tiap minggu;
    Perpanjang saat masih aktif = periode baru dimulai SETELAH masa
    berjalan habis (stacking), bila sudah telat = mulai sekarang.
40. User: tabel (nama + KAMU, username + sekolah atau kosong untuk
    superadmin, role, jumlah jual, status Aktif/Nonaktif), tambah kasir
    (admin) / tambah admin (super admin), toggle, hapus via modal.
    Username full-width di form.
41. Laporan: tombol preset Hari ini / 7 hari / 30 hari / Semua + DUA input
    tanggal Dari–Sampai (baris sendiri di bawah preset), filter cara
    bayar + Tunai/Kredit, kartu Omzet / Modal (HPP) / Laba Kotor /
    Rata-rata / Piutang, panel Cara Bayar + Top Produk, tabel detail
    (Omzet/HPP/Laba/Status), Export CSV. TIDAK ADA grafik batang
    (sudah dihapus).
42. Notifikasi: lonceng header dengan badge merah ANGKA unread; dropdown
    mini (klik item = tandai dibaca + badge berkurang) + tombol Tandai
    dibaca; halaman Notifikasi dengan filter Semua / Belum dibaca /
    Kredit / Stok / Langganan / Lainnya; item BARU ber-highlight, setelah
    diklik permanen terbaca walau di-refresh.
43. Aturan notifikasi kasir: HANYA pengingat kredit (tepat H+2 dan tiap
    7 hari: 7/14/21...) + apresiasi omzet pribadi (≥ Rp1 juta/hari,
    ≥ Rp10 juta/minggu).
44. Aturan notifikasi admin: HANYA stok menipis (≤10) / habis (0) +
    apresiasi omzet sekolah (≥ Rp5 juta/hari, ≥ Rp30 juta/minggu).
45. Aturan notifikasi super admin: HANYA timer langganan (H-7 sebelum
    30 hari + tiap minggu setelah jatuh tempo).
46. Settings: kartu profil (nama + username + role), ganti nama (cooldown
    7 hari) & username (cooldown 30 hari), ganti password 2 langkah
    (verifikasi password lama dulu), pilihan tema Gelap/Terang.
47. Semua popup menutup overlay penuh (sidebar + navbar ikut tertutup)
    dan HANYA tertutup via tombol Batal/Simpan/Tutup (klik overlay
    tidak menutup); tidak ada alert/confirm bawaan browser di mana pun.
48. Contoh piutang nyata: Rizky Ramadhan 2 hari, Hidayat 7 hari,
    Koperasi Unit 2 14 hari (SMKN 1); Dimas Prasetyo 2 hari,
    Lina Marlina 7 hari, Yoga Saputra 14 hari (SMKN 2).

BAGIAN 5 — ANGKA ASLI DATABASE (pakai untuk kesimpulan & contoh)
49. Total sekolah: 4.
50. Total user aktif: 43 (3 superadmin global + 40 users sekolah).
51. Total produk aktif: 400 (100 per sekolah).
52. Total pelanggan: 61.
53. Total supplier: 21.
54. Total penjualan: 462 transaksi (47 di antaranya hari ini).
55. Total omzet tercatat: Rp41.177.350.
56. Total piutang (belum bayar): 7 transaksi.
57. Total pembelian: 4 (1 draft + 3 selesai).
58. Total notifikasi: 16 (12 belum dibaca).
59. Stok habis: 1 produk. Stok menipis: 2 produk.
60. Skema database (14 tabel): tb_sekolah, roles, tb_user,
    tb_kelompok_kategori, tb_kategori, tb_supplier, tb_barang,
    tb_pembelian, tb_detail_pembelian, tb_kelompok_pelanggan,
    tb_pelanggan, tb_penjualan, tb_detail_penjualan, tb_notifikasi.

BAGIAN 6 — DAFTAR HAL YANG DILARANG TULIS (ANTI-HALUSINASI, WAJIB DIPATUHI)
61. DILARANG menyebut splash screen / animasi bola cahaya / ledakan.
62. DILARANG menyebut gamifikasi, streak api, pojok santai, mini-games.
63. DILARANG menyebut suara/TTS AI, dialek daerah, printer 58mm/80mm,
    wallpaper sinematik, 5 warna aksen.
64. DILARANG menyebut audit log login / riwayat login / forensik / IP /
    browser (fitur ini TIDAK ADA).
65. DILARANG menyebut foto produk (katalog hanya teks + badge).
66. DILARANG menyebut grafik batang di Laporan (sudah dihapus) dan
    grafik di dashboard super admin (disembunyikan).
67. DILARANG menyebut mode pantau read-only (tidak ada).
68. DILARANG menyebut kategori expandable / valuasi aset / stock opname
    (tidak ada halaman khusus).
69. DILARANG menyebut foto profil/avatar (tidak ada).
70. DILARANG menyebut payment gateway / QRIS otomatis / cloud;
    QRIS di sini hanya label cara bayar.
71. Jika ragu apakah sesuatu ada, cek BAGIAN 4; bila tidak ada di sana,
    JANGAN tulis.

BAGIAN 7 — STRUKTUR NASKAH (ikuti persis urutan & penomoran ini)
72. Halaman judul: "LAPORAN DOKUMENTASI PROYEK NIXA" (besar, tengah),
    subjudul "SISTEM NIXA IS X-PLATFORM ACCOUNTING: DOKUMENTASI
    ARSITEKTUR ANTARMUKA, MANAJEMEN TRANSAKSI, MULTI-ROLE, DAN
    INVENTARIS", "Disusun Oleh: [ISI NAMA ANGGOTA + KELAS, contoh
    2 orang]", "[ISI SEKOLAH]", "TAHUN AJARAN 2026/2027".
73. DAFTAR ISI lengkap dengan nomor halaman (i, 1, 2, ...).
74. Setiap BAB diawali judul tengah + 1 paragraf pembuka.
75. Setiap subbab = 1–2 paragraf + diakhiri TEPAT SATU baris
    "Gambar X.Y: [deskripsi]" sesuai daftar BAGIAN 8.
76. Footer tiap halaman: "NIXA [ISI KELAS] 2026/2027".
77. BAB I – ARSITEKTUR OTENTIKASI DAN KEAMANAN AKSES.
78. BAB II – DASHBOARD PER ROLE.
79. BAB III – MODUL TRANSAKSI KASIR (POINT OF SALE).
80. BAB IV – RIWAYAT TRANSAKSI.
81. BAB V – MASTER DATA (PRODUK, PEMBELIAN, SUPPLIER, PELANGGAN).
82. BAB VI – SEKOLAH DAN LANGGANAN 30 HARI.
83. BAB VII – PENGGUNA DAN PENGATURAN PROFIL.
84. BAB VIII – LAPORAN.
85. BAB IX – NOTIFIKASI.
86. BAB X – KESIMPULAN DAN SARAN.
87. Lampiran A: akun demo + link website [ISI LINK].
88. Lampiran B: skema database (14 tabel + relasinya, singkat).

BAGIAN 8 — ISI TIAP BAB (tulis semua, jangan ada yang diskip)
89. 1.1 Halaman login: layout dua kolom (kiri branding NIXA + quotes,
    kanan form Username/Password + Remember me + tombol Masuk),
    login multi-role otomatis ke dashboard sesuai wewenang.
90. 1.2 Modal Logout ala ChatGPT: akun aktif + badge AKTIF di atas,
    maks 2 akun lain, tombol Logout merah + teks warning.
91. 1.3 Beralih Akun: menu sidebar di bawah Settings, popup sama tanpa
    tombol logout, klik akun lain = keluar + username terisi otomatis.
92. 1.4 RBAC: tulis matriks BAGIAN 3 butir 19–22 + perilaku 403.
93. 2.1 Dashboard kasir: sapaan + 3 kartu shift + tombol Buka Kasir /
    Data Pelanggan + daftar Transaksi Shift Saya.
94. 2.2 Dashboard admin: 4 kartu operasional + grafik omzet 7 hari +
    Penjualan Terbaru.
95. 2.3 Dashboard super admin: ringkasan jaringan + tabel per-sekolah
    (Sekolah/Admin/Kasir/SKU/Trx/Omzet) + Kelola Akses + Penjualan
    Terbaru (TANPA grafik — tegaskan dihapus).
96. 3.1 Katalog: pencarian, filter kategori, badge Stok/Sisa/STOK HABIS,
    stok 0 ke bawah + disabled, realtime (pembelian admin & transaksi
    kasir lain langsung mengubah angka).
97. 3.2 Keranjang: −/+ , input angka borong (1..stok), diskon %,
    pilih pelanggan + cara bayar, Tunai vs Kredit, Batalkan Keranjang
    kondisional, Total Tagihan, Kembalian (tunai saja).
98. 3.3 Struk: #TRX-XXXX, badge TUNAI LUNAS vs KREDIT BELUM BAYAR
    (+pelanggan/Dibayar/Sisa piutang), Cetak + Tutup.
99. 4.1 Riwayat: tab Hari Ini/Semua/Penjualan Saya, pencarian, filter
    status + cara bayar, ringkasan omzet, expand detail.
100. 4.2 Pembatalan via modal: stok kembali, riwayat tetap tercatat.
101. 4.3 Cetak ulang struk.
102. 5.1 Produk: kolom tabel (barang+barcode, kategori, Beli → Jual,
    margin, stok), filter, tambah/edit, aturan hapus stok-0 +
    modal peringatan + efek realtime tanpa merusak riwayat.
103. 5.2 Pembelian: kartu draft vs selesai, expand detail, Tandai
    Selesai (stok +), Hapus draft, form 3 tombol (Batal / Simpan
    Draft / Simpan & Selesaikan).
104. 5.3 Supplier: kartu (SKU dipasok, jumlah & nilai pembelian),
    tambah/edit/hapus.
105. 5.4 Pelanggan: kartu (kelompok, kontak, jumlah & total belanja),
    tambah/edit/hapus; kelompok Siswa / Guru & Karyawan / Umum.
106. 6.1 Daftar sekolah: status + sisa hari (Sisa X hari / Telat X hari
    + tanggal s/d), tombol Edit/Perpanjang/Aktifkan/Hapus.
107. 6.2 Tambah sekolah: TANPA input kode (otomatis SCH-XXX).
108. 6.3 Perpanjang + stacking: contoh nyata SCH002 H-7 dan SCH003
    jatuh tempo; timer mulai ulang saat diaktifkan.
109. 7.1 Tabel user: nama + KAMU, @username + sekolah (KOSONG untuk
    superadmin), role, jumlah jual, toggle Aktif/Nonaktif, hapus.
110. 7.2 Tambah kasir (admin) / tambah admin (super admin); username
    full-width; password wajib untuk akun baru.
111. 7.3 Settings: ganti nama (7 hari) & username (30 hari) + info sisa
    cooldown, password 2 langkah, tema Gelap/Terang.
112. 8.1 Filter: preset + DUA input Dari–Sampai (baris sendiri) +
    cara bayar + Tunai/Kredit + Export CSV.
113. 8.2 Kartu: Omzet / Modal (HPP) / Laba Kotor / Rata-rata / Piutang
    (rumus laba = omzet − modal).
114. 8.3 Panel Cara Bayar + Top Produk + tabel detail + CSV.
115. 9.1 Lonceng: badge merah angka, dropdown mini, klik = dibaca.
116. 9.2 Halaman: filter Semua/Belum dibaca/Kredit/Stok/Langganan/
    Lainnya, highlight BARU, tandai semua dibaca.
117. 9.3 Tulis ambang tiap role (butir 43–45) + contoh nyata butir 48.
118. 10.1 Kesimpulan 5 poin memakai ANGKA BAGIAN 5 (462 transaksi,
    Rp41.177.350 omzet, 400 produk, 43 user, 4 sekolah, 7 piutang).
119. 10.2 Saran: payment gateway QRIS otomatis, aplikasi mobile,
    sinkronisasi cloud multi-cabang.
120. Lampiran A: tabel 3 akun contoh + password 123 + [ISI LINK].
121. Lampiran B: 14 tabel + relasi inti (user→sekolah/role,
    barang→kategori/supplier/sekolah, penjualan→user/pelanggan/
    sekolah + detail, pembelian→supplier + detail, notifikasi→
    sekolah/user).

BAGIAN 9 — DAFTAR FOTO WAJIB (satu fitur = minimal satu foto; tulis
baris "Gambar X.Y: ..." persis setelah subbab terkait)
122. FOTO-01: Halaman login mode gelap (full).
123. FOTO-02: Halaman login mode terang (full).
124. FOTO-03: Modal Logout (akun aktif + 2 akun lain + tombol merah).
125. FOTO-04: Popup Beralih Akun (tanpa tombol logout).
126. FOTO-05: Dashboard kasir (kartu shift + tombol).
127. FOTO-06: Dashboard admin + grafik 7 hari.
128. FOTO-07: Dashboard super admin (tabel per-sekolah).
129. FOTO-08: Transaksi: katalog + keranjang terisi.
130. FOTO-09: Transaksi mode Kredit (info piutang, tanpa input bayar).
131. FOTO-10: Struk TUNAI — LUNAS.
132. FOTO-11: Struk KREDIT — BELUM BAYAR + sisa piutang.
133. FOTO-12: Riwayat + tab + filter + baris ter-expand.
134. FOTO-13: Modal Batalkan Transaksi.
135. FOTO-14: Pelanggan (kartu + statistik kelompok).
136. FOTO-15: Modal tambah Pelanggan.
137. FOTO-16: Produk (tabel + badge stok + filter).
138. FOTO-17: Modal tambah/edit Produk.
139. FOTO-18: Modal peringatan hapus produk stok-0.
140. FOTO-19: Pembelian (draft vs selesai ter-expand).
141. FOTO-20: Form Pembelian (3 tombol).
142. FOTO-21: Modal Tandai Selesai.
143. FOTO-22: Supplier (kartu + statistik).
144. FOTO-23: Modal tambah Supplier.
145. FOTO-24: Sekolah (status + sisa hari + s/d).
146. FOTO-25: Modal tambah Sekolah (kode otomatis).
147. FOTO-26: Modal Perpanjang (teks stacking).
148. FOTO-27: User (tabel + badge KAMU).
149. FOTO-28: Modal tambah User (username full-width).
150. FOTO-29: Settings profil (nama/username + cooldown).
151. FOTO-30: Popup ganti password langkah 1 dan 2.
152. FOTO-31: Laporan (preset + Dari–Sampai + kartu ringkasan).
153. FOTO-32: Laporan (Cara Bayar + Top Produk + tabel).
154. FOTO-33: Header lonceng dengan badge merah angka.
155. FOTO-34: Dropdown mini notifikasi (item BARU).
156. FOTO-35: Halaman Notifikasi kasir (pengingat kredit).
157. FOTO-36: Halaman Notifikasi admin (stok + apresiasi).
158. FOTO-37: Halaman Notifikasi super admin (timer langganan).
159. FOTO-38: phpMyAdmin daftar 14 tabel db.
160. FOTO-39: Isi tb_penjualan (terlihat tunai + kredit).
161. FOTO-40: Isi tb_barang (stok + harga).
162. FOTO-41: Isi tb_notifikasi (tipe + is_read).
163. FOTO-42: Isi tb_user (role + sekolah, superadmin kosong).
164. FOTO-43: Sidebar + topbar (logo NIXA).

BAGIAN 11 — SKENARIO PENGUJIAN & ALUR SCREENSHOT (tulis sebagai BAB
pengujian / lampiran bila perlu; urutan klik persis untuk setiap foto)
S-01. Login sebagai superadmin / 123 → verifikasi mendarat di Dashboard.
S-02. Buka halaman Sekolah → catat status + sisa hari tiap sekolah.
S-03. Buka halaman User → filter per sekolah, catat 10 user per sekolah.
S-04. Buka Laporan sebagai superadmin → preset Semua → Export CSV.
S-05. Buka Notifikasi sebagai superadmin → catat timer SCH002 & SCH003.
S-06. Klik lonceng → catat badge angka → klik satu item → badge berkurang.
S-07. Buka Settings sebagai superadmin → ganti tema Terang untuk foto.
S-08. Logout via modal (FOTO-03) → login sebagai smkn1_admin01 / 123.
S-09. Dashboard admin → catat 4 kartu + grafik 7 hari.
S-10. Produk → cari barang stok 0 → buka modal hapus (jangan hapus).
S-11. Produk → tambah produk baru via modal → catat harga exact tampil.
S-12. Pembelian → expand satu draft + satu selesai → catat detail.
S-13. Pembelian → buka form → catat 3 tombol (FOTO-20).
S-14. Selesaikan satu draft via modal (FOTO-21) → stok bertambah.
S-15. Supplier → catat kartu + buka modal tambah.
S-16. User (admin) → catat hanya kasir sekolah sendiri.
S-17. Laporan (admin) → preset 7 hari → catat ringkasan.
S-18. Laporan → isi Dari–Sampai → catat tabel berubah.
S-19. Notifikasi (admin) → catat stok + apresiasi.
S-20. Logout → login sebagai smkn1_kasir01 / 123.
S-21. Dashboard kasir → catat sapaan + kartu shift.
S-22. Transaksi → tambah 3 barang (satu qty ketik 12) + diskon 10%.
S-23. Transaksi mode Kredit → pilih pelanggan → SIMPAN KREDIT.
S-24. Struk kredit muncul → Cetak (dialog print) → Tutup.
S-25. Transaksi tunai → bayar lebih → catat kembalian → struk.
S-26. Batalkan Keranjang muncul/hilang sesuai isi (catat dua kondisi).
S-27. Riwayat → tab + filter + expand + modal batalkan (jangan eksekusi
  bila takut data berubah; boleh batalkan transaksi kecil).
S-28. Pelanggan → tambah pelanggan baru via modal.
S-29. Pelanggan → hapus via modal → catat riwayat tetap ada.
S-30. Settings kasir → ganti tema → foto mode gelap vs terang.

BAGIAN 12 — SKEMA DATABASE DETAIL (tulis ringkas di Lampiran B + 4 foto DB)
T-01. tb_sekolah: id_sekolah, kode_sekolah (unik SCH001–SCH004),
  nama_sekolah, alamat_sekolah, website, is_active, created_at,
  activated_at (awal timer 30 hari; contoh SCH002 H-23, SCH003 H-30).
T-02. roles: id_role, nama_role (super admin, admin, kasir).
T-03. tb_user: id_user, id_sekolah (NULL = superadmin global),
  id_role, username (format smkn{N}_{role}{NN}), password (bcrypt,
  semua "123"), nama_lengkap, is_active, created_at/updated_at +
  by, deleted_at/by (soft delete), nama_lengkap_changed_at +
  username_changed_at (cooldown profil).
T-04. tb_kelompok_kategori: id_kelompok, id_sekolah, nama_kelompok
  (contoh: Makanan, Minuman, ATK).
T-05. tb_kategori: id_kategori, id_kelompok, nama, + audit +
  is_delete (contoh: Snack, Minuman Dingin, Alat Tulis).
T-06. tb_supplier: id_supplier, id_sekolah, nama, no_telepon,
  alamat_supplier + audit + is_delete (21 baris).
T-07. tb_barang: id_barang, id_sekolah, barcode (unik per sekolah),
  nama, id_kategori, id_kelompok_kategori, id_supplier, satuan,
  harga_beli, harga_jual, stok, is_active + audit + is_delete
  (400 aktif; 1 habis + 2 menipis di SCH001).
T-08. tb_pembelian: id_pembelian, id_sekolah, id_supplier, id_user,
  nomor_faktur (PO-YYYY-NNNN), tanggal_faktur, total_bayar,
  status_pembelian (draft/selesai), jenis_transaksi (tunai/kredit),
  cara_bayar, note + audit + is_delete (1 draft + 3 selesai).
T-09. tb_detail_pembelian: id_detail_pembelian, id_pembelian,
  id_barang, satuan, jumlah, harga_beli, subtotal.
T-10. tb_kelompok_pelanggan: id_kelompok_pelanggan, id_sekolah,
  nama_kelompok (Siswa, Guru & Karyawan, Umum).
T-11. tb_pelanggan: id_pelanggan, id_kelompok_pelanggan,
  nama_pelanggan, telepon, alamat + audit + is_delete (61 baris).
T-12. tb_penjualan: id_penjualan, id_sekolah, id_user, id_pelanggan
  (boleh NULL = walk-in), tanggal_penjualan, total_faktur,
  total_bayar, kembalian, status_pembayaran (sudah/belum bayar),
  jenis_transaksi (tunai/kredit), cara_bayar, note + audit +
  is_delete (462 baris; 7 piutang).
T-13. tb_detail_penjualan: id_detail_penjualan, id_penjualan,
  id_barang, jumlah_barang, harga_beli (snapshot HPP), harga_jual
  (snapshot), diskon_tipe, diskon_nilai, diskon_nominal, subtotal.
T-14. tb_notifikasi: id_notifikasi, id_sekolah, id_user (NULL =
  se-role), role_target (kasir/admin/super admin), tipe
  (kredit_2hari/kredit_mingguan/omzet_harian/omzet_mingguan/
  stok_menipis/stok_habis/sekolah_hampir_30hari/sekolah_30hari),
  judul, pesan, ref_type/ref_id/ref_key (idempotensi), href,
  is_read, read_at, created_at (16 baris; 12 unread).
T-15. Relasi inti: user→sekolah & role; barang→kategori/supplier/
  sekolah; penjualan→user/pelanggan/sekolah + detail;
  pembelian→supplier/user/sekolah + detail; notifikasi→sekolah/user.
T-16. Soft delete: hapus = is_delete=1 / deleted_at (riwayat aman);
  hapus produk mensyaratkan stok 0.

BAGIAN 13 — GLOSARIUM (pakai definisi ini di makalah)
G-01. POS: titik penjualan tempat kasir memproses transaksi.
G-02. RBAC: pembatasan menu per role.
G-03. Omzet: total nilai penjualan (total_faktur).
G-04. HPP/Modal: harga_beli × qty (disingkat modal di UI).
G-05. Laba kotor: omzet − modal.
G-06. Piutang: penjualan kredit belum bayar.
G-07. Draft pembelian: faktur masuk yang belum menambah stok.
G-08. Stok menipis: sisa 1–10. Stok habis: 0.
G-09. Struk: faktur digital #TRX-XXXX + tombol cetak.
G-10. Badge: lingkaran merah angka notifikasi belum dibaca.
G-11. Highlight BARU: penanda item belum diklik.
G-12. Cooldown profil: jeda ganti nama 7 hari / username 30 hari.
G-13. Langganan: masa aktif sekolah 30 hari sejak aktivasi.
G-14. Stacking: perpanjang aktif menumpuk setelah masa berjalan.
G-15. Kembalian: bayar tunai − total (tidak ada di kredit).
G-16. Diskon %: potongan per baris keranjang.
G-17. Katalog realtime: angka stok ter-update tiap polling.
G-18. Session: login berbasis sesi server (bukan token).
G-19. Soft delete: hapus logis tanpa menghilangkan riwayat.
G-20. CSV: berkas ekspor laporan (separator ;).
G-21. Tenant/sekolah: batas data per sekolah (isolasi otomatis).
G-22. Toggle: saklar Aktif/Nonaktif akun & sekolah.
G-23. Modal: jendela popup (hanya tertutup via tombol).
G-24. Inertia.js: jembatan Laravel ↔ Vue tanpa API manual per halaman.
G-25. Seeder: pengisi data awal/dummy database.

BAGIAN 14 — PANDUAN CAPTURE PER FOTO (tujuan + langkah + akun + mode)
FOTO-01: bukti login gelap. Login: bebas. Langkah: buka halaman login.
  Mode: gelap. Tujuan: tampilkan branding NIXA + form.
FOTO-02: bukti login terang. Sama seperti FOTO-01. Mode: terang.
FOTO-03: bukti logout aman. Login: kasir. Langkah: sidebar → Logout.
  Tujuan: modal akun + tombol merah + warning.
FOTO-04: bukti switch akun. Login: kasir. Langkah: sidebar → Beralih
  Akun. Tujuan: popup tanpa tombol logout.
FOTO-05: bukti dashboard kasir. Login: smkn1_kasir01. Tujuan: sapaan,
  3 kartu shift, 2 tombol menu.
FOTO-06: bukti dashboard admin. Login: smkn1_admin01. Tujuan: 4 kartu
  + grafik 7 hari + penjualan terbaru.
FOTO-07: bukti dashboard superadmin. Login: superadmin. Tujuan: tabel
  4 sekolah + kelola akses (tanpa grafik).
FOTO-08: bukti kasir aktif. Login: kasir. Langkah: tambah 3 barang.
  Tujuan: katalog + keranjang + total + kembalian.
FOTO-09: bukti mode kredit. Login: kasir. Langkah: jenis Kredit +
  pelanggan. Tujuan: info piutang, tanpa input bayar.
FOTO-10: bukti struk tunai. Lanjutan FOTO-08 bayar tunai. Tujuan: badge
  TUNAI LUNAS + kembalian + tombol Cetak/Tutup.
FOTO-11: bukti struk kredit. Lanjutan FOTO-09. Tujuan: badge KREDIT,
  pelanggan, sisa piutang.
FOTO-12: bukti riwayat. Login: kasir. Langkah: tab + filter + expand
  satu baris. Tujuan: pencarian & detail.
FOTO-13: bukti void aman. Langkah: Batalkan Transaksi. Tujuan: modal
  konfirmasi + info stok kembali.
FOTO-14: bukti pelanggan. Login: kasir. Tujuan: kartu + statistik.
FOTO-15: bukti tambah pelanggan. Langkah: + Pelanggan. Tujuan: form
  modal (nama/kelompok/telepon/alamat).
FOTO-16: bukti katalog produk. Login: admin. Tujuan: tabel + badge
  stok + filter kategori/stok.
FOTO-17: bukti form produk. Langkah: + Produk. Tujuan: semua field +
  margin otomatis.
FOTO-18: bukti aturan hapus. Langkah: Hapus produk stok 0. Tujuan:
  modal peringatan + syarat stok-0.
FOTO-19: bukti pembelian. Login: admin. Langkah: expand draft +
  selesai. Tujuan: status + detail + tombol aksi.
FOTO-20: bukti form pembelian. Langkah: + Pembelian Baru. Tujuan:
  3 tombol Batal/Draft/Selesaikan.
FOTO-21: bukti selesaikan. Langkah: Tandai Selesai. Tujuan: modal +
  info stok bertambah.
FOTO-22: bukti supplier. Login: admin. Tujuan: kartu + SKU/draft/nilai.
FOTO-23: bukti form supplier. Langkah: + Supplier. Tujuan: form modal.
FOTO-24: bukti sekolah. Login: superadmin. Tujuan: status + sisa hari
  + s/d + 4 tombol aksi.
FOTO-25: bukti tambah sekolah. Langkah: + Sekolah. Tujuan: tanpa
  input kode (otomatis).
FOTO-26: bukti perpanjang. Langkah: Perpanjang. Tujuan: teks stacking
  + konfirmasi bayar.
FOTO-27: bukti user. Login: superadmin. Tujuan: tabel + KAMU + status.
FOTO-28: bukti form user. Langkah: + Admin. Tujuan: username
  full-width + password wajib.
FOTO-29: bukti settings. Login: admin. Tujuan: profil + cooldown.
FOTO-30: bukti password. Langkah: Ubah → langkah 1 dan 2. Tujuan:
  verifikasi lama + baru/konfirmasi.
FOTO-31: bukti laporan filter. Login: admin. Tujuan: preset + Dari–
  Sampai + kartu ringkasan.
FOTO-32: bukti laporan bawah. Tujuan: Cara Bayar + Top Produk + tabel
  + Export CSV.
FOTO-33: bukti badge. Login: kasir. Tujuan: lonceng + angka merah.
FOTO-34: bukti dropdown. Langkah: klik lonceng. Tujuan: item BARU.
FOTO-35: bukti notif kasir. Tujuan: pengingat kredit (contoh Rizky
  Ramadhan 2 hari).
FOTO-36: bukti notif admin. Login: admin. Tujuan: stok + apresiasi.
FOTO-37: bukti notif superadmin. Login: superadmin. Tujuan: timer
  SCH002 H-7 + SCH003 jatuh tempo.
FOTO-38: bukti skema. phpMyAdmin → daftar 14 tabel. Tujuan: struktur.
FOTO-39: bukti isi jual. Tabel tb_penjualan. Tujuan: tunai + kredit.
FOTO-40: bukti isi barang. Tabel tb_barang. Tujuan: stok + harga.
FOTO-41: bukti isi notif. Tabel tb_notifikasi. Tujuan: tipe + is_read.
FOTO-42: bukti isi user. Tabel tb_user. Tujuan: role + sekolah
  (superadmin kosong).
FOTO-43: bukti kerangka. Sidebar + topbar. Tujuan: logo NIXA + menu
  per role + lonceng + profil.
FOTO-44: bukti prefill switch. Login dengan ?username= terisi. Tujuan:
  hasil klik akun lain di popup Beralih Akun.
FOTO-45: bukti keranjang kosong. Tujuan: teks ajakan + tombol
  Batalkan Keranjang TIDAK tampil.
FOTO-46: bukti keranjang penuh. Tujuan: tombol Batalkan Keranjang
  tampil + Total Tagihan + Kembalian.
FOTO-47: bukti pencarian katalog. Langkah: ketik nama barang.
  Tujuan: hasil filter realtime.
FOTO-48: bukti produk stok-0 di kasir. Tujuan: kartu paling bawah +
  STOK HABIS + disabled.
FOTO-49: bukti dashboard terang vs gelap. Tujuan: dua mode tema.
FOTO-50: bukti filter user. Login: superadmin. Langkah: filter
  sekolah + cari nama. Tujuan: pencarian user.
FOTO-51: bukti pencarian sekolah. Langkah: ketik kode. Tujuan: filter.
FOTO-52: bukti tab riwayat. Tujuan: Hari Ini vs Semua vs Saya.
FOTO-53: bukti spending piutang. Riwayat filter Belum bayar. Tujuan:
  daftar piutang + nominal.
FOTO-54: bukti tema settings. Tujuan: tombol Gelap/Terang + AKTIF.
FOTO-55: bukti profil cooldown. Tujuan: teks "Bisa diganti lagi
  dalam X hari" setelah ganti nama/username.

BAGIAN 20 — INVENTARIS TEKS UI (kutip persis saat mendeskripsikan layar)
U-01. Sidebar: "NIXA" + "X-PLATFORM ACCOUNTING".
U-02. Menu: Dashboard, Transaksi, Riwayat Transaksi, Pelanggan,
  Produk, Pembelian, Supplier, Sekolah, User, Laporan, Notifikasi,
  Settings, Beralih Akun, Logout.
U-03. Sapaan kasir: "mari lakukan transaksi untuk hari ini!".
U-04. Kasir: "Transaksi Shift Saya", "Omzet Saya", "Rata-rata / Trx",
  "Buka Kasir", "Data Pelanggan" + "Kelola pelanggan daganganmu".
U-05. Kasir: "Transaksi Shift Saya" + "Riwayat lengkap →".
U-06. Keranjang: "Total Tagihan", "Nominal bayar", "Kembalian",
  "BAYAR • ...", "SIMPAN KREDIT • ...", "Batalkan Keranjang".
U-07. Kredit: "Transaksi kredit — tanpa pembayaran sekarang" +
  "piutang (belum bayar)".
U-08. Struk: "Struk Penjualan", "TUNAI — LUNAS" / "KREDIT — BELUM
  BAYAR", "Dibayar", "Sisa piutang", "Terima kasih".
U-09. Riwayat tab: "Hari Ini", "Semua", "Penjualan Saya".
U-10. Produk: "Total SKU", "Nilai Stok (HPP)", "Stok Menipis ≤10",
  "Nonaktif", "Margin".
U-11. Produk hapus: "Hapus produk?", "Hanya bisa dihapus karena stok
  0", "REALTIME", "Riwayat transaksi TIDAK terpengaruh".
U-12. Pembelian: "Tandai Selesai (stok +)", "Hapus draft", "Simpan
  sebagai Draft" no→ tombol: "Batal / Simpan Draft /
  Simpan & Selesaikan".
U-13. Sekolah: "Sisa X hari", "Telat X hari", "s/d {tanggal}",
  "Perpanjang", "Aktifkan/Nonaktifkan", kode "otomatis".
U-14. Sekolah modal: "Konfirmasi ... sudah bayar & perpanjang 30 hari".
U-15. User: badge "KAMU", "Aktif/Nonaktif", "+ Kasir / + Admin".
U-16. Settings: "Bisa diganti lagi dalam X hari", "Verifikasi password
  lama", "Password baru", "Minimal 3 karakter".
U-17. Laporan: "Hari ini / 7 hari / 30 hari / Semua", "Dari",
  "Sampai", "Tunai + Kredit", "Export CSV", "Cara Bayar",
  "Top Produk".
U-18. Notifikasi: "Belum dibaca", "Kredit", "Stok", "Langganan",
  "Lainnya", "Tandai semua dibaca", badge "BARU".
U-19. Logout: "Keluar akun?", "AKTIF", "Akun lain di perangkat ini
  (x/2)", "Pakai →", "Akun ini akan keluar & sesi berakhir."
U-20. Umum: "Aman! Tidak ada peringatan.", "Tidak ada data.",
  "Menyimpan…", "Memproses…".

BAGIAN 21 — PROFIL TIAP SEKOLAH (tulis 1 paragraf per sekolah di BAB II/VI)
V-01. SMKN 1 Tasikmalaya (SCH001): sekolah utama Basil, transaksi
  tersibuk (ratusan penjualan, omzet jutaan/hari), contoh piutang
  Rizky Ramadhan, Hidayat, Koperasi Unit 2; contoh stok kritis.
V-02. SMKN 2 Tasikmalaya (SCH002): langganan H-23 (peringatan H-7
  aktif), contoh piutang Dimas Prasetyo (2 hari), Lina Marlina
  (7 hari), Yoga Saputra (14 hari); kasir utama Agus Wijaya.
V-03. SMKN 3 Tasikmalaya (SCH003): langganan H-30 (jatuh tempo,
  perlu konfirmasi bayar); aktivitas ringan.
V-04. SMKN 4 Tasikmalaya (SCH004): sekolah baru (aktivasi hari ini),
  master lengkap + aktivitas ringan perintis.
V-05. Pola user: tiap sekolah 2 admin + 8 kasir; superadmin global
  3 akun tanpa sekolah.

BAGIAN 22 — TABEL UJI BLACK-BOX (tulis sebagai BAB/lampiran pengujian)
B-01. Login valid tiap role → masuk dashboard sesuai role.
B-02. Login salah → pesan error, tetap di halaman login.
B-03. Buka menu di luar role → 403/dashboard.
B-04. Tambah barang ke keranjang → qty 1, total bertambah exact.
B-05. Ketik qty 50 → dijepit ke stok bila melebihi.
B-06. Diskon 10% → subtotal berkurang tepat 10%.
B-07. Bayar tunai kurang → error, transaksi tidak tersimpan.
B-08. Bayar tunai pas/lebih → struk + kembalian benar.
B-09. Kredit → tanpa input bayar → status belum bayar + piutang.
B-10. Batalkan keranjang → kosong + tombol hilang.
B-11. Batalkan transaksi hari ini → stok kembali.
B-12. Hapus produk stok >0 → ditolak (backend + UI).
B-13. Hapus produk stok 0 → hilang dari kasir, riwayat utuh.
B-14. Simpan draft → stok tidak berubah.
B-15. Selesaikan pembelian → stok bertambah tepat.
B-16. Tambah supplier/pelanggan/user → muncul di daftar.
B-17. Toggle nonaktif user → tidak bisa login.
B-18. Tambah sekolah → kode otomatis + timer 0 hari.
B-19. Perpanjang aktif → stacking setelah expiry + notif selesai.
B-20. Ganti nama → cooldown 7 hari tampil.
B-21. Password lama salah → langkah 2 terkunci.
B-22. Filter laporan tanggal → tabel + ringkasan berubah.
B-23. Export CSV → berkas terunduh, separator ;.
B-24. Klik notif → badge berkurang, refresh tetap dibaca.
B-25. Klik overlay modal → tidak tertutup.
B-26. Ganti tema → tersimpan setelah refresh.
B-27. Logout via modal → sesi berakhir ke halaman login.
B-28. Beralih akun → username terisi otomatis.
B-29. Kasir akses /produk → ditolak.
B-30. Stok barang habis → kartu disabled paling bawah.

BAGIAN 16 — BRIEF PARAGRAF PER SUBBAB (setiap subbab wajib memuat
butir-butir ini dalam 1–2 paragraf sebelum baris Gambar)
P-01 (1.1). Paragraf 1: fungsi login sebagai gerbang + kredensial
  username/password + Remember me. Paragraf 2: prefill otomatis saat
  beralih akun + branding NIXA dua mode.
P-02 (1.2). Paragraf 1: anatomi modal logout (akun aktif, 2 akun lain,
  tombol merah, warning). Paragraf 2: mengapa konfirmasi mencegah
  logout tak sengaja di meja kasir.
P-03 (1.3). Paragraf 1: lokasi menu + isi popup tanpa tombol logout.
  Paragraf 2: alur klik akun lain (keluar + username terisi).
P-04 (1.4). Paragraf 1: definisi RBAC + tabel matriks 3 role.
  Paragraf 2: perilaku 403/pengalihan + isolasi data per sekolah.
P-05 (2.1). Paragraf 1: sapaan + makna tiap kartu shift kasir.
  Paragraf 2: dua tombol aksi + daftar transaksi shift.
P-06 (2.2). Paragraf 1: 4 kartu operasional admin + grafik 7 hari.
  Paragraf 2: panel Penjualan Terbaru + tombol laporan.
P-07 (2.3). Paragraf 1: ringkasan jaringan + tabel 4 sekolah.
  Paragraf 2: panel Kelola Akses + penegasan TANPA grafik.
P-08 (3.1). Paragraf 1: layout katalog vs keranjang + pencarian/kategori.
  Paragraf 2: badge stok + aturan stok-0 + realtime dua arah.
P-09 (3.2). Paragraf 1: isi baris keranjang (qty ketik, diskon,
  pelanggan, cara bayar). Paragraf 2: Tunai vs Kredit + tombol
  kondisional Batalkan Keranjang.
P-10 (3.3). Paragraf 1: anatomi struk tunai vs kredit.
  Paragraf 2: alur Cetak + Tutup.
P-11 (4.1). Paragraf 1: tab + pencarian + filter + ringkasan omzet.
  Paragraf 2: expand detail per transaksi.
P-12 (4.2). Paragraf 1: modal konfirmasi + efek stok kembali.
  Paragraf 2: batas kasir (milik sendiri, hari sama) + riwayat utuh.
P-13 (4.3). Paragraf 1: tombol Cetak Ulang + jendela struk.
P-14 (5.1). Paragraf 1: kolom tabel + filter + badge stok.
  Paragraf 2: form tambah/edit + margin otomatis.
P-15 (5.1b). Paragraf 1: syarat stok-0 + isi modal peringatan.
  Paragraf 2: efek realtime + riwayat tidak rusak (soft delete).
P-16 (5.2). Paragraf 1: beda draft vs selesai + expand detail.
  Paragraf 2: tiga tombol form + efek stok tiap pilihan.
P-17 (5.2b). Paragraf 1: isi modal Tandai Selesai + Hapus draft.
P-18 (5.3). Paragraf 1: isi kartu supplier + 3 statistiknya.
  Paragraf 2: form tambah/edit + aturan hapus.
P-19 (5.4). Paragraf 1: isi kartu pelanggan + statistik kelompok.
  Paragraf 2: form + kelompok Siswa/Guru/Umum + efek hapus.
P-20 (6.1). Paragraf 1: baris sekolah (status, sisa hari, s/d).
  Paragraf 2: empat tombol aksi + maknanya.
P-21 (6.2). Paragraf 1: form tanpa kode + contoh SCH002 H-7.
  Paragraf 2: contoh SCH003 jatuh tempo + tombol Perpanjang.
P-22 (6.3). Paragraf 1: definisi stacking + dua kasusnya.
  Paragraf 2: reset timer + notif merah ikut selesai.
P-23 (7.1). Paragraf 1: kolom tabel + badge KAMU + sekolah kosong
  milik superadmin. Paragraf 2: toggle + hapus + batas tiap role.
P-24 (7.2). Paragraf 1: isi form + username full-width.
  Paragraf 2: password wajib akun baru vs opsional saat edit.
P-25 (7.3). Paragraf 1: kartu profil + cooldown 7/30 + info sisa hari.
  Paragraf 2: password 2 langkah + tema Gelap/Terang permanen.
P-26 (8.1). Paragraf 1: preset + Dari–Sampai + cara bayar + jenis.
  Paragraf 2: perilaku custom vs preset + tombol CSV.
P-27 (8.2). Paragraf 1: lima kartu + rumus laba.
  Paragraf 2: cara membaca tiap kartu + contoh periode.
P-28 (8.3). Paragraf 1: panel Cara Bayar + Top Produk.
  Paragraf 2: kolom tabel + isi file CSV.
P-29 (9.1). Paragraf 1: badge angka + dropdown + klik-membaca.
  Paragraf 2: perbedaan dropdown vs halaman.
P-30 (9.2). Paragraf 1: filter + highlight BARU + tandai semua.
  Paragraf 2: persistensi setelah refresh (kolom is_read).
P-31 (9.3). Paragraf 1: ambang kasir + contoh nyata SCH001/SCH002.
  Paragraf 2: ambang admin + timer superadmin + contoh SCH002/003.
P-32 (10.1). Lima poin kesimpulan memakai angka BAGIAN 5.
P-33 (10.2). Tiga saran + alasan tiap saran.

BAGIAN 17 — PREDIKSI PERTANYAAN SIDANG + JAWABAN (tulis sebagai
lampiran/Q&A bila diminta penguji; siapkan 15 pasang ini)
Q-01. Mengapa ada 3 role? J: pemisahan tugas kasir/operasional/pemilik.
Q-02. Bagaimana mencegah kasir melihat data sekolah lain? J: isolasi
  otomatis id_sekolah dari session + 403 lintas tenant.
Q-03. Mengapa hapus produk mensyaratkan stok 0? J: cegah hilangnya
  katalog yang masih bernilai + jaga konsistensi riwayat.
Q-04. Bagaimana riwayat tetap utuh setelah produk dihapus? J: soft
  delete + snapshot harga di detail + lookup nama dipertahankan.
Q-05. Mengapa kredit tanpa input bayar? J: belum bayar = piutang,
  total_bayar 0, sisa = total faktur.
Q-06. Dari mana angka kembalian berasal? J: bayar tunai − total,
  hanya mode Tunai.
Q-07. Mengapa klik overlay tidak menutup popup? J: cegah data form
  hilang karena klik tak sengaja.
Q-08. Bagaimana badge notifikasi tahu jumlahnya? J: hitung is_read=0
  per scope role dari tb_notifikasi.
Q-09. Mengapa superadmin tanpa sekolah? J: peran developer global
  lintas tenant.
Q-10. Apa itu stacking langganan? J: perpanjang aktif menumpuk setelah
  expiry; telat mulai sekarang.
Q-11. Mengapa password semua 123 di demo? J: akun contoh; produksi
  wajib diganti + bcrypt.
Q-12. Bagaimana stok selalu akurat? J: decrement atomik saat jual,
  increment saat pembelian selesai/void.
Q-13. Untuk apa Export CSV? J: olah lanjut di spreadsheet + arsip.
Q-14. Mengapa ada cooldown nama/username? J: cegah penyalahgunaan
  identitas + audit (kolom changed_at).
Q-15. Apa keterbatasan sistem? J: single database, tanpa payment
  gateway otomatis, tanpa aplikasi mobile (→ saran).

BAGIAN 18 — TABEL BAKU YANG WAJIB ADA DI NASKAH (format markdown rapi)
TBL-01. Tabel akun contoh: kolom Role | Username | Password | Sekolah.
  Isi: Super Admin | superadmin | 123 | (kosong/global); Admin |
  smkn1_admin01 | 123 | SMKN 1 Tasikmalaya; Kasir | smkn1_kasir01 |
  123 | SMKN 1 Tasikmalaya.
TBL-02. Tabel matriks menu: baris = 6 menu kasir + 8 menu admin +
  6 menu super admin; kolom Akses (✓/–) per role.
TBL-03. Tabel ambang notifikasi: kolom Role | Pemicu | Ambang |
  Tujuan. Isi: kasir/kredit/H+2 & 7/14/21/tagih; kasir/omzet
  pribadi/1jt hari & 10jt minggu/motivasi; admin/stok/≤10 & 0/
  restock; admin/omzet sekolah/5jt hari & 30jt minggu/pantau;
  superadmin/langganan/H-7 & tiap minggu pasca-30/konfirmasi bayar.
TBL-04. Tabel 14 tabel database: kolom Tabel | Fungsi | Kunci relasi.
TBL-05. Tabel statistik database: metrik BAGIAN 5 butir 49–59.
TBL-06. Tabel perbandingan Tunai vs Kredit: baris input bayar,
  kembalian, status, struk, label tombol.

BAGIAN 19 — KALIMAT BAKU (pakai/adaptasi di naskah, jangan ubah makna)
K-01. "NIXA Is X-platform Accounting adalah sistem Point of Sale dan
  akuntansi ringan untuk koperasi dan kantin sekolah."
K-02. "Akses dibatasi berbasis peran: kasir, admin, dan super admin."
K-03. "Setiap transaksi tunai mengurangi stok secara atomik, sedangkan
  pembelian yang diselesaikan menambahnya kembali."
K-04. "Penghapusan bersifat logis (soft delete) sehingga riwayat
  transaksi tidak pernah hilang."
K-05. "Notifikasi tersimpan persisten di database dan ditandai dibaca
  per item."
K-06. "Periode langganan sekolah adalah 30 hari sejak aktivasi, dengan
  perpanjangan menumpuk bila masih aktif."
K-07. "Seluruh kata sandi akun contoh adalah 123 dan wajib diganti
  pada lingkungan produksi."

BAGIAN 23 — ARSITEKTUR TEKNIS (tulis ringkas di BAB I / lampiran)
A-01. Pola: monolit Laravel + Inertia (tanpa REST manual per halaman).
A-02. Backend: routes/web.php (halaman Inertia per role + middleware
  auth/role), routes/api.php (CRUD JSON session-based, grup web).
A-03. Controller halaman: PageController (dashboard, transaksi,
  riwayat, pembelian, produk, pelanggan, supplier, user, sekolah,
  laporan, notifikasi, settings) — tiap halaman dikirimi props awal
  dari database via hydrate(props).
A-04. Controller API: Auth, Dashboard, Kategori, Laporan, Notifikasi,
  Pelanggan, Pembelian, Penjualan, Produk, Sekolah, Settings,
  Supplier, User — isolasi sekolah via App\Support\Tenant (selalu
  dari user login, bukan input frontend).
A-05. Layanan notifikasi: App\Support\NotifikasiService (generator
  idempoten via ref_key) + TbNotifikasi.
A-06. Validasi: FormRequest per modul (Barang/User/Supplier/
  Pelanggan/Pembelian/Penjualan/Sekolah) + Rule unik per sekolah.
A-07. Transaksi stok atomik: DB::transaction + lockForUpdate;
  jual decrement, selesai-beli increment, void increment kembali.
A-08. Auth: guard web model TbUser (username + bcrypt), session +
  regenerate; remember opsional.
A-09. Middleware: auth, role:{daftar}, VerifyCsrfToken (X-XSRF-TOKEN),
  HandleInertiaRequests (share auth user + title).
A-10. Frontend: resources/js/pages (*.vue per halaman), layouts
  (DashboardLayout: sidebar + topbar lonceng + slot), components
  (DashboardSidebar, ConfirmModal, AccountModal, NixaLogo,
  Pagination, OmzetChart, RoleDenied, ui/*).
A-11. Composables: usePosStore (single source of truth + hydrate +
  mergeBarangRealtime + replaceCollection), useAuthMock (session
  + sinkron /api/auth/me), useAccounts (3 akun terakhir perangkat),
  usePagination, useAppearance (tema permanen).
A-12. Real-time: polling GET /api/produk + /api/pelanggan tiap
  4 detik di kasir; lonceng tiap 15 detik; polling = TanStack? TIDAK —
  tulis "polling interval fetch", jangan sebut library yang tak ada.
A-13. Cetak: struk dirender ke jendela print mandiri (window.print).
A-14. Export CSV: Blob + separator titik-koma, nama
  laporan-penjualan.csv.
A-15. Migrasi: 000010 skema POS 13 tabel, 000020 cooldown profil,
  000030 tb_notifikasi + activated_at.
A-16. Seeder: RoleSeeder, DummyDataSeeder (master 4×100 produk),
  DemoShowcaseSeeder (14 hari transaksi), DeployFinalSeeder
  (konsolidasi Tasikmalaya + notifikasi).
A-17. Konfigurasi: APP_NAME=NIXA, session file driver, timezone
  Asia/Jakarta (tulis hanya bila yakin; bila ragu tulis [CEK]).
A-18. Keamanan: password bcrypt (cast hashed), password hidden dari
  JSON, 403 lintas tenant (404 disamarkan), rate-limit login.

BAGIAN 24 — DAFTAR SINGKATAN (tabel lampiran)
S-01. POS — Point of Sale.
S-02. RBAC — Role-Based Access Control.
S-03. HPP — Harga Pokok Penjualan (modal).
S-04. SKU — kode barang/barcode unik per sekolah.
S-05. CRUD — Create, Read, Update, Delete.
S-06. CSV — Comma-Separated Values (ekspor laporan).
S-07. TTS — tidak dipakai (cantumkan sebagai "di luar ruang lingkup").
S-08. API — antarmuka JSON backend↔frontend.
S-09. UI — antarmuka pengguna.
S-10. UX — pengalaman pengguna.
S-11. FK — foreign key (relasi antar tabel).
S-12. H-7 — tujuh hari sebelum jatuh tempo.
S-13. QRIS — label metode bayar (non-otomatis).
S-14. NULL — kosong (mis. sekolah superadmin).

BAGIAN 25 — ABSTRAK + JUDUL ALTERNATIF (pilih satu, atau minta
Claude buatkan 3 varian)
AB-01. Judul utama: "SISTEM NIXA IS X-PLATFORM ACCOUNTING:
  DOKUMENTASI ARSITEKTUR ANTARMUKA, MANAJEMEN TRANSAKSI,
  MULTI-ROLE, DAN INVENTARIS".
AB-02. Abstrak wajib memuat: masalah (kasir manual), solusi (NIXA
  3 role), metode (observasi–implementasi–uji black-box B-01–B-30),
  hasil angka (butir 49–59), kesimpulan satu kalimat.
AB-03. Kata kunci: point of sale, multi-role, koperasi sekolah,
  notifikasi realtime, Laravel.
AB-04. Panjang abstrak: 1 halaman (±200 kata) + versi Inggris bila
  diminta pembimbing.

BAGIAN 26 — CHECKLIST PRA-CETAK (centang satu per satu)
C-01. Cover + identitas + tahun ajaran benar.
C-02. Daftar isi nomor halaman berurutan.
C-03. Setiap subbab diakhiri tepat satu baris Gambar X.Y.
C-04. Semua FOTO-01..FOTO-55 sudah diambil dan tertempel.
C-05. Tidak ada kata KlikNota / splash / TTS / game / audit-login.
C-06. Angka kesimpulan = angka BAGIAN 5.
C-07. Tabel TBL-01..TBL-06 ada dan terisi.
C-08. Footer "NIXA [KELAS] 2026/2027" di semua halaman.
C-09. Lampiran akun + link terisi (bukan placeholder).
C-10. Daftar singkatan + glosarium konsisten istilahnya.
C-11. Lolos cek plagiarisme (parafrase ulang bila perlu).

BAGIAN 27 — CARA MEMINTA REVISI KE CLAUDE (contoh perintah siap pakai)
R-01. "Lanjutkan BAB III sampai selesai."
R-02. "Tulis ulang BAB IX lebih panjang, tambah 2 paragraf per subbab."
R-03. "Ganti semua nominal contoh dengan angka BAGIAN 5."
R-04. "Tambahkan tabel perbandingan Tunai vs Kredit di BAB III."
R-05. "Buatkan abstrak + 3 varian judul dari naskah ini."
R-06. "Susun ulang daftar isi dengan nomor halaman i, 1–23."
R-07. "Parafrase BAB II agar lolos cek plagiarisme, makna tetap."
R-08. "Buatkan 10 pertanyaan sidang tambahan di luar BAGIAN 17."
R-09. "Tulis ulang kesimpulan dengan angka terbaru yang aku beri."
R-10. "Buatkan versi 1 halaman (executive summary) dari makalah ini."
R-11. "Buatkan naskah presentasi 10 slide dari tiap BAB."
R-12. "Tambahkan kutipan tiap tabel ke paragraf yang membahasnya."
R-13. "Periksa konsistensi istilah dengan BAGIAN 31, laporkan yangawait."
R-14. "Ubah semua 'Gambar X.Y' menjadi format 'Gambar 3-1'."
R-15. "Buatkan daftar isi ulang sesuai naskah final + halaman."
R-16. "Tulis ulang kata pengantar atas nama [ISI NAMA] kelas [ISI]."

BAGIAN 40 — FOTO CADANGAN (bila foto utama gagal/dianggap kurang)
F-01. Cadangan login: mode terang bila utama gelap, dan sebaliknya.
F-02. Cadangan struk: versi cetak (dialog print browser).
F-03. Cadangan riwayat: hasil pencarian spesifik satu nama.
F-04. Cadangan produk: hasil filter "Habis" (bukti aturan stok-0).
F-05. Cadangan pembelian: baris draft ter-expand penuh.
F-06. Cadangan laporan: preset "Semua" bila "7 hari" kosong.
F-07. Cadangan notifikasi: tab "Belum dibaca" yang sudah kosong
  + caption "semua sudah ditangani".
F-08. Cadangan DB: struktur/describe satu tabel (tb_penjualan).
F-09. Cadangan sidebar: menu tiap role berdampingan (3 foto).
F-10. Aturan: foto cadangan diberi nomor Gambar susulan (mis. 3.4)
  dan disebut di paragraf sebagai "dokumentasi tambahan".

BAGIAN 41 — FORMAT FILE KELUARAN (agar mudah jadi DOCX)
O-01. Gunakan heading markdown (#, ##, ###) untuk judul/BAB/subbab.
O-02. Gambar ditulis sebagai baris teks biasa (bukan sintaks ![ ]),
  agar mudah ditempeli foto manual di Word.
O-03. Tabel memakai sintaks markdown standar (mudah convert).
O-04. Bold untuk istilah penting, italic untuk istilah asing.
O-05. Satu baris kosong antar paragraf, dua baris antar subbab.
O-06. Jangan pakai emoji di naskah final.
O-07. Jangan pakai kode program kecuali nama tabel (monospace).
O-08. Nomor halaman ditulis manual per BAGIAN 35.
O-09. Font yang disarankan saat convert: Times New Roman 12,
  spasi 1,5, margin 4-3-3-3 (sesuai standar sekolah).
O-10. Simpan placeholder [ISI ...] persis apa adanya agar mudah
  dicari (Ctrl+F "[ISI") saat revisi manual.
O-11. Total target naskah: ±20 halaman isi di luar lampiran.

BAGIAN 39 — PENUTUP PROMPT (batas bawah; jangan hapus baris ini)
Z-01. Seluruh isi di atas adalah SATU perintah utuh.
Z-02. Eksekusi sekarang: keluarkan naskah makalah lengkap.
Z-03. Jangan meminta klarifikasi dalam bentuk apa pun.
Z-04. Jangan meringkas perintah ini; langsung hasilkan naskah.
Z-05. Placeholder yang belum terisi biarkan apa adanya.
Z-06. Bahasa: Indonesia formal. Nada: akademik SMK.
Z-07. Mulai dari halaman judul sampai checklist penutup.

BAGIAN 28 — CONTOH KALIMAT PEMBUKA PER BAB (adaptasi, jangan copas mentah)
N-01 (BAB I). "Keamanan sistem informasi koperasi sekolah dimulai
  dari gerbang yang paling sering disentuh: halaman login."
N-02 (BAB I). "NIXA membedakan tiga aktor dengan kebutuhan yang
  berbeda: kasir yang mengejar kecepatan, admin yang menjaga
  operasional, dan super admin yang mengawasi jaringan."
N-03 (BAB II). "Dashboard adalah cermin kesehatan toko; setiap role
  mendapat cermin yang berbeda sesuai wewenangnya."
N-04 (BAB III). "Meja kasir adalah tempat uang dan kepercayaan
  bertemu; modul ini dirancang agar keduanya tidak bocor."
N-05 (BAB IV). "Transaksi yang tidak tercatat sama dengan transaksi
  yang tidak pernah terjadi — karena itu riwayat dibuat anti-hilang."
N-06 (BAB V). "Master data yang berantakan melahirkan laporan yang
  berantakan; modul ini menegakkan disiplin sejak input."
N-07 (BAB VI). "Sekolah adalah tenant: datanya terisolasi, langganannya
  berbayar, timernya otomatis."
N-08 (BAB VII). "Akun adalah identitas; settings adalah kendali pemilik
  atas identitasnya sendiri."
N-09 (BAB VIII). "Laporan yang baik menjawab tiga pertanyaan: berapa
  masuk, berapa modal, berapa sisa."
N-10 (BAB IX). "Notifikasi yang baik datang tepat waktu, tepat orang,
  dan tidak berisik — tiga prinsip itu dipegang sistem ini."
N-11 (BAB X). "Sistem yang baik diukur dari angkanya, bukan janjinya."
N-12 (Penutup). "Dokumentasi ini disusun dari sistem yang berjalan,
  bukan dari rencana di atas kertas."

BAGIAN 29 — NASKAH DEMO LIVE / SIDANG (durasi ±10 menit, urut klik)
D-01 (0:00). Login superadmin/123 → sapu Dashboard 4 sekolah.
D-02 (1:00). Sekolah → tunjuk SCH003 jatuh tempo + SCH002 H-7.
D-03 (2:00). Perpanjang SCH003 → timer reset (jelaskan stacking).
D-04 (3:00). Logout modal → Beralih Akun → login smkn1_admin01.
D-05 (4:00). Produk → tambah produk → hapus produk stok-0 (warning).
D-06 (5:00). Pembelian → Simpan & Selesaikan → stok bertambah live.
D-07 (6:00). Logout → login smkn1_kasir01 → Transaksi borongan.
D-08 (7:00). Mode Kredit → struk piutang → badge notif bertambah.
D-09 (8:00). Riwayat → filter + batalkan (stok kembali).
D-10 (9:00). Laporan Dari–Sampai + Export CSV → Notifikasi → tutup
  dengan Settings tema. Siapkan jawaban Q-01–Q-15.

BAGIAN 30 — RISIKO & MITIGASI (tabel lampiran bila diminta pembimbing)
R-01. Risiko: kasir salah input qty borongan. Mitigasi: jepit 1..stok
  + konfirmasi struk sebelum bayar.
R-02. Risiko: produk terhapus tak sengaja. Mitigasi: syarat stok-0 +
  modal peringatan + soft delete.
R-03. Risiko: logout tak sengaja di meja kasir. Mitigasi: modal
  konfirmasi + overlay tak bisa menutup.
R-04. Risiko: data sekolah tercampur. Mitigasi: isolasi id_sekolah
  dari session + 403 lintas tenant.
R-05. Risiko: lupa bayar langganan. Mitigasi: notif H-7 + mingguan
  pasca-tempo + tombol Perpanjang.
R-06. Risiko: piutang tak tertagih. Mitigasi: pengingat H+2 & tiap
  7 hari + struk sisa piutang.
R-07. Risiko: password lemah demo (123). Mitigasi: ganti wajib di
  produksi + bcrypt + cooldown identitas.

BAGIAN 31 — PERISTILAHAN KONSISTEN (pakai kiri, jangan kanan)
W-01. Gunakan "kasir" (bukan "operator/kasirku").
W-02. Gunakan "belum bayar" (bukan "hutang/unggakan").
W-03. Gunakan "kembalian" (bukan "kembali/uang kembali").
W-04. Gunakan "langganan" (bukan "subskripsi/subscription").
W-05. Gunakan "jatu tempo / jatuh tempo" konsisten satu ejaan.
W-06. Gunakan "struk" (bukan "nota/slip" bergantian).
W-07. Gunakan "stok menipis / habis" (bukan "low stock/out of stock").
W-08. Gunakan "dasbor" boleh, tetapi judul BAB tetap "Dashboard".
W-09. Gunakan "modal/popup" konsisten (pilih satu per naskah).
W-10. Gunakan "Rp41.177.350" format titik-ribu tanpa spasi.
W-11. Gunakan "H+2", "H-7", "H+30" untuk hitungan hari.
W-12. Gunakan "super admin" huruf kecil (kecuali awal kalimat).

BAGIAN 32 — LEMBAR PENGESAHAN (template, isi manual)
E-01. Judul: sama persis dengan halaman judul.
E-02. Tabel: Nama / NIS / Kelas / Tanda tangan untuk tiap anggota.
E-03. Kolom pembimbing: Nama [ISI], NIP [ISI], tanda tangan.
E-04. Kolom penguji: Nama [ISI], NIP [ISI], tanda tangan.
E-05. Tempat-tanggal: Tasikmalaya, [ISI TANGGAL] 2026/2027.
E-06. Minta Claude: "buatkan teks pengesahan formal 1 halaman".

BAGIAN 33 — KATA PENGANTAR (template isi, 1 halaman)
K-01. Paragraf 1: puji syukur.
K-02. Paragraf 2: tujuan (tugas akhir / hasil karya) + terima kasih
  pembimbing, sekolah, orang tua, teman.
K-03. Paragraf 3: sadar kekurangan + mohon kritik saran.
K-04. Minta Claude: "tuliskan kata pengantar dari kerangka ini".

BAGIAN 34 — DAFTAR PUSTAKA (template, sesuaikan yang dipakai)
P-01. Dokumentasi Laravel (laravel.com/docs).
P-02. Dokumentasi Vue.js (vuejs.org).
P-03. Dokumentasi Inertia.js (inertiajs.com).
P-04. Dokumentasi MySQL.
P-05. Buku/metode RPL sekolah [ISI bila ada].
P-06. Minta Claude memformat gaya APA sederhana.

BAGIAN 35 — ESTIMASI HALAMAN (total ±20 isi + i sampul, patuhi)
H-01. Sampul + pengesahan + kata pengantar: i–iii.
H-02. Daftar isi + daftar gambar + daftar tabel: iv–v.
H-03. BAB I: 2 halaman (5 gambar).
H-04. BAB II: 2 halaman (3 gambar).
H-05. BAB III: 3 halaman (6 gambar).
H-06. BAB IV: 1,5 halaman (2 gambar).
H-07. BAB V: 3 halaman (10 gambar).
H-08. BAB VI: 2 halaman (3 gambar).
H-09. BAB VII: 2 halaman (4 gambar).
H-10. BAB VIII: 1,5 halaman (2 gambar).
H-11. BAB IX: 2 halaman (5 gambar).
H-12. BAB X + lampiran: 2 halaman.
H-13. Bila kepanjangan: pangkas contoh, jangan buang gambar.
H-14. Bila kependekan: tambah paragraf "mengapa dirancang begitu".

BAGIAN 36 — DAFTAR GAMBAR & TABEL TERPUSAT (wajib di depan)
D-01. Setelah daftar isi: "DAFTAR GAMBAR" berisi Gambar 1.1–9.5 +
  halaman (55 entri FOTO-01..55).
D-02. Lalu "DAFTAR TABEL": TBL-01..TBL-06 + tabel B-01..B-30
  (diringkas satu tabel uji).
D-03. Penomoran mengikuti BAB (reset tiap BAB).

BAGIAN 37 — KALIMAT TRANSISI ANTAR BAB (pakai di akhir tiap BAB)
T-01. I→II: "Setelah gerbang aman, kini masuk ke ruang kendalinya."
T-02. II→III: "Dari memantau, kini ke bekerja: meja kasir."
T-03. III→IV: "Transaksi selesai belum berarti selesai dicatat."
T-04. IV→V: "Di balik setiap transaksi ada master data yang rapi."
T-05. V→VI: "Barang dan user menumpang di atas bangunan bernama
  sekolah — bangunan yang berbayar."
T-06. VI→VII: "Sekolah dikelola lewat akun; akun dikelola lewat
  halaman Pengguna."
T-07. VII→VIII: "Akun yang tertib menghasilkan angka; angka dibaca
  di Laporan."
T-08. VIII→IX: "Angka yang janggal tidak menunggu dibaca — sistem
  yang mengetuk lewat notifikasi."
T-09. IX→X: "Semua fitur di atas bermuara pada satu pertanyaan:
  layakkah sistem ini dipakai?"
T-10. X→Lampiran: "Bukti-bukti pendukung dirangkum sebagai berikut."

BAGIAN 38 — KONTROL KUALITAS JAWABAN (Claude wajib cek sebelum kirim)
Q-01. Apakah semua BAB  I–X tertulis tanpa ada yang diskip?
Q-02. Apakah setiap subbab diakhiri SATU baris Gambar X.Y?
Q-03. Apakah FOTO-01..FOTO-55 semuanya terpetakan ke subbab?
Q-04. Apakah angka kesimpulan = angka BAGIAN 5?
Q-05. Apakah ada kata terlarang BAGIAN 6? Hapus bila ada.
Q-06. Apakah nama aplikasi selalu NIXA (bukan KlikNota)?
Q-07. Apakah password contoh hanya "123"?
Q-08. Apakah placeholder [ISI ...] ada untuk nama/kelas/link?
Q-09. Apakah footer format benar di tiap halaman?
Q-10. Apakah tabel TBL-01..TBL-06 semuanya ada?
Q-11. Apakah glosarium G-01..G-25 dipakai konsisten?
Q-12. Apakah total jawaban setara ±20 halaman (±6000–8000 kata)?
Q-13. Bila jawaban terpotong limit, akhiri dengan "—BERSAMBUNG:
  sebutkan 'lanjutkan'—" tepat di batas subbab (jangan tengah kalimat).
Q-14. Prioritas bila harus memendekkan: pangkas contoh, JANGAN buang
  gambar, tabel, atau BAB.
Q-15. Awali jawaban persis dengan baris: "LAPORAN DOKUMENTASI
  PROYEK NIXA".
Q-16. Akhiri jawaban persis dengan checklist BAGIAN 10 butir 172.

BAGIAN 10 — ATURAN FORMAT DAN REVISI
165. Judul BAB rata tengah, huruf kapital.
166. Nomor gambar berurutan per BAB (Gambar 3.1, 3.2, ...).
167. Footer tiap halaman: "NIXA [ISI KELAS] 2026/2027".
168. Tabel dan daftar memakai gaya rapi konsisten.
169. Istilah asing dicetak miring (role-based access control, real-time).
170. Jangan sebut kata "KlikNota" di mana pun; nama aplikasi hanya NIXA.
171. Jangan sebut password selain "123" untuk akun contoh.
172. Setelah naskah jadi, tutup dengan checklist: "Siap direvisi — sebutkan
    nomor BAB yang ingin diubah, ditambah, atau dibuang."
