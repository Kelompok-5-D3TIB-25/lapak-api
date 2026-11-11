<?php

use App\Domain\Pesanan\PenomorPesanan;
use App\Domain\Pesanan\StatusPesanan;
use App\Enums\StatusPembayaran;
use App\Models\Alamat;
use App\Models\ButirKeranjang;
use App\Models\ButirPesanan;
use App\Models\Checkout;
use App\Models\KunciIdempotensi;
use App\Models\Pembayaran;
use App\Models\Pengguna;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\Toko;
use App\Services\LayananKeranjang;

/*
| Keadaan awal setiap uji: Andi memiliki alamat dan keranjang berisi dua toko.
|   Batik Bu Sri   : 2 x Kain Batik Cap @ 185.000, ongkir 12.000 -> 382.000
|   Oleh-Oleh Wulan: 3 x Intip Goreng @ 32.500, ongkir 10.000 -> 107.500
|                                                       total bayar 489.500
*/
beforeEach(function () {
    $this->andi = Pengguna::factory()->pembeli()->create();
    $this->alamat = Alamat::factory()->for($this->andi, 'pengguna')->create([
        'nama_penerima' => 'Andi Nugroho', 'jalan' => 'Jl. Slamet Riyadi No. 12',
    ]);

    $this->sri = Pengguna::factory()->penjual()->create();
    $this->batik = Toko::factory()->for($this->sri, 'pemilik')
        ->create(['nama' => 'Batik Bu Sri', 'ongkir_rupiah' => 12_000]);
    $this->kain = Produk::factory()->for($this->batik)
        ->create(['nama' => 'Kain Batik Cap', 'harga_rupiah' => 185_000, 'stok' => 5]);

    $this->oleh = Toko::factory()->create(['nama' => 'Oleh-Oleh Wulan', 'ongkir_rupiah' => 10_000]);
    $this->intip = Produk::factory()->for($this->oleh)
        ->create(['nama' => 'Intip Goreng', 'harga_rupiah' => 32_500, 'stok' => 10]);

    $keranjang = app(LayananKeranjang::class);
    $keranjang->aturButir($this->andi, $this->kain->id, 2);
    $keranjang->aturButir($this->andi, $this->intip->id, 3);

    $this->checkout = fn (array $badan = [], string $kunci = 'checkout-uji-0000000001') => $this
        ->postJson(
            '/api/v1/checkout',
            $badan + ['alamat_id' => $this->alamat->id],
            ['Idempotency-Key' => $kunci],
        );
});

it('menolak permintaan tanpa kunci sesi, tanpa izin, atau tanpa Idempotency-Key', function () {
    $badan = ['alamat_id' => $this->alamat->id];

    $this->postJson('/api/v1/checkout', $badan)->assertUnauthorized();

    $this->sebagai(Pengguna::factory()->admin()->create());
    ($this->checkout)()->assertForbidden()->assertJsonPath('galat.kode', 'DITOLAK');

    $this->sebagai($this->andi)->postJson('/api/v1/checkout', $badan)
        ->assertUnprocessable()
        ->assertJsonPath('galat.kode', 'LP-11');

    expect(Checkout::count())->toBe(0);
});

it('melahirkan satu pesanan per toko dan satu tagihan untuk semuanya (LP-7)', function () {
    $respons = $this->sebagai($this->andi);
    $respons = ($this->checkout)()
        ->assertCreated()
        ->assertJsonPath('data.total', ['rupiah' => 489_500, 'tampilan' => 'Rp 489.500'])
        ->assertJsonPath('data.pembayaran.status', 'menunggu')
        ->assertJsonPath('data.pembayaran.jumlah.rupiah', 489_500)
        ->assertJsonCount(2, 'data.pesanan')
        ->assertJsonPath('data.pesanan.0.toko.nama', 'Batik Bu Sri')
        ->assertJsonPath('data.pesanan.0.status', 'menunggu_pembayaran')
        ->assertJsonPath('data.pesanan.0.total.rupiah', 382_000)
        ->assertJsonPath('data.pesanan.1.total.rupiah', 107_500);

    $checkout = Checkout::sole();

    expect($checkout->total_rupiah)->toBe(489_500)
        ->and($checkout->pengenal_sesi)->toBe($this->sesiUji->pengenal)
        ->and(Pembayaran::sole()->jumlah_rupiah)->toBe(489_500)
        ->and(Pesanan::count())->toBe(2)
        ->and($respons->json('data.pesanan.0.nomor'))->toMatch(PenomorPesanan::POLA)
        // Jumlah seluruh pesanan selalu sama dengan jumlah tagihannya.
        ->and((int) Pesanan::sum('total_rupiah'))->toBe(489_500);
});

it('mengurangi stok dan mengosongkan keranjang dalam langkah yang sama (LP-4)', function () {
    $this->sebagai($this->andi);
    ($this->checkout)()->assertCreated();

    expect($this->kain->refresh()->stok)->toBe(3)
        ->and($this->intip->refresh()->stok)->toBe(7)
        ->and(ButirKeranjang::count())->toBe(0);

    $this->getJson('/api/v1/keranjang')->assertJsonPath('data.pesanan', []);
});

it('mengabaikan harga, kuantitas, total, dan pembeli kiriman klien (LP-6)', function () {
    $orangLain = Pengguna::factory()->pembeli()->create();

    $this->sebagai($this->andi);

    ($this->checkout)([
        'total' => 1, 'total_rupiah' => 1, 'pembeli_id' => $orangLain->id, 'status' => 'dibayar',
        'butir' => [['produk_id' => $this->kain->id, 'kuantitas' => 1, 'harga_satuan' => 1]],
    ])
        ->assertCreated()
        ->assertJsonPath('data.total.rupiah', 489_500)
        ->assertJsonPath('data.pesanan.0.status', 'menunggu_pembayaran');

    expect(Checkout::sole()->pembeli_id)->toBe($this->andi->id)
        ->and(ButirPesanan::query()->where('produk_id', $this->kain->id)->value('kuantitas'))
        ->toBe(2);
});

it('membatalkan SELURUH checkout bila satu butir kehabisan stok', function () {
    // Di antara mengisi keranjang dan menekan bayar, stok kain tinggal satu.
    $this->kain->forceFill(['stok' => 1])->save();
    $this->sebagai($this->andi);

    ($this->checkout)()
        ->assertConflict()
        ->assertJsonPath('galat.kode', 'LP-4')
        ->assertJsonPath('galat.rincian.tersedia', 1);

    // Tidak ada yang tertulis separuh: pesanan toko kedua pun tidak lahir, stoknya utuh,
    // keranjang masih berisi, dan Idempotency-Key tidak tercatat.
    expect(Checkout::count())->toBe(0)
        ->and(Pesanan::count())->toBe(0)
        ->and(Pembayaran::count())->toBe(0)
        ->and($this->intip->refresh()->stok)->toBe(10)
        ->and(ButirKeranjang::count())->toBe(2)
        ->and(KunciIdempotensi::count())->toBe(0);
});

it('tidak menyisakan apa pun bila langkah terakhir gagal', function () {
    // Tagihan dibuat paling akhir. Saat pembuatannya gagal, checkout, kedua pesanan, butirnya,
    // dan pengurangan stok SUDAH ditulis di dalam transaksi. Semuanya harus ikut batal.
    Pembayaran::creating(fn () => throw new RuntimeException('Tabel pembayaran bermasalah.'));
    $this->sebagai($this->andi);

    ($this->checkout)()->assertStatus(500)->assertJsonPath('galat.kode', 'GALAT_SERVER');

    expect(Checkout::count())->toBe(0)
        ->and(Pesanan::count())->toBe(0)
        ->and(ButirPesanan::count())->toBe(0)
        ->and($this->kain->refresh()->stok)->toBe(5)
        ->and($this->intip->refresh()->stok)->toBe(10)
        ->and(ButirKeranjang::count())->toBe(2)
        ->and(KunciIdempotensi::count())->toBe(0);
});

it('mengulang checkout dengan kunci yang sama tidak menggandakan efek (LP-11)', function () {
    $this->sebagai($this->andi);

    $pertama = ($this->checkout)()->assertCreated();
    $kedua = ($this->checkout)()->assertCreated();

    expect($pertama->json('data.kode'))->toBe($kedua->json('data.kode'))
        ->and(Checkout::count())->toBe(1)
        ->and(Pembayaran::count())->toBe(1)
        ->and($this->kain->refresh()->stok)->toBe(3);
});

it('menolak kunci yang sama dengan badan berbeda (LP-11)', function () {
    $this->sebagai($this->andi);

    ($this->checkout)()->assertCreated();

    // Kunci sama, badan lain -> 422 LP-11
    $this->postJson(
        '/api/v1/checkout',
        ['alamat_id' => $this->alamat->id, 'catatan' => 'berubah'],
        ['Idempotency-Key' => 'checkout-uji-0000000001'],
    )->assertUnprocessable()->assertJsonPath('galat.kode', 'LP-11');
});

it('menuntut sesi segar untuk checkout di atas batas nilai (LP-15)', function () {
    config(['lapak.transaksi.batas_segar_rupiah' => 100_000]);

    $this->sebagai($this->andi, segar: false);

    ($this->checkout)()
        ->assertForbidden()
        ->assertJsonPath('galat.kode', 'LP-15');

    config(['lapak.transaksi.batas_segar_rupiah' => 2_000_000]);
});

it('menolak permintaan melebihi batas laju dengan 429 TERLALU_SERING', function () {
    config(['lapak.transaksi.laju_per_menit' => 1]);

    $this->sebagai($this->andi);
    ($this->checkout)()->assertCreated();
    ($this->checkout)([], 'checkout-uji-0000000002')
        ->assertStatus(429)
        ->assertJsonPath('galat.kode', 'TERLALU_SERING');

    config(['lapak.transaksi.laju_per_menit' => 10]);
});

it('menampilkan daftar pesanan milik sendiri di /pesanan', function () {
    $this->sebagai($this->andi);
    ($this->checkout)()->assertCreated();

    $respons = $this->getJson('/api/v1/pesanan')->assertOk();

    expect($respons->json('data'))->toHaveCount(2)
        ->and($respons->json('data.0.total.rupiah'))->toBeIn([382_000, 107_500]);
});

it('menjawab 404 untuk nomor pesanan milik orang lain', function () {
    $this->sebagai($this->andi);
    ($this->checkout)()->assertCreated();

    $nomor = Pesanan::query()->orderBy('id')->value('nomor');

    $this->sebagai(Pengguna::factory()->pembeli()->create())
        ->getJson("/api/v1/pesanan/{$nomor}")
        ->assertNotFound();
});

it('menampilkan satu pesanan beserta tagihannya', function () {
    $this->sebagai($this->andi);
    ($this->checkout)()->assertCreated();

    $nomor = Pesanan::query()->orderBy('id')->value('nomor');

    $this->getJson("/api/v1/pesanan/{$nomor}")
        ->assertOk()
        ->assertJsonPath('data.pesanan.nomor', $nomor)
        ->assertJsonPath('data.pembayaran.status', 'menunggu')
        ->assertJsonPath('data.pembayaran.jumlah.rupiah', 489_500);
});

it('membatalkan checkout kedaluwarsa beserta stoknya (LP-10)', function () {
    $this->sebagai($this->andi);
    ($this->checkout)()->assertCreated();

    $this->travel(24)->hours();
    $this->travel(1)->seconds();

    $this->artisan('lapak:batalkan-kedaluwarsa')->assertSuccessful();

    expect(Pembayaran::sole()->status)->toBe(StatusPembayaran::Kedaluwarsa)
        ->and($this->kain->refresh()->stok)->toBe(5)
        ->and($this->intip->refresh()->stok)->toBe(10)
        ->and(Pesanan::query()->where('status', StatusPesanan::Dibatalkan)->count())->toBe(2);
});

it('menolak GET /pesanan tanpa kunci sesi (401)', function () {
    $this->getJson('/api/v1/pesanan')->assertUnauthorized();
});

it('menjawab 422 dengan pesan yang sama untuk alamat orang lain', function () {
    $orangLain = Pengguna::factory()->pembeli()->create();
    $milikLain = Alamat::factory()->for($orangLain, 'pengguna')->create();

    $this->sebagai($this->andi)
        ->postJson(
            '/api/v1/checkout',
            ['alamat_id' => $milikLain->id],
            ['Idempotency-Key' => 'checkout-uji-0000000003'],
        )
        ->assertUnprocessable()
        ->assertJsonPath('galat.rincian.alamat_id.0', 'Alamat tidak ditemukan.');
});

it('menolak checkout dengan keranjang kosong', function () {
    ButirKeranjang::query()->delete();

    $this->sebagai($this->andi)
        ->postJson(
            '/api/v1/checkout',
            ['alamat_id' => $this->alamat->id],
            ['Idempotency-Key' => 'checkout-uji-0000000004'],
        )
        ->assertUnprocessable()
        ->assertJsonPath('galat.kode', 'KERANJANG_KOSONG');
});

it('membiarkan checkout heran bila pembayaran masuk di menit terakhir (tidak ada yang dibatalkan)', function () {
    $this->sebagai($this->andi);
    ($this->checkout)()->assertCreated();

    $pembayaran = Pembayaran::sole();
    $pembayaran->forceFill(['status' => StatusPembayaran::Lunas, 'lunas_pada' => now()])->save();

    $this->travel(24)->hours();
    $this->travel(1)->seconds();

    $this->artisan('lapak:batalkan-kedaluwarsa')->assertSuccessful();

    expect(Pembayaran::sole()->status)->toBe(StatusPembayaran::Lunas)
        ->and(Pesanan::query()->where('status', StatusPesanan::Dibatalkan)->count())->toBe(0)
        ->and($this->kain->refresh()->stok)->toBe(3);
});

it('product kadaluwarsa tidak menulis apa pun saat gembok diambil', function () {
    $this->sebagai($this->andi);
    ($this->checkout)()->assertCreated();

    $this->travel(24)->hours();
    $this->travel(1)->seconds();

    $this->artisan('lapak:batalkan-kedaluwarsa')->assertSuccessful();
    $this->artisan('lapak:batalkan-kedaluwarsa')->assertSuccessful();

    // Kedua kalinya: tidak ada checkout tersisa yang dibatalkan, tidak ada stok ditambah dua kali.
    expect($this->kain->refresh()->stok)->toBe(5);
});
