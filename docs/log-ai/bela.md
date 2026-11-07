# Catatan Penggunaan AI — Nabela Azzahra Putri

## Sesi 1

| Tanggal | Bagian | Alat | Cara verifikasi |
|---|---|---|---|
| 2026-11-06 | Keranjang dan simulasi belanja | Deepseek AI | `./vendor/bin/pest tests/Unit/PenghitungBelanjaTest.php tests/Feature/Belanja`, tinjau diff |

## Catatan

- Bagian yang dibantu AI: kerangka `BarisBelanja`, `RincianToko`, `PenghitungBelanja`, `PenyusunBelanja`, dan `SimulasiBelanjaController`.
- Bagian yang ditulis sendiri: aturan LP-6, LP-7, LP-8 serta penyesuaian nama kolom dan pesan galat.
- Hal yang perlu diperbaiki setelah keluaran AI: pembulatan komisi dan pemisahan ongkir; diverifikasi lewat `tests/Unit/PenghitungBelanjaTest.php` dan `tests/Feature/Belanja/SimulasiBelanjaTest.php`.
