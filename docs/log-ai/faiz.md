# Catatan Penggunaan AI — Muhammad Faiz Niwansyah

## Sesi 1

| Tanggal | Bagian | Alat | Cara verifikasi |
|---|---|---|---|
| 2026-11-06 | Skema katalog dan endpoint publik | Gemini | `./vendor/bin/pest tests/Feature/Katalog`, `php artisan migrate:fresh --seed` |

## Sesi 2

| Tanggal | Bagian | Alat | Cara verifikasi |
|---|---|---|---|
| 2026-11-08 | `TokoPolicy`, `ProdukPolicy`, kontrak katalog, `PenyusunBelanja` | ChatGPT | `./vendor/bin/pest tests/Feature/Katalog`, `php artisan route:list` |
| 2026-11-08 | `TokoSayaController`, `ProdukSayaController`, penjagaan kepemilikan | Claude | `./vendor/bin/pest tests/Feature/Katalog/TokoSayaTest.php tests/Feature/Katalog/ProdukSayaTest.php` |

## Sesi 3

| Tanggal | Bagian | Alat | Cara verifikasi |
|---|---|---|---|
| 2026-11-09 | `ModerasiToko`, aturan admin tidak boleh moderasi toko sendiri | Gemini | `./vendor/bin/pest tests/Feature/Katalog/ModerasiTokoTest.php`, `php artisan route:list` |

## Sesi 4

| Tanggal | Bagian | Alat | Cara verifikasi |
|---|---|---|---|
| 2026-11-09 | `RebutanStok` (pertama kali), lalu perbaikan dua baris `PenjagaStok::ambil()` | ChatGPT | `php artisan lapak:balapan rebutan-stok`, `./vendor/bin/pest tests/Feature/Katalog/PenjagaStokTest.php` |
| 2026-11-10 | `PenyesuaianStokRequest`, `PenyesuaianStokDitolak`, `PenjagaStok::sesuaikan()` | Claude | `./vendor/bin/pest tests/Feature/Katalog/PenyesuaianStokTest.php`, `php artisan lapak:balapan penyesuaian-stok` |

## Catatan

- Bagian yang dibantu AI: kerangka `TokoPolicy`, `ModerasiToko`, `PenjagaStok::ambil()`, dan potongan `RebutanStok`.
- Bagian yang ditulis sendiri: aturan LP-4 dan LP-11 khusus penyesuaian stok; larangan moderasi toko sendiri; dan `'prohibited'` pada `PATCH /toko-saya/produk/{id}` untuk `stok`.
- Hal yang perlu diperbaiki setelah keluaran AI: `PenjagaStok::ambil()` semula tidak memastikan dipanggil di dalam `DB::transaction`; diganti wajib di dalam transaksi dengan `LogicException`; diverifikasi lewat `tests/Feature/Katalog/PenjagaStokTest.php`.
- `lapak:balapan rebutan-stok` dipakai dua kali: sekali terhadap versi kontrak (PELANGGARAN), sekali setelah perbaikan (LULUS).
