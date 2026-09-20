<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Skema POS multi-sekolah (13 tabel) — mirror 1:1 dari db_pos.sql guru.
 *
 * Perubahan teknis kecil yang MUTLAK diperlukan (bukan perubahan bisnis):
 * - `tb_sekolah.kode_sekolah` diberi UNIQUE agar klausa
 *   `ON DUPLICATE KEY UPDATE` pada dummy SQL berfungsi.
 * - `tb_barang` diberi UNIQUE composite `(barcode, id_sekolah)` sesuai
 *   komentar dummy SQL ("unique key (barcode, id_sekolah)").
 * Tidak ada tabel/kolom bisnis yang ditambah atau dihapus.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. tb_sekolah
        Schema::create('tb_sekolah', function (Blueprint $table) {
            $table->increments('id_sekolah');
            $table->string('kode_sekolah', 20)->unique();
            $table->string('nama_sekolah', 150)->nullable();
            $table->text('alamat_sekolah')->nullable();
            $table->string('website', 200)->nullable();
            $table->tinyInteger('is_active')->default(1);
            $table->timestamp('created_at')->nullable();
        });

        // 2. roles
        Schema::create('roles', function (Blueprint $table) {
            $table->increments('id_role');
            $table->enum('nama_role', ['super admin', 'admin', 'kasir']);
        });

        // 3. tb_user
        Schema::create('tb_user', function (Blueprint $table) {
            $table->increments('id_user');
            $table->unsignedInteger('id_sekolah')->nullable();
            $table->unsignedInteger('id_role')->nullable();
            $table->string('username', 50);
            $table->string('password', 255);
            $table->string('nama_lengkap', 100)->nullable();
            $table->tinyInteger('is_active')->default(1);
            $table->timestamp('created_at')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->unsignedInteger('deleted_by')->nullable();

            $table->index(['id_sekolah', 'username']);
            $table->foreign('id_sekolah')->references('id_sekolah')->on('tb_sekolah')->nullOnDelete();
            $table->foreign('id_role')->references('id_role')->on('roles')->nullOnDelete();
        });

        // 4. tb_kelompok_kategori
        Schema::create('tb_kelompok_kategori', function (Blueprint $table) {
            $table->increments('id_kelompok');
            $table->unsignedInteger('id_sekolah')->nullable();
            $table->string('nama_kelompok', 100);
            $table->timestamp('created_at')->nullable();
            $table->unsignedInteger('created_by')->nullable();

            $table->foreign('id_sekolah')->references('id_sekolah')->on('tb_sekolah')->nullOnDelete();
        });

        // 5. tb_kategori
        Schema::create('tb_kategori', function (Blueprint $table) {
            $table->increments('id_kategori');
            $table->unsignedInteger('id_kelompok')->nullable();
            $table->string('nama', 100);
            $table->timestamp('created_at')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->unsignedInteger('deleted_by')->nullable();
            $table->tinyInteger('is_delete')->default(0);

            $table->foreign('id_kelompok')->references('id_kelompok')->on('tb_kelompok_kategori')->nullOnDelete();
        });

        // 6. tb_supplier
        Schema::create('tb_supplier', function (Blueprint $table) {
            $table->increments('id_supplier');
            $table->unsignedInteger('id_sekolah')->nullable();
            $table->string('nama', 100);
            $table->string('no_telepon', 20)->nullable();
            $table->text('alamat_supplier')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->unsignedInteger('deleted_by')->nullable();
            $table->tinyInteger('is_delete')->default(0);

            $table->foreign('id_sekolah')->references('id_sekolah')->on('tb_sekolah')->nullOnDelete();
        });

        // 7. tb_barang
        Schema::create('tb_barang', function (Blueprint $table) {
            $table->increments('id_barang');
            $table->unsignedInteger('id_sekolah')->nullable();
            $table->string('barcode', 50);
            $table->string('nama', 150);
            $table->unsignedInteger('id_kategori')->nullable();
            $table->unsignedInteger('id_kelompok_kategori')->nullable();
            $table->unsignedInteger('id_supplier')->nullable();
            $table->string('satuan', 20)->nullable();
            $table->decimal('harga_beli', 12, 2)->default(0);
            $table->decimal('harga_jual', 12, 2)->default(0);
            $table->integer('stok')->default(0);
            $table->tinyInteger('is_active')->default(1);
            $table->timestamp('created_at')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->unsignedInteger('deleted_by')->nullable();
            $table->tinyInteger('is_delete')->default(0);

            $table->unique(['barcode', 'id_sekolah']);
            $table->foreign('id_sekolah')->references('id_sekolah')->on('tb_sekolah')->nullOnDelete();
            $table->foreign('id_kategori')->references('id_kategori')->on('tb_kategori')->nullOnDelete();
            $table->foreign('id_kelompok_kategori')->references('id_kelompok')->on('tb_kelompok_kategori')->nullOnDelete();
            $table->foreign('id_supplier')->references('id_supplier')->on('tb_supplier')->nullOnDelete();
        });

        // 8. tb_pembelian
        Schema::create('tb_pembelian', function (Blueprint $table) {
            $table->increments('id_pembelian');
            $table->unsignedInteger('id_sekolah')->nullable();
            $table->unsignedInteger('id_supplier')->nullable();
            $table->unsignedInteger('id_user')->nullable();
            $table->string('nomor_faktur', 50)->nullable();
            $table->dateTime('tanggal_faktur')->nullable();
            $table->decimal('total_bayar', 14, 2)->default(0);
            $table->enum('status_pembelian', ['draft', 'selesai'])->default('draft');
            $table->enum('jenis_transaksi', ['tunai', 'kredit'])->default('tunai');
            $table->string('cara_bayar', 50)->nullable();
            $table->text('note')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->unsignedInteger('deleted_by')->nullable();
            $table->tinyInteger('is_delete')->default(0);

            $table->index('nomor_faktur');
            $table->foreign('id_sekolah')->references('id_sekolah')->on('tb_sekolah')->nullOnDelete();
            $table->foreign('id_supplier')->references('id_supplier')->on('tb_supplier')->nullOnDelete();
            $table->foreign('id_user')->references('id_user')->on('tb_user')->nullOnDelete();
        });

        // 9. tb_detail_pembelian
        Schema::create('tb_detail_pembelian', function (Blueprint $table) {
            $table->increments('id_detail_pembelian');
            $table->unsignedInteger('id_pembelian')->nullable();
            $table->unsignedInteger('id_barang')->nullable();
            $table->string('satuan', 20)->nullable();
            $table->integer('jumlah')->default(0);
            $table->decimal('harga_beli', 12, 2)->default(0);
            $table->decimal('subtotal', 14, 2)->default(0);

            $table->foreign('id_pembelian')->references('id_pembelian')->on('tb_pembelian')->cascadeOnDelete();
            $table->foreign('id_barang')->references('id_barang')->on('tb_barang')->nullOnDelete();
        });

        // 10. tb_kelompok_pelanggan
        Schema::create('tb_kelompok_pelanggan', function (Blueprint $table) {
            $table->increments('id_kelompok_pelanggan');
            $table->unsignedInteger('id_sekolah')->nullable();
            $table->string('nama_kelompok', 50);

            $table->foreign('id_sekolah')->references('id_sekolah')->on('tb_sekolah')->nullOnDelete();
        });

        // 11. tb_pelanggan
        Schema::create('tb_pelanggan', function (Blueprint $table) {
            $table->increments('id_pelanggan');
            $table->unsignedInteger('id_kelompok_pelanggan')->nullable();
            $table->string('nama_pelanggan', 150);
            $table->string('telepon', 20)->nullable();
            $table->text('alamat')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->unsignedInteger('deleted_by')->nullable();
            $table->tinyInteger('is_delete')->default(0);

            $table->foreign('id_kelompok_pelanggan')->references('id_kelompok_pelanggan')->on('tb_kelompok_pelanggan')->nullOnDelete();
        });

        // 12. tb_penjualan
        Schema::create('tb_penjualan', function (Blueprint $table) {
            $table->increments('id_penjualan');
            $table->unsignedInteger('id_sekolah')->nullable();
            $table->unsignedInteger('id_user')->nullable();
            $table->unsignedInteger('id_pelanggan')->nullable();
            $table->dateTime('tanggal_penjualan')->nullable();
            $table->decimal('total_faktur', 14, 2)->default(0);
            $table->decimal('total_bayar', 14, 2)->default(0);
            $table->decimal('kembalian', 14, 2)->default(0);
            $table->enum('status_pembayaran', ['sudah bayar', 'belum bayar'])->default('sudah bayar');
            $table->enum('jenis_transaksi', ['tunai', 'kredit'])->default('tunai');
            $table->string('cara_bayar', 50)->nullable();
            $table->text('note')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->unsignedInteger('deleted_by')->nullable();
            $table->tinyInteger('is_delete')->default(0);

            $table->foreign('id_sekolah')->references('id_sekolah')->on('tb_sekolah')->nullOnDelete();
            $table->foreign('id_user')->references('id_user')->on('tb_user')->nullOnDelete();
            $table->foreign('id_pelanggan')->references('id_pelanggan')->on('tb_pelanggan')->nullOnDelete();
        });

        // 13. tb_detail_penjualan
        Schema::create('tb_detail_penjualan', function (Blueprint $table) {
            $table->increments('id_detail_penjualan');
            $table->unsignedInteger('id_penjualan')->nullable();
            $table->unsignedInteger('id_barang')->nullable();
            $table->integer('jumlah_barang')->default(0);
            $table->decimal('harga_beli', 12, 2)->default(0);
            $table->decimal('harga_jual', 12, 2)->default(0);
            $table->enum('diskon_tipe', ['persen', 'nominal'])->default('persen');
            $table->decimal('diskon_nilai', 12, 2)->default(0);
            $table->decimal('diskon_nominal', 12, 2)->default(0);
            $table->decimal('subtotal', 14, 2)->default(0);

            $table->foreign('id_penjualan')->references('id_penjualan')->on('tb_penjualan')->cascadeOnDelete();
            $table->foreign('id_barang')->references('id_barang')->on('tb_barang')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_detail_penjualan');
        Schema::dropIfExists('tb_penjualan');
        Schema::dropIfExists('tb_pelanggan');
        Schema::dropIfExists('tb_kelompok_pelanggan');
        Schema::dropIfExists('tb_detail_pembelian');
        Schema::dropIfExists('tb_pembelian');
        Schema::dropIfExists('tb_barang');
        Schema::dropIfExists('tb_supplier');
        Schema::dropIfExists('tb_kategori');
        Schema::dropIfExists('tb_kelompok_kategori');
        Schema::dropIfExists('tb_user');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('tb_sekolah');
    }
};
