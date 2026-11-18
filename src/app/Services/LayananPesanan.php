<?php

declare(strict_types=1);

namespace App\Services;

use App\Domain\Pesanan\PelakuPesanan;
use App\Domain\Pesanan\StatusPesanan;
use App\Enums\PeristiwaAudit;
use App\Exceptions\GalatDomain;
use App\Models\Checkout;
use App\Models\Pesanan;
use Illuminate\Support\Collection;

final class LayananPesanan
{
    public function __construct(
        private readonly GembokCheckout $gembok,
        private readonly MesinStatusPesanan $mesin,
        private readonly PenjagaStok $stok,
        private readonly LayananDana $dana,
        private readonly LayananPembayaran $pembayaran,
        private readonly PencatatAudit $audit,
    ) {}

    public function kemas(Pesanan $pesanan): Pesanan
    {
        return $this->gembok->dalam($pesanan->checkout_id, fn () => $this->mesin->pindahkan(
            $pesanan, StatusPesanan::Dikemas, PelakuPesanan::Penjual,
        ));
    }

    public function kirim(Pesanan $pesanan, string $nomorResi): Pesanan
    {
        return $this->gembok->dalam($pesanan->checkout_id, function () use ($pesanan, $nomorResi) {
            $terkini = $this->mesin->pindahkan(
                $pesanan, StatusPesanan::Dikirim, PelakuPesanan::Penjual,
            );
            $terkini->forceFill(['nomor_resi' => trim($nomorResi)])->save();

            return $terkini;
        });
    }

    public function terima(Pesanan $pesanan, PelakuPesanan $oleh, ?\App\Models\Pengguna $pelaku = null): Pesanan
    {
        return $this->gembok->dalam($pesanan->checkout_id, function () use (
            $pesanan, $oleh, $pelaku,
        ) {
            $terkini = $this->mesin->pindahkan($pesanan, StatusPesanan::Selesai, $oleh);
            $this->dana->lepaskan($terkini);

            $sasaran = "pesanan:{$terkini->nomor}";
            $this->audit->catat(PeristiwaAudit::DanaDilepas, $pelaku, $sasaran, [
                'oleh' => $oleh->value,
                'bagian_penjual_rupiah' => $this->dana->bagianPenjual($terkini)->rupiah,
                'komisi_rupiah' => $this->dana->komisi($terkini)->rupiah,
            ]);

            return $terkini;
        });
    }

    public function batalkan(
        Pesanan $pesanan,
        PelakuPesanan $oleh,
        ?\App\Models\Pengguna $pelaku,
        string $alasan,
    ): Pesanan {
        return $this->gembok->dalam($pesanan->checkout_id, function () use (
            $pesanan, $oleh, $pelaku, $alasan,
        ) {
            $asal = Pesanan::query()->lockForUpdate()->findOrFail($pesanan->id)->status;

            if ($asal === StatusPesanan::MenungguPembayaran) {
                throw new GalatDomain(
                    'LP-10', 'Pesanan yang belum dibayar dibatalkan bersama checkout-nya.', 409,
                );
            }

            $terkini = $this->mesin->pindahkan($pesanan, StatusPesanan::Dibatalkan, $oleh, $alasan);
            $this->kembalikanStok(collect([$terkini]));
            $this->dana->kembalikan($terkini);

            $sasaran = "pesanan:{$terkini->nomor}";
            $this->audit->catat(PeristiwaAudit::PesananDibatalkan, $pelaku, $sasaran, [
                'oleh' => $oleh->value, 'dari_status' => $asal->value, 'alasan' => $alasan,
            ]);
            $this->audit->catat(PeristiwaAudit::DanaDikembalikan, $pelaku, $sasaran, [
                'jumlah_rupiah' => $terkini->total_rupiah,
            ]);

            return $terkini;
        });
    }

    public function batalkanCheckout(
        int $idCheckout,
        PelakuPesanan $oleh,
        ?\App\Models\Pengguna $pelaku,
        string $alasan,
    ): bool {
        return $this->gembok->dalam($idCheckout, function (Checkout $checkout) use (
            $oleh, $pelaku, $alasan,
        ): bool {
            $ditutup = $oleh === PelakuPesanan::Sistem
                ? $this->pembayaran->tandaiKedaluwarsa($checkout)
                : $this->pembayaran->tandaiDibatalkan($checkout);

            if (! $ditutup) {
                return false;
            }

            $semua = $checkout->pesanan()->with('butir')->orderBy('id')->get();

            foreach ($semua as $pesanan) {
                $this->mesin->pindahkan($pesanan, StatusPesanan::Dibatalkan, $oleh, $alasan);
            }

            $this->kembalikanStok($semua);

            $sasaran = "checkout:{$checkout->kode}";
            $this->audit->catat(PeristiwaAudit::CheckoutDibatalkan, $pelaku, $sasaran, [
                'oleh' => $oleh->value,
                'alasan' => $alasan,
                'pesanan' => $semua->pluck('nomor')->all(),
            ]);

            return true;
        });
    }

    private function kembalikanStok(Collection $pesanan): void
    {
        $kembali = [];

        foreach ($pesanan as $satu) {
            foreach ($satu->butir as $butir) {
                $id = $butir->produk_id;
                $kembali[$id] = ($kembali[$id] ?? 0) + $butir->kuantitas;
            }
        }

        ksort($kembali);

        foreach ($kembali as $idProduk => $kuantitas) {
            $this->stok->kembalikan($idProduk, $kuantitas);
        }
    }
}