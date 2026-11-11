<?php

declare(strict_types=1);

namespace App\Balapan;

use App\Domain\Pesanan\StatusPesanan;
use App\Enums\JenisDompet;
use App\Enums\StatusPembayaran;
use App\Models\Checkout;
use App\Models\Dompet;
use App\Models\MutasiSaldo;
use App\Models\Pembayaran;
use App\Models\Pengguna;
use App\Models\Pesanan;
use App\Models\Toko;
use App\Services\BukuBesar;
use App\Services\TandaTanganGateway;

/**
 * Gerbang pembayaran mengirim ulang webhook yang jawabannya tidak sampai. Enam kabar yang
 * sama tiba serentak. Aturannya (LP-12, LP-13): dana masuk escrow tepat satu kali, bukan enam.
 */
final class WebhookGanda implements SkenarioBalapan
{
    use PanggilHttp;

    private const TAGIHAN = 489_500;

    public function keterangan(): string
    {
        return 'Kabar pembayaran yang sama tiba serentak: dana masuk escrow tepat satu kali';
    }

    public function proses(): int
    {
        return 6;
    }

    public function siapkan(): array
    {
        $pembeli = Pengguna::factory()->pembeli()->create();
        $toko = Toko::factory()->create(['nama' => 'Toko Balapan Webhook']);
        $checkout = Checkout::factory()->for($pembeli, 'pembeli')->create(['total_rupiah' => self::TAGIHAN]);
        $tagihan = Pembayaran::factory()->for($checkout)->create(['jumlah_rupiah' => self::TAGIHAN]);
        Pesanan::factory()->for($checkout)->for($toko, 'toko')->create(['total_rupiah' => 382_000]);
        Pesanan::factory()->for($checkout)->create(['total_rupiah' => 107_500]);

        $badan = json_encode([
            'id_peristiwa' => 'evt_balapan_'.uniqid(),
            'kode_pembayaran' => $tagihan->kode,
            'jumlah_rupiah' => self::TAGIHAN,
        ], JSON_THROW_ON_ERROR);

        $tanda = app(TandaTanganGateway::class)->buat($badan);
        $escrowAwal = (int) Dompet::query()->where('kode', JenisDompet::Escrow->value)->value('saldo_rupiah');

        return [
            'checkout' => $checkout->id,
            'badan' => $badan,
            'tanda' => $tanda,
            'escrow_awal' => $escrowAwal,
        ];
    }

    public function jalankan(array $konteks, int $nomor): string
    {
        [$status, $json] = $this->panggil(
            'POST',
            '/api/v1/webhook/pembayaran',
            json_decode($konteks['badan'], true),
            [TandaTanganGateway::HEADER => $konteks['tanda']],
        );

        return $status === 200 ? "200 {$json['data']['hasil']}" : $this->ringkas($status, $json);
    }

    public function periksa(array $konteks, array $hasil): array
    {
        $checkout = Checkout::query()->findOrFail($konteks['checkout']);
        $escrow = app(BukuBesar::class)->dompetSistem(JenisDompet::Escrow)->saldo_rupiah;
        $masuk = $escrow - $konteks['escrow_awal'];
        $nomor = $checkout->pesanan()->pluck('nomor');
        $mutasi = MutasiSaldo::query()->whereIn('rujukan', $nomor)->count();
        $melunasi = count(array_filter($hasil, fn (string $h) => $h === '200 dilunasi'));

        $pelanggaran = [];
        if ($melunasi !== 1) {
            $pelanggaran[] = "{$melunasi} kabar dianggap melunasi, seharusnya tepat 1.";
        }
        if ($masuk !== self::TAGIHAN) {
            $pelanggaran[] = "Escrow bertambah {$masuk}, padahal tagihannya ".self::TAGIHAN.'.';
        }
        if ($mutasi !== 4) {
            $pelanggaran[] = "Tercatat {$mutasi} baris mutasi untuk dua pesanan, seharusnya 4.";
        }
        if ($checkout->pesanan()->where('status', StatusPesanan::Dibayar)->count() !== 2) {
            $pelanggaran[] = 'Tidak semua pesanan berstatus dibayar.';
        }
        $tagihan = Pembayaran::query()->where('checkout_id', $checkout->id)->sole();
        if ($tagihan->status !== StatusPembayaran::Lunas) {
            $pelanggaran[] = 'Tagihan tidak berstatus lunas.';
        }

        return $pelanggaran;
    }
}
