<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cooldown ganti profil tb_user:
 * - nama_lengkap: jeda 7 hari setelah ganti.
 * - username: jeda 30 hari setelah ganti.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tb_user', function (Blueprint $table) {
            $table->timestamp('nama_lengkap_changed_at')->nullable()->after('nama_lengkap');
            $table->timestamp('username_changed_at')->nullable()->after('nama_lengkap_changed_at');
        });
    }

    public function down(): void
    {
        Schema::table('tb_user', function (Blueprint $table) {
            $table->dropColumn(['nama_lengkap_changed_at', 'username_changed_at']);
        });
    }
};
