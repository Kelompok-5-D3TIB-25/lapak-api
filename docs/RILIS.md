# Daftar Periksa Rilis

## Kandidat

- v1.0.0-rc.1: commit kandidat pertama
- v1.0.0-rc.2: commit kandidat kedua setelah perbaikan temuan

## Verifikasi

- Klon baru dari tag kandidat
- Basis data kosong
- `composer install`
- `php artisan migrate --seed`
- `./vendor/bin/pest`
- `DB_CONNECTION=mysql DB_DATABASE=lapak_uji ./vendor/bin/pest`
- `php artisan lapak:balapan --semua`
- `php artisan lapak:periksa-buku`

## Naik versi

- `php artisan migrate` di atas data lama
- lapak_naik basis data untuk uji naik versi

## Rilis

- CHANGELOG.md terisi
- Tag beranotasi v1.0.0 pada commit yang sama dengan sesi-5
- Tag sesi-5 dipasang pada commit yang sama