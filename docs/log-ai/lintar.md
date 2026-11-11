# Catatan Penggunaan AI — Muhammad Halilintar Jagat Kertapati

## Sesi 1

| Tanggal | Bagian | Alat | Cara verifikasi |
|---|---|---|---|
| 2026-11-06 | Amplop respons, penanganan galat, health, dan CI | Inkling 2 Small Free | `./vendor/bin/pest tests/Feature/Sistem`, `php artisan route:list --path=api/v1` |

## Sesi 2

| Tanggal | Bagian | Alat | Cara verifikasi |
|---|---|---|---|
| 2026-11-07 | Guard sesi: `auth:sesi`, pembatas laju per konteks, `SesiSegar` | Beam Free | `./vendor/bin/pest tests/Feature/Sistem/GuardSesiTest.php` |
| 2026-11-08 | Gerbang CI, `phpunit.xml`, `lapak:periksa-buku`, RuteTerlindungTest | Deepseek Free | `./vendor/bin/pint --test`, `gh run list --limit 3` |

## Sesi 3

| Tanggal | Bagian | Alat | Cara verifikasi |
|---|---|---|---|
| 2026-11-08 | `NamaPeran`, `NamaIzin`, pemetaan peran → izin | Inkling 2 Small Free | `./vendor/bin/pest tests/Feature/Sistem/IzinTest.php` |
| 2026-11-08 | `PeriksaIzin`, `IngatanIzin`, `RuteTerlindungTest`, matriks otorisasi | Beam Free | `./vendor/bin/pest tests/Feature/Sistem/MatriksOtorisasiTest.php tests/Feature/Sistem/RuteTerlindungTest.php` |

## Sesi 4

| Tanggal | Bagian | Alat | Cara verifikasi |
|---|---|---|---|
| 2026-11-09 | `Idempoten` middleware, tabel `kunci_idempotensi`, `lapak:balapan` | Deepseek Free | `./vendor/bin/pest tests/Feature/Sistem`, `php artisan lapak:balapan serempak` |
| 2026-11-10 | `RuteIdempotenTest`, tiga skenario balapan baru (`KunciGanda`, `RebutanCheckout`, `BatalSaatCheckout`) | Inkling 2 Small Free | `./vendor/bin/pest --filter=RuteIdempoten`, `php artisan lapak:balapan --semua` |
| 2026-11-11 | CI tugas kedua `kualitas-mysql`; temuan kebuntuan di `PembatalanOtomatis` dan laporan ke tim | Beam Free | `./vendor/bin/pest`, `php artisan lapak:balapan batal-saat-checkout` |

## Catatan

- Bagian yang dibantu AI: kerangka `SkenarioBalapan`, `PanggilHttp`, `RuteIdempotenTest`, dan tugas `kualitas-mysql` di `ci.yml`.
- Bagian yang ditulis sendiri: daftar `POST_BERKUNCI` dan `POST_TANPA_KUNCI` beserta 11 alasan; cara `gatherMiddleware()` pada rute; dan catatan laporan kebuntuan yang diserahkan ke tim.
- Hal yang perlu diperbaiki setelah keluaran AI: `lapak:balapan --semua` keluar dengan kode 1 bila satu skenario melanggar; diverifikasi di CI tugas `kualitas-mysql`.
- `IngatanIzin` adalah cache izin permintaan; diverifikasi lewat `tests/Feature/Sistem/IngatanIzinTest.php`.
