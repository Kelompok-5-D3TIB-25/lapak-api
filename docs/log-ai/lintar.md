# Catatan Penggunaan AI — Muhammad Halilintar Jagat Kertapati

## Sesi 1

| Tanggal | Bagian | Alat | Cara verifikasi |
|---|---|---|---|
| 2026-11-06 | Amplop respons, penanganan galat, health, dan CI | Deepseek AI | `./vendor/bin/pest tests/Feature/Sistem`, `php artisan route:list --path=api/v1` |

## Catatan

- Bagian yang dibantu AI: kerangka `ResponsApi`, `GalatDomain`, `PenanganGalatApi`, `PaksaJson`, `IdPermintaan`, dan `KesehatanController`.
- Bagian yang ditulis sendiri: pemetaan kode galat, header `X-Request-Id`, serta urutan langkah CI.
- Hal yang perlu diperbaiki setelah keluaran AI: pastikan 404/405/422 tetap berbentuk JSON tanpa header `Accept`; diverifikasi lewat `tests/Feature/Sistem/KontrakGalatTest.php`.
