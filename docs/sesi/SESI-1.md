# Sesi 1 — Fondasi Tim dan Repositori

- Integrator: @Rokkett2500
- Tag penutup: `sesi-1`
- Status: penutup

## Papan unggahan

| PR | Unggahan | Cabang | Penulis | Isi | Status |
|---|---|---|---|---|---|
| #1 | Pembuka | `sesi-1/fondasi` | Kelimanya | Kerangka Laravel, konfigurasi, pemuat rute | Merged |
| #2 | Pembuka | `sesi-1/fondasi` | Kelimanya | Lanjutan fondasi | Merged |
| #3 | Pembuka | `sesi-1/fondasi` | Kelimanya | Dokumen tim | Merged |
| #4 | A2 | `sesi-1/kontrak-uang-kelompok5` | @mrdaknsh | Value object `Uang` | Merged |
| #5 | A5 | `sesi-1/kontrak-dompet-dimas` | @nabelazahra-ptr26 | Skema dan model dompet | Merged |
| #6 | A1 | `kontrak-respons-lintar` | @Rokkett2500 | Amplop respons dan galat domain | Merged |
| #7 | A4 | `sesi-1/kontrak-katalog-faiz` | @victor-isMe | Skema dan model katalog | Merged |
| #8 | B1 | `sesi-1/galat-dan-ci-lintar` | @Rokkett2500 | Galat, health, CI | Merged |
| #9 | B2 | `sesi-1/registrasi-dipta` | @ihsanpradipta45-alt | Registrasi pengguna | Merged |
| #10 | B3 | `sesi-1/katalog-faiz` | @victor-isMe | Katalog publik | Merged |
| #11 | Perbaikan | `perbaikan-1to15-lintar` | @Rokkett2500 | Perbaikan Langkah 1–15 | Merged |
| #12 | B4 | `sesi-1/belanja-bela` | @nabelazahra-ptr26 | Keranjang dan simulasi belanja | Merged |
| #13 | B5 | `sesi-1/buku-besar-ida` | @mrdaknsh | Buku besar dan pemeriksaannya | Merged |
| #14 | Penutup | `sesi-1/penutup-...` | Integrator | Papan sesi, aturan, rekap | Draf |

> Penomoran PR mengikuti repo ini. HandsOn memakai #12 untuk penutup; di sini #11 adalah PR perbaikan, sehingga seluruh nomor bergeser.

## Catatan penutup

### Kendala

- Konflik `DatabaseSeeder.php` saat rebase; diselesaikan dengan menyimpan `KatalogSeeder` dan `DompetSeeder`.
- Nama migration keranjang sempat tidak mengikuti pola `2026_10_01_300001`.
- `CODEOWNERS` sempat menukar kepemilikan D3 dan D4.

### Titipan untuk integrator Sesi 2

- Salin kerangka papan ini menjadi `docs/sesi/SESI-2.md`.
- Latihan mandiri 1–6 wajib selesai sebelum Sesi 2.
- CI memakai SQLite file (`src/database/database.sqlite`), bukan `:memory:`.
- Isi dokumen domain yang masih kosong sebelum sesi berikutnya.