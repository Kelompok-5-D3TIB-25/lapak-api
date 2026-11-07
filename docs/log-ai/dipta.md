# Catatan Penggunaan AI — Pradipta Ihsan Ariyanto

## Sesi 1

| Tanggal | Bagian | Alat | Cara verifikasi |
|---|---|---|---|
| 2026-11-06 | Registrasi pengguna dan resource respons | Deepseek AI | `./vendor/bin/pest tests/Feature/Identitas`, tinjau diff |

## Catatan

- Bagian yang dibantu AI: kerangka `RegistrasiRequest`, `PenggunaResource`, dan `RegistrasiController`.
- Bagian yang ditulis sendiri: aturan normalisasi surel, validasi kata sandi, dan pengecualian `kata_sandi` dari respons.
- Hal yang perlu diperbaiki setelah keluaran AI: penyesuaian pesan validasi dan status `201`; diverifikasi lewat `tests/Feature/Identitas/RegistrasiTest.php`.
