<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel notifikasi persisten (tb_notifikasi) + penanda aktivasi sekolah.
 *
 * - tb_notifikasi: target per sekolah / per user / per role, dengan status
 *   dibaca (is_read) agar badge merah & highlight "BARU" hilang setelah diklik
 *   dan tetap hilang setelah refresh.
 * - tb_sekolah.activated_at: titik awal timer langganan 30 hari super admin
 *   (diisi saat create & saat diaktifkan ulang via toggle).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tb_notifikasi', function (Blueprint $table) {
            $table->increments('id_notifikasi');
            $table->unsignedInteger('id_sekolah')->nullable();
            $table->unsignedInteger('id_user')->nullable()->comment('null = role-wide dalam sekolah');
            $table->string('role_target', 20)->nullable()->comment('kasir|admin|super admin|null=all');
            $table->string('tipe', 50)->comment('kredit_2hari|kredit_mingguan|omzet_harian|omzet_mingguan|stok_menipis|stok_habis|sekolah_hampir_30hari|sekolah_30hari');
            $table->string('judul', 200);
            $table->text('pesan')->nullable();
            $table->string('ref_type', 30)->nullable()->comment('penjualan|barang|sekolah');
            $table->unsignedInteger('ref_id')->nullable();
            $table->string('ref_key', 100)->nullable()->comment('kunci idempoten: cth penjualan:12:hari-7');
            $table->string('href', 150)->nullable()->comment('link frontend saat diklik');
            $table->tinyInteger('is_read')->default(0);
            $table->timestamp('read_at')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->unique(['id_sekolah', 'id_user', 'role_target', 'tipe', 'ref_key'], 'uniq_notif_idempoten');
            $table->index(['id_sekolah', 'role_target', 'is_read']);
            $table->index(['id_user', 'is_read']);

            $table->foreign('id_sekolah')->references('id_sekolah')->on('tb_sekolah')->nullOnDelete();
            $table->foreign('id_user')->references('id_user')->on('tb_user')->nullOnDelete();
        });

        Schema::table('tb_sekolah', function (Blueprint $table) {
            $table->timestamp('activated_at')->nullable()->after('created_at');
        });

        // Isi activated_at awal dari created_at agar timer lama tetap jalan.
        try {
            \DB::statement('UPDATE tb_sekolah SET activated_at = created_at WHERE activated_at IS NULL');
        } catch (\Throwable $e) {
            // abaikan bila driver tidak mendukung
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_notifikasi');

        Schema::table('tb_sekolah', function (Blueprint $table) {
            $table->dropColumn('activated_at');
        });
    }
};
