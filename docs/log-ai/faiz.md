# Catatan Penggunaan AI — Muhammad Faiz Niwansyah

## Sesi 1

| Tanggal | Bagian | Alat | Cara verifikasi |
|---|---|---|---|
| 2026-11-06 | Skema katalog dan endpoint publik | Deepseek AI | `./vendor/bin/pest tests/Feature/Katalog`, `php artisan migrate:fresh --seed` |

## Catatan

- Bagian yang dibantu AI: kerangka migration `toko`, `kategori`, `produk`, factory, dan `KatalogController`.
- Bagian yang ditulis sendiri: scope `tampil`, `saring`, `urut`, aturan slug, dan validasi `KatalogRequest`.
- Hal yang perlu diperbaiki setelah keluaran AI: escape wildcard `%` dan `_` pada pencarian; diverifikasi lewat `tests/Feature/Katalog/KatalogTest.php`.
