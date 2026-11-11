<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Pembayaran;
use App\Services\TandaTanganGateway;
use Illuminate\Console\Command;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

/**
 * Gerbang pembayaran disimulasikan: menyusun kabar pelunasan atas sebuah tagihan,
 * menandatanganinya dengan rahasia bersama, lalu menyerahkannya ke kernel HTTP sama seperti
 * webhook sungguhan. Menolak di produksi.
 *
 * Pemakaian:
 *   lapak:simulasi-bayar BYR-01K7...
 *   lapak:simulasi-bayar BYR-01K7... --ganda          kabar "kembar", dikirim dua kali
 *   lapak:simulasi-bayar BYR-01K7... --jumlah=1       jumlah yang salah (tidak melunasi)
 *   lapak:simulasi-bayar BYR-01K7... --rahasia=salah  tanda tangan yang tidak sah (dijawab 401)
 */
final class SimulasiBayar extends Command
{
    protected $signature = 'lapak:simulasi-bayar
                            {kode : Kode tagihan, misalnya BYR-01K7...}
                            {--jumlah= : Jumlah yang akan "dibayar" (default: sama dengan tagihan)}
                            {--ganda : Kabar kembar, dikirim dua kali berturut-turut}
                            {--rahasia= : Rahasia yang dipakai menandatangani (default: dari konfigurasi)}';

    protected $description = 'Menyimulasikan kabar pelunasan dari gerbang pembayaran';

    public function handle(TandaTanganGateway $ttd, Kernel $kernel): int
    {
        if (app()->isProduction()) {
            $this->error('lapak:simulasi-bayar tidak boleh dijalankan di produksi.');

            return self::FAILURE;
        }

        $tagihan = Pembayaran::query()->where('kode', $this->argument('kode'))->firstOrFail();

        $kabar = [
            'id_peristiwa' => 'evt_sim_'.str_replace('.', '', uniqid('', true)),
            'kode_pembayaran' => $tagihan->kode,
            'jumlah_rupiah' => (int) ($this->option('jumlah') ?? $tagihan->jumlah_rupiah),
        ];

        $kirim = function () use ($kabar, $ttd, $kernel): int {
            $badan = json_encode($kabar, JSON_THROW_ON_ERROR);
            $rahasia = $this->option('rahasia');
            $tanda = $ttd->buat($badan, waktu: null, rahasia: is_string($rahasia) ? $rahasia : null);

            $permintaan = Request::create(
                '/api/v1/webhook/pembayaran',
                'POST',
                [], [], [],
                [
                    'CONTENT_TYPE' => 'application/json',
                    'HTTP_ACCEPT' => 'application/json',
                    'HTTP_'.strtoupper(str_replace('-', '_', TandaTanganGateway::HEADER)) => $tanda,
                ],
                $badan,
            );

            $respons = $kernel->handle($permintaan);
            $this->line($respons->getStatusCode().' '.$respons->getContent());

            return $respons->getStatusCode();
        };

        $kode = $kirim();
        if ($this->option('ganda')) {
            $this->line('— kabar kembar —');
            $kirim();
        }

        return $kode >= 200 && $kode < 300 ? self::SUCCESS : self::FAILURE;
    }
}
