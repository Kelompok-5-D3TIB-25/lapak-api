# Catatan Penggunaan AI — Nabela Azzahra Putri

## Sesi 1

| Tanggal | Bagian | Alat | Cara verifikasi |
|---|---|---|---|
| 2026-11-06 | Keranjang dan simulasi belanja | Deepseek AI | `./vendor/bin/pest tests/Unit/PenghitungBelanjaTest.php tests/Feature/Belanja`, tinjau diff |

## Sesi 2

| Tanggal | Bagian | Alat | Cara verifikasi |
|---|---|---|---|
| 2026-11-08 | Dompet dan riwayat mutasi: `Dompet`, `MutasiSaldo` | Deepseek AI | `./vendor/bin/pest tests/Feature/Dompet`, `php artisan migrate:fresh --seed` |
| 2026-11-09 | `DompetController`, `GET /dompet`, `GET /dompet/mutasi` | Deepseek AI | `./vendor/bin/pest tests/Feature/Dompet/DompetTest.php`, `php artisan route:list` |

## Sesi 3

| Tanggal | Bagian | Alat | Cara verifikasi |
|---|---|---|---|
| 2026-11-09 | Buku besar untuk staf: `BukuBesarController`, `lapak:periksa-buku` | Deepseek AI | `./vendor/bin/pest tests/Feature/Dompet`, `php artisan lapak:periksa-buku` |

## Sesi 4

| Tanggal | Bagian | Alat | Cara verifikasi |
|---|---|---|---|
| 2026-11-09 | Pembayaran: `Pembayaran`, `LayananPembayaran` (`buat()`, `untuk()`, `tandaiKedaluwarsa()`) | Deepseek AI | `./vendor/bin/pest tests/Feature/Dompet/PembayaranTest.php` |
| 2026-11-10 | `TandaTanganGateway` dan `WebhookPembayaranController` | Deepseek AI | `./vendor/bin/pest tests/Feature/Dompet -k webhook`, `php artisan lapak:balapan webhook-ganda` |
| 2026-11-11 | `lapak:periksa-buku` lima invarian dan `lapak:simulasi-bayar` | Deepseek AI | `php artisan lapak:periksa-buku`, `php artisan lapak:balapan --semua` |

## Catatan

- Bagian yang dibantu AI: kerangka `PembayaranResource`, `LayananPembayaran`, `TandaTanganGateway`, dan `WebhookPembayaranController`.
- Bagian yang ditulis sendiri: alur `terima()` — baca ulang tagihan di bawah gembok, toleransi 5 menit, `hash_equals()` pada tanda tangan, dan penolakan `jumlah` yang berbeda (LP-6).
- Hal yang perlu diperbaiki setelah keluaran AI: tanda tangan dihitung atas badan mentah, bukan hasil json_encode ulang; diverifikasi lewat `tests/Feature/Dompet/WebhookPembayaranTest.php`.
- `lapak:simulasi-bayar` diverifikasi manual dengan `lapak:batalkan-kedaluwarsa` dan `lapak:periksa-buku`.
