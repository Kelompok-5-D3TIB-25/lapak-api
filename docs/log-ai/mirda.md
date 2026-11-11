# Catatan Penggunaan AI — Mirda Kinasih

## Sesi 1

| Tanggal | Bagian | Alat | Cara verifikasi |
|---|---|---|---|
| 2026-11-06 | Value object `Uang` dan buku besar | Deepseek AI | `./vendor/bin/pest tests/Unit/UangTest.php tests/Feature/Dompet`, `php artisan lapak:periksa-buku` |

## Sesi 2

| Tanggal | Bagian | Alat | Cara verifikasi |
|---|---|---|---|
| 2026-11-08 | Keranjang: `LayananKeranjang`, `ButirKeranjang`, `KeranjangController` | Deepseek AI | `./vendor/bin/pest tests/Feature/Belanja`, `php artisan migrate:fresh --seed` |

## Sesi 3

| Tanggal | Bagian | Alat | Cara verifikasi |
|---|---|---|---|
| 2026-11-08 | Matriks belanja dan aturan keranjang; `gerbang-keranjang-ida` | Deepseek AI | `./vendor/bin/pest tests/Feature/Belanja`, `php artisan route:list --path=api/v1/keranjang` |

## Sesi 4

| Tanggal | Bagian | Alat | Cara verifikasi |
|---|---|---|---|
| 2026-11-09 | `PenomorPesanan`, `Pesanan`, `GembokCheckout`, `MesinStatusPesanan` | Deepseek AI | `./vendor/bin/pest tests/Unit/PenomorPesananTest.php tests/Feature/Belanja/PesananTest.php` |
| 2026-11-10 | `LayananCheckout`, `CheckoutResource`, pembatalan otomatis (`PembatalanOtomatis`) | Deepseek AI | `./vendor/bin/pest tests/Feature/Belanja/CheckoutTest.php`, `php artisan lapak:balapan keranjang-ganda` |
| 2026-11-11 | Perbaikan P1: kembalikan stok urut nomor produk; `PembatalanOtomatisTest` | Deepseek AI | `./vendor/bin/pest tests/Feature/Belanja/PembatalanOtomatisTest.php`, `php artisan lapak:balapan batal-saat-checkout` |

## Catatan

- Bagian yang dibantu AI: kerangka `PesananResource`, `CheckoutRequest`, skeleton `LayananCheckout`, dan potongan `PembatalanOtomatis`.
- Bagian yang ditulis sendiri: urutan kunci (keranjang dulu, produk urut nomor), `alamat_kirim` sebagai salinan `potret()`, dan satu-satunya pintu perubahan status lewat `MesinStatusPesanan`.
- Hal yang perlu diperbaiki setelah keluaran AI: pesanan "dibayar dua kali" dan "hidup lagi setelah batal" harus ditolak dengan 409 `LP-10`; diverifikasi lewat `tests/Feature/Belanja/PesananTest.php`.
- `PenomorPesananTest` memverifikasi format LPK dan tanggal WIB, bukan tanggal UTC.
