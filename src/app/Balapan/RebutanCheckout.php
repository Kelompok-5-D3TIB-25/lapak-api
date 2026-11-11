<?php

declare(strict_types=1);

namespace App\Balapan;

use App\Enums\NamaPeran;
use App\Models\Checkout;
use App\Models\Pembayaran;
use App\Models\Pengguna;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\Toko;
use App\Services\BukuAlamat;
use App\Services\LayananKeranjang;
use App\Services\PenerbitSesi;
use App\Services\PengelolaPeran;
use Illuminate\Support\Str;

/**
 * Empat pembeli BERBEDA berebut satu produk yang sama. Masing-masing membawa sesinya,
 * keranjangnya, alamatnya, dan Idempotency-Key-nya sendiri. Aturannya (LP-7): setiap pembeli
 * yang dijawab 201 memiliki tepat satu checkout utuh beserta pesanan dan tagihannya;
 * tidak ada pembeli yang setengah jadi.
 */
final class RebutanCheckout implements SkenarioBalapan
{
    use PanggilHttp;

    private const STOK_AWAL = 5;

    private const PEMBELI = 4;

    public function keterangan(): string
    {
        return 'Empat pembeli berbeda berebut 5 barang: jawaban 201 berarti checkout yang utuh';
    }

    public function proses(): int
    {
        return self::PEMBELI;
    }

    public function siapkan(): array
    {
        $toko = Toko::factory()->create(['nama' => 'Toko Balapan Rebutan', 'ongkir_rupiah' => 10_000]);
        $produk = Produk::factory()->for($toko)->create(['stok' => self::STOK_AWAL]);

        $pembeli = [];
        $alamat = [];
        $kunci = [];

        foreach (range(1, self::PEMBELI) as $i) {
            $orang = new Pengguna([
                'nama' => "Pembeli Balapan {$i}",
                'surel' => 'pembeli-balapan-'.Str::lower(Str::random(8)).'@lapak.test',
                'kata_sandi' => Str::random(24),
            ]);
            $orang->save();
            app(PengelolaPeran::class)->beri($orang, NamaPeran::Pembeli);

            $pembeli[] = $orang->id;
            $alamat[] = app(BukuAlamat::class)->tambah($orang, [
                'label' => 'Rumah',
                'nama_penerima' => "Pembeli Balapan {$i}",
                'telepon' => '081200000000',
                'jalan' => "Jl. Balapan No. {$i}",
                'kota' => 'Surakarta',
                'kode_pos' => '57111',
            ])->id;

            app(LayananKeranjang::class)->aturButir($orang, $produk->id, 1);

            [, $kunciSesi] = app(PenerbitSesi::class)->terbitkan($orang, 'Uji balapan');
            $kunci[] = $kunciSesi;
        }

        return [
            'pembeli' => $pembeli,
            'produk' => $produk->id,
            'alamat' => $alamat,
            'kunci' => $kunci,
        ];
    }

    public function jalankan(array $konteks, int $nomor): string
    {
        $i = $nomor - 1;

        [$status, $json] = $this->panggil(
            'POST',
            '/api/v1/checkout',
            ['alamat_id' => $konteks['alamat'][$i]],
            [
                'Authorization' => 'Bearer '.$konteks['kunci'][$i],
                'Idempotency-Key' => "pembeli-{$nomor}-".Str::random(12),
            ],
        );

        return $status === 201 ? 'terbeli' : $this->ringkas($status, $json);
    }

    public function periksa(array $konteks, array $hasil): array
    {
        $terbeli = count(array_filter($hasil, fn (string $h) => $h === 'terbeli'));

        $idPembeli = $konteks['pembeli'];
        $idCheckout = Checkout::query()->whereIn('pembeli_id', $idPembeli)->pluck('id');
        $checkout = $idCheckout->count();
        $pesanan = Pesanan::query()->whereIn('checkout_id', $idCheckout)->count();
        $pembayaran = Pembayaran::query()->whereIn('checkout_id', $idCheckout)->count();
        $stok = Produk::query()->findOrFail($konteks['produk'])->stok;

        $pelanggaran = [];
        if ($checkout !== $terbeli) {
            $pelanggaran[] = "Ada {$checkout} checkout untuk {$terbeli} jawaban 201.";
        }
        if ($pesanan !== $terbeli) {
            // Satu toko: jumlah pesanan harus sama dengan jumlah jawaban 201.
            $pelanggaran[] = "Ada {$pesanan} pesanan untuk {$terbeli} jawaban 201.";
        }
        if ($pembayaran !== $terbeli) {
            $pelanggaran[] = "Ada {$pembayaran} tagihan untuk {$terbeli} jawaban 201.";
        }
        if ($stok !== self::STOK_AWAL - $terbeli) {
            $pelanggaran[] = "Stok {$stok}, seharusnya ".(self::STOK_AWAL - $terbeli).'.';
        }

        return $pelanggaran;
    }
}
