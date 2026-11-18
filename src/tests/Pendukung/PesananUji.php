<?php

declare(strict_types=1);

namespace Tests\Pendukung;

use App\Domain\Pesanan\StatusPesanan;
use App\Enums\JenisDompet;
use App\Enums\JenisMutasi;
use App\Enums\StatusPembayaran;
use App\Models\ButirPesanan;
use App\Models\Checkout;
use App\Models\Pembayaran;
use App\Models\Pesanan;
use App\Models\Pengguna;
use App\Models\Produk;
use App\Services\BukuBesar;
use App\Domain\Uang;

final class PesananUji
{
    public const HARGA = 185_000;

    public const ONGKIR = 12_000;

    public static function belumDibayar(
        Pengguna $pembeli,
        Produk $produk,
        int $kuantitas = 2,
    ): Pesanan {
        return self::buat($pembeli, $produk, $kuantitas, StatusPembayaran::Menunggu);
    }

    public static function dibayar(Pengguna $pembeli, Produk $produk, int $kuantitas = 2): Pesanan
    {
        $pesanan = self::buat($pembeli, $produk, $kuantitas, StatusPembayaran::Lunas);
        $pesanan->forceFill(['status' => StatusPesanan::Dibayar, 'dibayar_pada' => now()])->save();

        $buku = app(BukuBesar::class);
        $buku->pindahkan(
            $buku->dompetSistem(JenisDompet::Eksternal),
            $buku->dompetSistem(JenisDompet::Escrow),
            Uang::dari($pesanan->total_rupiah),
            JenisMutasi::DanaMasuk,
            $pesanan->nomor,
        );

        return $pesanan;
    }

    private static function buat(
        Pengguna $pembeli,
        Produk $produk,
        int $kuantitas,
        StatusPembayaran $status,
    ): Pesanan {
        $subtotal = $kuantitas * self::HARGA;
        $total = $subtotal + self::ONGKIR;

        $checkout = Checkout::factory()->create([
            'pembeli_id' => $pembeli->id,
            'total_rupiah' => $total,
        ]);

        Pembayaran::factory()->create([
            'checkout_id' => $checkout->id,
            'jumlah_rupiah' => $total,
            'status' => $status,
            ...($status === StatusPembayaran::Lunas ? ['lunas_pada' => now()] : []),
        ]);

        $pesanan = Pesanan::factory()->create([
            'checkout_id' => $checkout->id,
            'pembeli_id' => $pembeli->id,
            'toko_id' => $produk->toko_id,
            'status' => StatusPesanan::MenungguPembayaran,
            'subtotal_rupiah' => $subtotal,
            'ongkir_rupiah' => self::ONGKIR,
            'total_rupiah' => $total,
        ]);

        ButirPesanan::factory()->create([
            'pesanan_id' => $pesanan->id,
            'produk_id' => $produk->id,
            'nama_produk' => $produk->nama,
            'harga_rupiah' => self::HARGA,
            'kuantitas' => $kuantitas,
            'subtotal_rupiah' => $subtotal,
        ]);

        return $pesanan;
    }
}
