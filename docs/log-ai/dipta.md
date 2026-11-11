# Catatan Penggunaan AI — Pradipta Ihsan Ariyanto

## Sesi 1

| Tanggal | Bagian | Alat | Cara verifikasi |
|---|---|---|---|
| 2026-11-06 | Registrasi pengguna dan resource respons | Deepseek AI | `./vendor/bin/pest tests/Feature/Identitas`, tinjau diff |

## Sesi 2

| Tanggal | Bagian | Alat | Cara verifikasi |
|---|---|---|---|
| 2026-11-07 | Kontrak sesi: kunci sesi, penerbitan, pemeriksa kunci | Deepseek AI | `./vendor/bin/pest tests/Unit/KunciSesiTest.php` |
| 2026-11-08 | Masuk/keluar/sesi/konfirmasi; `PenerbitSesi` | Deepseek AI | `./vendor/bin/pest tests/Feature/Identitas/SesiTest.php tests/Feature/Sistem/GuardSesiTest.php` |

## Sesi 3

| Tanggal | Bagian | Alat | Cara verifikasi |
|---|---|---|---|
| 2026-11-08 | `PengelolaPeran` + pencabutan sesi, state factory peran | Deepseek AI | `./vendor/bin/pest tests/Feature/Identitas`, tinjau diff |
| 2026-11-09 | Pintu admin peran: `PeranPenggunaController`, `PenggunaPolicy` | Deepseek AI | `./vendor/bin/pest tests/Feature/Identitas/PeranPenggunaTest.php`, `php artisan route:list` |

## Sesi 4

| Tanggal | Bagian | Alat | Cara verifikasi |
|---|---|---|---|
| 2026-11-09 | Tabel alamat, `Alamat` (`potret()`), `AlamatFactory` | Deepseek AI | `./vendor/bin/pest tests/Feature/Identitas/AlamatTest.php`, `php artisan migrate:fresh --seed` |
| 2026-11-10 | `BukuAlamat` dengan kunci baris pengguna; `AlamatPertama` | Deepseek AI | `./vendor/bin/pest tests/Feature/Identitas`, `php artisan lapak:balapan alamat-pertama` |

## Catatan

- Bagian yang dibantu AI: kerangka `KunciSesi`, `PenerbitSesi`, `PengelolaPeran`, `AlamatFactory`, dan potongan `BukuAlamat`.
- Bagian yang ditulis sendiri: aturan LP-14 dan LP-15 (sesi segar 10 menit), normalisasi surel, dan urutan cek 401 → 403 di matriks.
- Hal yang perlu diperbaiki setelah keluaran AI: kunci disimpan hanya sebagai hash; kunci utuh hanya ada di memori saat login; diverifikasi lewat `tests/Unit/KunciSesiTest.php` dan `tests/Feature/Identitas/SesiTest.php`.
- Tabel `alamat` diuji lewat skenario `alamat-pertama` pada MySQL; satu alamat utama, tidak melebihi batas lima.
