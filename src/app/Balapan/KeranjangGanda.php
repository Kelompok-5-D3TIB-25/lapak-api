<?php

declare(strict_types=1);

namespace App\Balapan;

use App\Enums\NamaPeran;
use App\Models\Checkout;
use App\Models\Pengguna;
use App\Models\Produk;
use App\Models\Toko;
use App\Services\BukuAlamat;
use App\Services\LayananKeranjang;
use App\Services\PenerbitSesi;
use App\Services\PengelolaPeran;
use Illuminate\Support\Str;

/**
 * Satu keranjang, enam tab, kunci idempotensi berbeda. Yang menahan skenario ini adalah
 * gembok keranjang pada LayananCheckout, bukan idempotensi: setiap tab memang permintaan
 * yang berbeda atas keranjang yang sama.
 */
final class KeranjangGanda implements SkenarioBalapan
{
    use PanggilHttp;

    private const STOK_AWAL = 5;

    public function keterangan(): string
    {
        return 'Satu keranjang di-checkout dari banyak tab, kunci berbeda: tepat satu checkout';
    }

    public function proses(): int
    {
        return 6;
    }

    public function siapkan(): array
    {
        $pembeli = new Pengguna([
            'nama' => 'Pembeli Balapan',
            'surel' => 'balapan-'.Str::lower(Str::random(10)).'@lapak.test',
            'kata_sandi' => Str::random(24),
        ]);
        $pembeli->save();
        app(PengelolaPeran::class)->beri($pembeli, NamaPeran::Pembeli);

        $toko = Toko::factory()->create(['nama' => 'Toko Balapan', 'ongkir_rupiah' => 10_000]);
        $produk = Produk::factory()->for($toko)->create(['stok' => self::STOK_AWAL]);

        $alamat = $this->siapkanAlamat($pembeli);

        $keranjang = app(LayananKeranjang::class);
        $keranjang->aturButir($pembeli, $produk->id, 2);

        [, $kunci] = app(PenerbitSesi::class)->terbitkan($pembeli, 'Uji balapan');

        return [
            'pembeli' => $pembeli->id,
            'produk' => $produk->id,
            'alamat' => $alamat,
            'kunci' => $kunci,
        ];
    }

    public function jalankan(array $konteks, int $nomor): string
    {
        $badan = ['alamat_id' => $konteks['alamat']];

        [$status, $json] = $this->panggil('POST', '/api/v1/checkout', $badan, [
            'Authorization' => 'Bearer '.$konteks['kunci'],
            'Idempotency-Key' => "tab-{$nomor}-".Str::random(16),
        ]);

        return $this->ringkas($status, $json);
    }

    public function periksa(array $konteks, array $hasil): array
    {
        $checkout = Checkout::query()->where('pembeli_id', $konteks['pembeli'])->count();
        $stok = Produk::query()->findOrFail($konteks['produk'])->stok;

        $pelanggaran = [];
        if ($checkout !== 1) {
            $pelanggaran[] = "Lahir {$checkout} checkout dari satu keranjang, seharusnya 1.";
        }
        if ($stok !== self::STOK_AWAL - 2) {
            $terjual = self::STOK_AWAL - $stok;
            $pelanggaran[] = "Stok berkurang {$terjual}, padahal keranjang hanya berisi 2.";
        }

        return $pelanggaran;
    }

    private function siapkanAlamat(Pengguna $pembeli): int
    {
        return app(BukuAlamat::class)->tambah($pembeli, [
            'label' => 'Rumah',
            'nama_penerima' => 'Pembeli Balapan',
            'telepon' => '081200000000',
            'jalan' => 'Jl. Balapan No. 1',
            'kota' => 'Surakarta',
            'kode_pos' => '57111',
        ])->id;
    }
}
