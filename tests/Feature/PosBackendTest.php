<?php

use App\Models\TbBarang;
use App\Models\TbPembelian;
use App\Models\TbPenjualan;
use App\Models\TbUser;
use Database\Seeders\DatabaseSeeder;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

function posUser(string $username): TbUser
{
    return TbUser::with('role')->where('username', $username)->firstOrFail();
}

// ---------- authentication & seed ----------

test('seeded users can login with password "password"', function () {
    foreach (['sch001_superadmin', 'sch001_admin', 'sch001_kasir'] as $username) {
        $response = $this->postJson('/api/auth/login', ['username' => $username, 'password' => '123']);
        $response->assertOk()->assertJsonPath('data.username', $username);
        $this->postJson('/api/auth/logout')->assertOk();
    }
});

test('user responses never expose password hashes', function () {
    $admin = posUser('sch001_superadmin');
    $this->actingAs($admin);

    $this->getJson('/api/users')->assertOk()
        ->assertJsonMissingPath('data.0.password');
    $this->getJson('/api/auth/me')->assertOk()
        ->assertJsonMissingPath('data.password');
});

// ---------- tenant isolation ----------

test('admin only sees own school products', function () {
    $admin = posUser('sch001_admin');
    $this->actingAs($admin);

    $res = $this->getJson('/api/produk?per_page=200')->assertOk();
    $this->assertNotEmpty($res->json('data'));
    foreach ($res->json('data') as $row) {
        $this->assertSame($admin->id_sekolah, $row['id_sekolah']);
    }

    // Produk sekolah lain tidak bisa dibuka langsung.
    $other = TbBarang::where('id_sekolah', '!=', $admin->id_sekolah)->firstOrFail();
    $this->getJson('/api/produk/'.$other->id_barang)->assertNotFound();
});

test('cross-school foreign keys are rejected on product create', function () {
    $admin = posUser('sch001_admin');
    $this->actingAs($admin);

    $supplierLain = \App\Models\TbSupplier::where('id_sekolah', '!=', $admin->id_sekolah)->firstOrFail();
    $kategoriSendiri = \App\Models\TbKategori::whereHas('kelompok', fn ($q) => $q->where('id_sekolah', $admin->id_sekolah))->firstOrFail();

    $this->postJson('/api/produk', [
        'barcode' => 'X-ISOLATION-1', 'nama' => 'Uji Isolasi',
        'id_kategori' => $kategoriSendiri->id_kategori,
        'id_kelompok_kategori' => $kategoriSendiri->id_kelompok,
        'id_supplier' => $supplierLain->id_supplier,
        'harga_beli' => 1000, 'harga_jual' => 1500, 'stok' => 5,
    ])->assertStatus(422);
});

// ---------- role authorization ----------

test('kasir can not manage products or users', function () {
    $kasir = posUser('sch001_kasir');
    $this->actingAs($kasir);

    $this->postJson('/api/produk', [])->assertForbidden();
    $this->getJson('/api/users')->assertForbidden();
    $this->postJson('/api/supplier', [])->assertForbidden();
});

test('admin can not grant super admin role', function () {
    $admin = posUser('sch001_admin');
    $this->actingAs($admin);

    $superRole = \App\Models\Role::where('nama_role', 'super admin')->firstOrFail();
    $this->postJson('/api/users', [
        'username' => 'evil_admin', 'nama_lengkap' => 'Evil',
        'password' => '123', 'id_role' => $superRole->id_role,
    ])->assertStatus(422);
});

test('admin created users are forced into own school', function () {
    $admin = posUser('sch001_admin');
    $this->actingAs($admin);

    $kasirRole = \App\Models\Role::where('nama_role', 'kasir')->firstOrFail();
    $otherSchool = \App\Models\TbSekolah::where('id_sekolah', '!=', $admin->id_sekolah)->firstOrFail();

    $res = $this->postJson('/api/users', [
        'username' => 'kasir_baru_t1', 'nama_lengkap' => 'Kasir Baru',
        'password' => '123', 'id_role' => $kasirRole->id_role,
        'id_sekolah' => $otherSchool->id_sekolah, // percobaan escalation
    ])->assertCreated();

    $this->assertSame($admin->id_sekolah, $res->json('data.id_sekolah'));
});

// ---------- produk & supplier CRUD ----------

test('produk CRUD persists to database', function () {
    $admin = posUser('sch001_admin');
    $this->actingAs($admin);

    $kategori = \App\Models\TbKategori::whereHas('kelompok', fn ($q) => $q->where('id_sekolah', $admin->id_sekolah))->firstOrFail();
    $supplier = \App\Models\TbSupplier::where('id_sekolah', $admin->id_sekolah)->firstOrFail();

    $created = $this->postJson('/api/produk', [
        'barcode' => 'CRUD-001', 'nama' => 'Produk CRUD',
        'id_kategori' => $kategori->id_kategori,
        'id_kelompok_kategori' => $kategori->id_kelompok,
        'id_supplier' => $supplier->id_supplier,
        'satuan' => 'pcs', 'harga_beli' => 5000, 'harga_jual' => 7000, 'stok' => 20,
    ])->assertCreated()->json('data');

    $this->assertDatabaseHas('tb_barang', ['id_barang' => $created['id_barang'], 'nama' => 'Produk CRUD']);

    $this->putJson('/api/produk/'.$created['id_barang'], [
        'barcode' => 'CRUD-001', 'nama' => 'Produk CRUD Updated',
        'id_kategori' => $kategori->id_kategori,
        'id_kelompok_kategori' => $kategori->id_kelompok,
        'id_supplier' => $supplier->id_supplier,
        'harga_beli' => 5500, 'harga_jual' => 7500, 'stok' => 25,
    ])->assertOk();

    $this->assertDatabaseHas('tb_barang', ['id_barang' => $created['id_barang'], 'nama' => 'Produk CRUD Updated', 'stok' => 25]);

    $this->deleteJson('/api/produk/'.$created['id_barang'])->assertOk();
    $this->assertDatabaseHas('tb_barang', ['id_barang' => $created['id_barang'], 'is_delete' => 1]);
});

test('supplier CRUD persists and stays in own school', function () {
    $admin = posUser('sch001_admin');
    $this->actingAs($admin);

    $created = $this->postJson('/api/supplier', [
        'nama' => 'Supplier Uji', 'no_telepon' => '0811', 'alamat_supplier' => 'Jl. Uji',
    ])->assertCreated()->json('data');

    $this->assertSame($admin->id_sekolah, $created['id_sekolah']);
    $this->assertDatabaseHas('tb_supplier', ['id_supplier' => $created['id_supplier'], 'nama' => 'Supplier Uji']);
});

// ---------- pelanggan ----------

test('pelanggan CRUD validates kelompok tenant', function () {
    $kasir = posUser('sch001_kasir');
    $this->actingAs($kasir);

    $kpLain = \App\Models\TbKelompokPelanggan::where('id_sekolah', '!=', $kasir->id_sekolah)->firstOrFail();
    $this->postJson('/api/pelanggan', [
        'id_kelompok_pelanggan' => $kpLain->id_kelompok_pelanggan,
        'nama_pelanggan' => 'Pelanggan Alien',
    ])->assertStatus(422);

    $kpSendiri = \App\Models\TbKelompokPelanggan::where('id_sekolah', $kasir->id_sekolah)->firstOrFail();
    $created = $this->postJson('/api/pelanggan', [
        'id_kelompok_pelanggan' => $kpSendiri->id_kelompok_pelanggan,
        'nama_pelanggan' => 'Pelanggan Valid', 'telepon' => '0800',
    ])->assertCreated()->json('data');

    $this->assertDatabaseHas('tb_pelanggan', ['id_pelanggan' => $created['id_pelanggan'], 'nama_pelanggan' => 'Pelanggan Valid']);
});

// ---------- pembelian ----------

test('pembelian draft does not add stock, selesai does exactly once', function () {
    $admin = posUser('sch001_admin');
    $this->actingAs($admin);

    $supplier = \App\Models\TbSupplier::where('id_sekolah', $admin->id_sekolah)->firstOrFail();
    $barang = TbBarang::where('id_sekolah', $admin->id_sekolah)->firstOrFail();
    $stokAwal = $barang->stok;

    $created = $this->postJson('/api/pembelian', [
        'id_supplier' => $supplier->id_supplier,
        'jenis_transaksi' => 'tunai', 'cara_bayar' => 'Tunai',
        'lines' => [['id_barang' => $barang->id_barang, 'jumlah' => 10, 'harga_beli' => 9000]],
    ])->assertCreated()->json('data');

    $this->assertSame('draft', $created['status_pembelian']);
    $this->assertSame($stokAwal, $barang->fresh()->stok);

    $this->postJson('/api/pembelian/'.$created['id_pembelian'].'/selesai')->assertOk();
    $this->assertSame($stokAwal + 10, $barang->fresh()->stok);
    $this->assertSame('9000.00', (string) $barang->fresh()->harga_beli);

    // Penyelesaian kedua ditolak — stok tidak boleh ganda.
    $this->postJson('/api/pembelian/'.$created['id_pembelian'].'/selesai')->assertStatus(422);
    $this->assertSame($stokAwal + 10, $barang->fresh()->stok);

    // Pembelian selesai tidak boleh dihapus.
    $this->deleteJson('/api/pembelian/'.$created['id_pembelian'])->assertStatus(422);
    $this->assertDatabaseHas('tb_pembelian', ['id_pembelian' => $created['id_pembelian'], 'is_delete' => 0]);
});

// ---------- penjualan ----------

test('penjualan computes totals server-side and decrements stock', function () {
    $kasir = posUser('sch001_kasir');
    $this->actingAs($kasir);

    $a = TbBarang::where('id_sekolah', $kasir->id_sekolah)->orderBy('id_barang')->firstOrFail();
    $b = TbBarang::where('id_sekolah', $kasir->id_sekolah)->orderBy('id_barang')->skip(1)->firstOrFail();
    $stokA = $a->stok;
    $stokB = $b->stok;

    $expectedTotal = (float) $a->harga_jual * 2 + ((float) $b->harga_jual * 1 - round((float) $b->harga_jual * 1 * 0.1));

    $res = $this->postJson('/api/penjualan', [
        'jenis_transaksi' => 'tunai', 'cara_bayar' => 'Tunai',
        'total_bayar' => $expectedTotal + 5000, // kembalian dihitung backend
        'lines' => [
            ['id_barang' => $a->id_barang, 'qty' => 2],
            ['id_barang' => $b->id_barang, 'qty' => 1, 'diskon_persen' => 10],
        ],
    ])->assertCreated()->json('data');

    $this->assertEquals($expectedTotal, (float) $res['total_faktur']);
    $this->assertSame('sudah bayar', $res['status_pembayaran']);
    $this->assertEquals(5000, (float) $res['kembalian']);
    $this->assertSame($stokA - 2, $a->fresh()->stok);
    $this->assertSame($stokB - 1, $b->fresh()->stok);
    $this->assertSame(2, TbPenjualan::find($res['id_penjualan'])->details()->count());
    $this->assertDatabaseHas('tb_detail_penjualan', ['id_penjualan' => $res['id_penjualan'], 'id_barang' => $a->id_barang]);
});

test('penjualan rejects overstock and underpaid cash', function () {
    $kasir = posUser('sch001_kasir');
    $this->actingAs($kasir);

    $barang = TbBarang::where('id_sekolah', $kasir->id_sekolah)->firstOrFail();

    $this->postJson('/api/penjualan', [
        'jenis_transaksi' => 'tunai', 'cara_bayar' => 'Tunai',
        'total_bayar' => 999999999,
        'lines' => [['id_barang' => $barang->id_barang, 'qty' => $barang->stok + 1]],
    ])->assertStatus(422);
    $this->assertSame($barang->stok, $barang->fresh()->stok);

    $this->postJson('/api/penjualan', [
        'jenis_transaksi' => 'tunai', 'cara_bayar' => 'Tunai',
        'total_bayar' => 100, // kurang dari total
        'lines' => [['id_barang' => $barang->id_barang, 'qty' => 1]],
    ])->assertStatus(422);
});

test('kredit sale with partial payment becomes piutang', function () {
    $kasir = posUser('sch001_kasir');
    $this->actingAs($kasir);

    $barang = TbBarang::where('id_sekolah', $kasir->id_sekolah)->firstOrFail();

    $res = $this->postJson('/api/penjualan', [
        'jenis_transaksi' => 'kredit', 'cara_bayar' => 'Tempo',
        'total_bayar' => 0,
        'lines' => [['id_barang' => $barang->id_barang, 'qty' => 1]],
    ])->assertCreated()->json('data');

    $this->assertSame('belum bayar', $res['status_pembayaran']);
});

test('void restores stock and kasir can not void others transactions', function () {
    $kasir = posUser('sch001_kasir');
    $this->actingAs($kasir);

    $barang = TbBarang::where('id_sekolah', $kasir->id_sekolah)->firstOrFail();
    $stokAwal = $barang->stok;

    $trx = $this->postJson('/api/penjualan', [
        'jenis_transaksi' => 'tunai', 'cara_bayar' => 'Tunai',
        'total_bayar' => (float) $barang->harga_jual * 2 + 10000,
        'lines' => [['id_barang' => $barang->id_barang, 'qty' => 2]],
    ])->assertCreated()->json('data');

    $this->deleteJson('/api/penjualan/'.$trx['id_penjualan'])->assertOk();
    $this->assertSame($stokAwal, $barang->fresh()->stok);
    $this->assertDatabaseHas('tb_penjualan', ['id_penjualan' => $trx['id_penjualan'], 'is_delete' => 1]);

    // Transaksi sekolah lain tidak bisa di-void (404, bukan bocor).
    $otherTrx = TbPenjualan::where('id_sekolah', '!=', $kasir->id_sekolah)->first();
    if ($otherTrx) {
        $this->deleteJson('/api/penjualan/'.$otherTrx->id_penjualan)->assertNotFound();
    }
});

// ---------- dashboard, laporan, notifikasi ----------

test('dashboard returns real database numbers', function () {
    $admin = posUser('sch001_admin');
    $this->actingAs($admin);

    $res = $this->getJson('/api/dashboard')->assertOk()->json('data');

    $this->assertSame(100, $res['total_produk']);
    $this->assertGreaterThanOrEqual(0, $res['stok_menipis']);
    $this->assertArrayHasKey('omzet_7_hari', $res);
    $this->assertCount(7, $res['omzet_7_hari']);
});

test('laporan and notifikasi reflect database state', function () {
    $admin = posUser('sch001_admin');
    $this->actingAs($admin);

    $this->getJson('/api/laporan/stok')->assertOk()->assertJsonPath('ringkasan.stok_menipis', countMenipis($admin));
    $this->getJson('/api/laporan/penjualan?preset=semua')->assertOk()->assertJsonStructure(['data', 'meta', 'ringkasan']);
    $this->getJson('/api/laporan/pembelian?preset=semua')->assertOk()->assertJsonStructure(['data', 'meta', 'ringkasan']);

    $notif = $this->getJson('/api/notifikasi')->assertOk()->json();
    $this->assertIsArray($notif['data']);
});

function countMenipis(TbUser $admin): int
{
    return TbBarang::where('id_sekolah', $admin->id_sekolah)->where('is_delete', 0)->where('stok', '<=', 10)->count();
}
