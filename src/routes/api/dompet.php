<?php

use App\Http\Controllers\BukuBesarController;
use App\Http\Controllers\DompetController;
use App\Http\Controllers\PencairanSayaController;
use App\Http\Controllers\PencairanStafController;
use App\Http\Controllers\PembayaranSayaController;
use App\Http\Controllers\WebhookPembayaranController;
use Illuminate\Support\Facades\Route;

// Dompet milik sendiri: hanya pemegang izin dompet.lihat, yaitu penjual.
Route::middleware(['auth:sesi', 'izin:dompet.lihat'])->group(function (): void {
    Route::get('dompet', [DompetController::class, 'show'])->name('dompet.show');
    // Sesi segar: kunci yang sah saja belum cukup untuk membuka rincian uang (LP-15).
    Route::get('dompet/mutasi', [DompetController::class, 'mutasi'])
        ->middleware('sesi.segar')
        ->name('dompet.mutasi');
});
// Buku besar seluruh marketplace: staf pemegang izin buku-besar.lihat, selalu dengan sesi segar.
// Admin tidak memegang izin ini. Mengelola peran dan toko tidak berarti boleh melihat uang.
Route::middleware(['auth:sesi', 'izin:buku-besar.lihat', 'sesi.segar'])
    ->prefix('buku-besar')
    ->name('buku-besar.')
    ->whereNumber('id')
    ->group(function (): void {
        Route::get('dompet', [BukuBesarController::class, 'index'])->name('dompet.index');
        Route::get('dompet/{id}/mutasi', [BukuBesarController::class, 'mutasi'])->name('dompet.mutasi');
    });

// Pencairan saldo penjual.
Route::middleware(['auth:sesi', 'izin:pencairan.ajukan'])
    ->prefix('dompet/pencairan')
    ->name('dompet.pencairan.')
    ->group(function (): void {
        Route::get('/', [PencairanSayaController::class, 'index'])->name('index');
        Route::post('/', [PencairanSayaController::class, 'store'])
            ->middleware(['throttle:transaksi', 'idempoten', 'sesi.segar'])
            ->name('store');
    });

// Putusan pencairan oleh staf keuangan.
Route::middleware(['auth:sesi', 'izin:pencairan.setujui', 'sesi.segar'])
    ->prefix('keuangan/pencairan')
    ->name('keuangan.pencairan.')
    ->group(function (): void {
        Route::get('/', [PencairanStafController::class, 'index'])->name('index');
        Route::post('{kode}/setujui', [PencairanStafController::class, 'setujui'])
            ->middleware('idempoten')
            ->name('setujui');
        Route::post('{kode}/tolak', [PencairanStafController::class, 'tolak'])
            ->middleware('idempoten')
            ->name('tolak');
    });

// Tagihan milik pembeli yang sedang masuk. Pembeli hanya dapat MELIHAT tagihannya.
Route::get('pembayaran/{kode}', [PembayaranSayaController::class, 'show'])
    ->middleware(['auth:sesi', 'izin:pesanan.buat'])
    ->name('pembayaran.show');

// Satu-satunya rute tulis tanpa kunci sesi. Pemanggilnya gerbang pembayaran, dan yang
// menjaganya adalah tanda tangan HMAC di dalam controller. Terdaftar pada RUTE_PUBLIK.
Route::post('webhook/pembayaran', WebhookPembayaranController::class)->name('webhook.pembayaran');
