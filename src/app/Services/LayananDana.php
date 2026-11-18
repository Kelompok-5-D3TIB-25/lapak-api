<?php

declare(strict_types=1);

namespace App\Services;

use App\Domain\Uang;
use App\Enums\JenisDompet;
use App\Enums\JenisMutasi;
use App\Models\MutasiSaldo;
use App\Models\Pesanan;
use Illuminate\Support\Facades\DB;
use LogicException;

final class LayananDana
{
    public function __construct(private readonly BukuBesar $buku) {}

    public function komisi(Pesanan $pesanan): Uang
    {
        return Uang::dari($pesanan->subtotal_rupiah)->persen(config('lapak.komisi_persen'));
    }

    public function bagianPenjual(Pesanan $pesanan): Uang
    {
        return Uang::dari($pesanan->total_rupiah)->kurang($this->komisi($pesanan));
    }

    public function lepaskan(Pesanan $pesanan): void
    {
        $this->pastikanBelumKeluar($pesanan);

        $escrow = $this->buku->dompetSistem(JenisDompet::Escrow);
        $pendapatan = $this->buku->dompetSistem(JenisDompet::Pendapatan);
        $penjual = $this->buku->dompetPenjual($pesanan->toko->pemilik);
        $persen = config('lapak.komisi_persen');

        $this->buku->pindahkan(
            $escrow, $pendapatan, $this->komisi($pesanan),
            JenisMutasi::Komisi, $pesanan->nomor, "Komisi {$persen}%",
        );
        $this->buku->pindahkan(
            $escrow, $penjual, $this->bagianPenjual($pesanan),
            JenisMutasi::LepasDana, $pesanan->nomor, 'Dana dilepas ke penjual',
        );
    }

    public function kembalikan(Pesanan $pesanan): void
    {
        $this->pastikanBelumKeluar($pesanan);

        $this->buku->pindahkan(
            $this->buku->dompetSistem(JenisDompet::Escrow),
            $this->buku->dompetSistem(JenisDompet::Eksternal),
            Uang::dari($pesanan->total_rupiah),
            JenisMutasi::Pengembalian,
            $pesanan->nomor,
            'Pengembalian dana ke pembeli',
        );
    }

    private function pastikanBelumKeluar(Pesanan $pesanan): void
    {
        if (DB::transactionLevel() === 0) {
            throw new LogicException('Dana hanya boleh dipindahkan di dalam GembokCheckout.');
        }

        $sudah = MutasiSaldo::query()
            ->where('rujukan', $pesanan->nomor)
            ->whereIn('jenis', [JenisMutasi::LepasDana, JenisMutasi::Pengembalian])
            ->exists();

        if ($sudah) {
            throw new LogicException("Dana {$pesanan->nomor} sudah pernah keluar dari escrow.");
        }
    }
}