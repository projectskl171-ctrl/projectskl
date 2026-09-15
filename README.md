-- 1. tb_sekolah
CREATE TABLE tb_sekolah (
    id_sekolah INT(11) AUTO_INCREMENT PRIMARY KEY,
    kode_sekolah VARCHAR(20),
    nama_sekolah VARCHAR(150),
    alamat_sekolah TEXT,
    website VARCHAR(200),
    is_active TINYINT(1),
    created_at TIMESTAMP NULL DEFAULT NULL
);

-- 2. roles
CREATE TABLE roles (
    id_role INT(11) AUTO_INCREMENT PRIMARY KEY,
    nama_role ENUM('super admin', 'admin', 'kasir')
);

-- 3. tb_user
CREATE TABLE tb_user (
    id_user INT(11) AUTO_INCREMENT PRIMARY KEY,
    id_sekolah INT(11),
    id_role INT(11),
    username VARCHAR(50),
    password VARCHAR(255),
    nama_lengkap VARCHAR(100),
    is_active TINYINT(1),
    created_at TIMESTAMP NULL DEFAULT NULL,
    created_by INT(11),
    updated_at TIMESTAMP NULL DEFAULT NULL,
    updated_by INT(11),
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    deleted_by INT(11),
    FOREIGN KEY (id_sekolah) REFERENCES tb_sekolah(id_sekolah),
    FOREIGN KEY (id_role) REFERENCES roles(id_role)
);

-- 4. tb_kelompok_kategori
CREATE TABLE tb_kelompok_kategori (
    id_kelompok INT(11) AUTO_INCREMENT PRIMARY KEY,
    id_sekolah INT(11),
    nama_kelompok VARCHAR(100),
    created_at TIMESTAMP NULL DEFAULT NULL,
    created_by INT(11),
    FOREIGN KEY (id_sekolah) REFERENCES tb_sekolah(id_sekolah)
);

-- 5. tb_kategori
CREATE TABLE tb_kategori (
    id_kategori INT(11) AUTO_INCREMENT PRIMARY KEY,
    id_kelompok INT(11),
    nama VARCHAR(100),
    created_at TIMESTAMP NULL DEFAULT NULL,
    created_by INT(11),
    updated_at TIMESTAMP NULL DEFAULT NULL,
    updated_by INT(11),
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    deleted_by INT(11),
    is_delete TINYINT(1),
    FOREIGN KEY (id_kelompok) REFERENCES tb_kelompok_kategori(id_kelompok)
);

-- 6. tb_supplier
CREATE TABLE tb_supplier (
    id_supplier INT(11) AUTO_INCREMENT PRIMARY KEY,
    id_sekolah INT(11),
    nama VARCHAR(100),
    no_telepon VARCHAR(20),
    alamat_supplier TEXT,
    created_at TIMESTAMP NULL DEFAULT NULL,
    created_by INT(11),
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    deleted_by INT(11),
    is_delete TINYINT(1),
    FOREIGN KEY (id_sekolah) REFERENCES tb_sekolah(id_sekolah)
);

-- 7. tb_barang
CREATE TABLE tb_barang (
    id_barang INT(11) AUTO_INCREMENT PRIMARY KEY,
    id_sekolah INT(11),
    barcode VARCHAR(50),
    nama VARCHAR(150),
    id_kategori INT(11),
    id_kelompok_kategori INT(11),
    id_supplier INT(11),
    satuan VARCHAR(20),
    harga_beli DECIMAL(12,2),
    harga_jual DECIMAL(12,2),
    stok INT(11),
    is_active TINYINT(1),
    created_at TIMESTAMP NULL DEFAULT NULL,
    created_by INT(11),
    updated_at TIMESTAMP NULL DEFAULT NULL,
    updated_by INT(11),
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    deleted_by INT(11),
    is_delete TINYINT(1),
    FOREIGN KEY (id_sekolah) REFERENCES tb_sekolah(id_sekolah),
    FOREIGN KEY (id_kategori) REFERENCES tb_kategori(id_kategori),
    FOREIGN KEY (id_kelompok_kategori) REFERENCES tb_kelompok_kategori(id_kelompok),
    FOREIGN KEY (id_supplier) REFERENCES tb_supplier(id_supplier)
);

-- 8. tb_pembelian
CREATE TABLE tb_pembelian (
    id_pembelian INT(11) AUTO_INCREMENT PRIMARY KEY,
    id_sekolah INT(11),
    id_supplier INT(11),
    id_user INT(11),
    nomor_faktur VARCHAR(50),
    tanggal_faktur DATETIME,
    total_bayar DECIMAL(14,2),
    status_pembelian ENUM('draft', 'selesai'),
    jenis_transaksi ENUM('tunai', 'kredit'),
    cara_bayar VARCHAR(50),
    note TEXT,
    created_at TIMESTAMP NULL DEFAULT NULL,
    created_by INT(11),
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    deleted_by INT(11),
    is_delete TINYINT(1),
    FOREIGN KEY (id_sekolah) REFERENCES tb_sekolah(id_sekolah),
    FOREIGN KEY (id_supplier) REFERENCES tb_supplier(id_supplier),
    FOREIGN KEY (id_user) REFERENCES tb_user(id_user)
);

-- 9. tb_detail_pembelian
CREATE TABLE tb_detail_pembelian (
    id_detail_pembelian INT(11) AUTO_INCREMENT PRIMARY KEY,
    id_pembelian INT(11),
    id_barang INT(11),
    satuan VARCHAR(20),
    jumlah INT(11),
    harga_beli DECIMAL(12,2),
    subtotal DECIMAL(14,2),
    FOREIGN KEY (id_pembelian) REFERENCES tb_pembelian(id_pembelian),
    FOREIGN KEY (id_barang) REFERENCES tb_barang(id_barang)
);

-- 10. tb_kelompok_pelanggan
CREATE TABLE tb_kelompok_pelanggan (
    id_kelompok_pelanggan INT(11) AUTO_INCREMENT PRIMARY KEY,
    id_sekolah INT(11),
    nama_kelompok VARCHAR(50),
    FOREIGN KEY (id_sekolah) REFERENCES tb_sekolah(id_sekolah)
);

-- 11. tb_pelanggan
CREATE TABLE tb_pelanggan (
    id_pelanggan INT(11) AUTO_INCREMENT PRIMARY KEY,
    id_kelompok_pelanggan INT(11),
    nama_pelanggan VARCHAR(150),
    telepon VARCHAR(20),
    alamat TEXT,
    created_at TIMESTAMP NULL DEFAULT NULL,
    created_by INT(11),
    updated_at TIMESTAMP NULL DEFAULT NULL,
    updated_by INT(11),
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    deleted_by INT(11),
    is_delete TINYINT(1),
    FOREIGN KEY (id_kelompok_pelanggan) REFERENCES tb_kelompok_pelanggan(id_kelompok_pelanggan)
);

-- 12. tb_penjualan
CREATE TABLE tb_penjualan (
    id_penjualan INT(11) AUTO_INCREMENT PRIMARY KEY,
    id_sekolah INT(11),
    id_user INT(11),
    id_pelanggan INT(11),
    tanggal_penjualan DATETIME,
    total_faktur DECIMAL(14,2),
    total_bayar DECIMAL(14,2),
    kembalian DECIMAL(14,2),
    status_pembayaran ENUM('sudah bayar', 'belum bayar'),
    jenis_transaksi ENUM('tunai', 'kredit'),
    cara_bayar VARCHAR(50),
    note TEXT,
    created_at TIMESTAMP NULL DEFAULT NULL,
    created_by INT(11),
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    deleted_by INT(11),
    is_delete TINYINT(1),
    FOREIGN KEY (id_sekolah) REFERENCES tb_sekolah(id_sekolah),
    FOREIGN KEY (id_user) REFERENCES tb_user(id_user),
    FOREIGN KEY (id_pelanggan) REFERENCES tb_pelanggan(id_pelanggan)
);

-- 13. tb_detail_penjualan
CREATE TABLE tb_detail_penjualan (
    id_detail_penjualan INT(11) AUTO_INCREMENT PRIMARY KEY,
    id_penjualan INT(11),
    id_barang INT(11),
    jumlah_barang INT(11),
    harga_beli DECIMAL(12,2),
    harga_jual DECIMAL(12,2),
    diskon_tipe ENUM('persen', 'nominal'),
    diskon_nilai DECIMAL(12,2),
    diskon_nominal DECIMAL(12,2),
    subtotal DECIMAL(14,2),
    FOREIGN KEY (id_penjualan) REFERENCES tb_penjualan(id_penjualan),
    FOREIGN KEY (id_barang) REFERENCES tb_barang(id_barang)
);