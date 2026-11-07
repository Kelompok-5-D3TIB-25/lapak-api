# Catatan Penggunaan AI — Mirda Kinasih

## Sesi 1

| Tanggal | Bagian | Alat | Cara verifikasi |
|---|---|---|---|
| 2026-11-06 | Value object `Uang` dan buku besar | Deepseek AI | `./vendor/bin/pest tests/Unit/UangTest.php tests/Feature/Dompet`, `php artisan lapak:periksa-buku` |

## Catatan

- Bagian yang dibantu AI: kerangka `Uang`, `Dompet`, `MutasiSaldo`, `BukuBesar`, dan `PeriksaBuku`.
- Bagian yang ditulis sendiri: aturan LP-1, LP-8, LP-13 serta penjagaan mutasi pada `MutasiSaldo::booted`.
- Hal yang perlu diperbaiki setelah keluaran AI: pembulatan setengah ke atas pada persentase dan penanganan saldo negatif dompet eksternal; diverifikasi lewat `tests/Feature/Dompet/BukuBesarTest.php`.
