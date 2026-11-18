<?php

declare(strict_types=1);

namespace App\Services;

use App\Domain\Pesanan\PelakuPesanan;
use App\Models\Pembayaran;

final class PembatalanOtomatis
{
    public const ALASAN = 'Tidak dibayar sampai batas waktu';

    public function __construct(private readonly LayananPesanan $pesanan) {}

    public function jalankan(): int
    {
        $dibatalkan = 0;

        foreach (Pembayaran::query()->lewatBatas()->orderBy('id')->pluck('checkout_id') as $id) {
            $dibatalkan += $this->batalkan($id) ? 1 : 0;
        }

        return $dibatalkan;
    }

    public function batalkan(int $idCheckout): bool
    {
        return $this->pesanan->batalkanCheckout(
            $idCheckout, PelakuPesanan::Sistem, null, self::ALASAN,
        );
    }
}