<?php

use App\Domain\Pesanan\StatusPesanan;
use App\Enums\StatusPembayaran;
use App\Models\ButirPesanan;
use App\Models\Checkout;
use App\Models\Pembayaran;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Services\PembatalanOtomatis;
use Illuminate\Support\Facades\DB;

/** Membuat butir pesanan langsung, tanpa factory. */
function buatButir(Pesanan $pesanan, Produk $produk, int $kuantitas = 1): ButirPesanan
{
    $butir = new ButirPesanan;
    $butir->forceFill([
        'pesanan_id' => $pesanan->id,
        'produk_id' => $produk->id,
        'nama_produk' => $produk->nama,
        'harga_rupiah' => $produk->harga_rupiah,
        'kuantitas' => $kuantitas,
        'subtotal_rupiah' => $produk->harga_rupiah * $kuantitas,
    ])->save();

    return $butir;
}

/** Satu checkout yang tagihannya menunggu, dengan satu pesanan berbutir satu produk berstok 5. */
function checkoutMenunggu(): array
{
    $checkout = Checkout::factory()->create();
    $produk = Produk::factory()->create(['stok' => 5]);
    $pesanan = Pesanan::factory()->for($checkout)->create();
    buatButir($pesanan, $produk, 2);
    Pembayaran::factory()->for($checkout)->create(['batas_bayar_pada' => now()->subMinute()]);

    return [$checkout, $produk, $pesanan];
}

it('aman dijalankan berulang: stok tidak dikembalikan dua kali', function () {
    [, $produk] = checkoutMenunggu();

    $this->artisan('lapak:batalkan-kedaluwarsa')->assertSuccessful();
    $this->artisan('lapak:batalkan-kedaluwarsa')
        ->expectsOutputToContain('0 checkout dibatalkan')
        ->assertSuccessful();

    expect($produk->refresh()->stok)->toBe(7);
});

it('memeriksa ulang tagihan di bawah gembok sebelum membatalkan', function () {
    [$checkout, $produk] = checkoutMenunggu();

    // Penjadwal sudah memilih checkout ini, tetapi pembayarannya masuk sesaat sebelum gembok.
    Pembayaran::query()->where('checkout_id', $checkout->id)
        ->update(['status' => StatusPembayaran::Lunas, 'lunas_pada' => now()]);

    expect(app(PembatalanOtomatis::class)->batalkan($checkout->id))->toBeFalse()
        ->and($checkout->pesanan()->pluck('status')->unique()->all())
        ->toBe([StatusPesanan::MenungguPembayaran])
        ->and($produk->refresh()->stok)->toBe(5);
});

it('mengembalikan stok urut nomor produk, apa pun urutan butirnya', function () {
    $checkout = Checkout::factory()->create();
    Pembayaran::factory()->for($checkout)->create(['batas_bayar_pada' => now()->subMinute()]);
    $pesanan = Pesanan::factory()->for($checkout)->create();
    [$kecil, $tengah, $besar] = Produk::factory()->count(3)->create(['stok' => 0])->all();

    // Butir sengaja disimpan dari nomor produk terbesar.
    foreach ([$besar, $kecil, $tengah] as $barang) {
        buatButir($pesanan, $barang);
    }

    DB::enableQueryLog();
    app(PembatalanOtomatis::class)->batalkan($checkout->id);

    $urutan = collect(DB::getQueryLog())
        // Tanda kutip nama tabel berbeda antara SQLite dan MySQL, maka dicocokkan dengan titik.
        ->filter(fn (array $kueri) => preg_match('/^update .produk. /', $kueri['query']) === 1)
        ->map(fn (array $kueri) => last($kueri['bindings']))
        ->values()
        ->all();

    expect($urutan)->toBe([$kecil->id, $tengah->id, $besar->id]);
});
