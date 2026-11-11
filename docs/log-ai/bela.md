# Catatan Penggunaan AI — Nabela Azzahra Putri

## Sesi 1

| Tanggal | Bagian | Alat | Cara verifikasi |
|---|---|---|---|
| 2026-11-06 | Keranjang dan simulasi belanja | ChatGPT | `./vendor/bin/pest tests/Unit/PenghitungBelanjaTest.php tests/Feature/Belanja`, tinjau diff |

## Sesi 2

| Tanggal | Bagian | Alat | Cara verifikasi |
|---|---|---|---|
| 2026-11-08 | Skema keranjang, keranjang controller, kontrak keranjang | Gemini | `./vendor/bin/pest tests/Feature/Belanja`, `php artisan migrate:fresh --seed` |
| 2026-11-08 | Keadaan keranjang PenggunaToko: `LayananKeranjang` | Claude | `./vendor/bin/pest tests/Feature/Belanja/KeranjangTest.php` |

## Sesi 3

| Tanggal | Bagian | Alat | Cara verifikasi |
|---|---|---|---|
| 2026-11-09 | PengelolaPeran versi atribusi Sesi 3, tabel PenggunaPeran | Gemini | `./vendor/bin/pest tests/Feature/Identitas`, tinjau `git show --stat` |

## Sesi 4

| Tanggal | Bagian | Alat | Cara verifikasi |
|---|---|---|---|
| 2026-11-09 | Pembayaran: `Pembayaran`, `LayananPembayaran` versi konfirmasi, pemetaan | ChatGPT | `./vendor/bin/pest tests/Feature/Dompet/PembayaranTest.php tests/Feature/Dompet/WebhookPembayaranTest.php` |
| 2026-11-10 | `TandaTanganGateway` dan `WebhookPembayaranController` | Claude | `./vendor/bin/pest tests/Feature/Dompet -k webhook`, `php artisan lapak:balapan webhook-ganda` |
| 2026-11-11 | `lapak:periksa-buku` kelima invarian dan `lapak:simulasi-bayar` | Gemini | `./vendor/bin/pest tests/Unit`, `php artisan lapak:periksa-buku` |

## Catatan

- Bagian yang dibantu AI: kerangka `PembayaranResource`, `LayananPembayaran`, `TandaTanganGateway`, dan `WebhookPembayaranController`.
- Bagian yang ditulis sendiri: aturan LP-7, LP-12, LP-13, pemetaan galat HTTP webhook, dan penjagaan `hash_equals` pada tanda tangan.
- Hal yang perlu diperbaiki setelah keluaran AI: hanya memindai status pembayaran setelah dan bukan sebelum tanda tangan, dan menolak `jumlah` yang berbeda; diverifikasi lewat `tests/Feature/Dompet/WebhookPembayaranTest.php`.
- `lapak:simulasi-bayar` diverifikasi manual dengan `lapak:batalkan-kedaluwarsa` dan `lapak:periksa-buku`.
