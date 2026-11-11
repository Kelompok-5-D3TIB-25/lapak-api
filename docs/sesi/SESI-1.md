# Sesi 1 — Fondasi Tim dan Repositori

- Integrator: @Rokkett2500
- Tag penutup: `sesi-1`
- Status: selesai

## Papan unggahan

| PR | Unggahan | Cabang | Penulis | Isi | Status |
|---|---|---|---|---|---|
| #1 | Pembuka | `sesi-1/fondasi` | @ihsanpradipta45-alt | Kerangka Laravel, konfigurasi, pemuat rute, Pest | Merged |
| #2 | Pembuka | `sesi-1/fondasi` | @victor-isMe | Dokumen tim | Merged |
| #3 | A3 | `sesi-1/fondasi` | @ihsanpradipta45-alt | Tabel, model, factory, dan akun contoh pengguna | Merged |
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
| #14 | B5 lanjutan | `sesi-1/buku-besar-ida` | @mrdaknsh | Perbaikan `KatalogSeeder` | Merged |
| #15 | B5 lanjutan | `sesi-1/buku-besar-ida` | @mrdaknsh | Pemeriksaan buku besar di CI | Merged |
| #16 | Penutup | `sesi-1/penutup-dipta` | @ihsanpradipta45-alt | Papan sesi, aturan bisnis, rekap | Merged |
| #17 | Finalisasi | `finalisasi-fix` | @Rokkett2500 | Perbaikan akhir dan penutupan | Merged |

> Penomoran mengikuti repo ini. HandsOn memakai #12 untuk penutup; repo ini menambah satu PR perbaikan
> dan tiga merge lanjutan pada cabang B5.

## Rekap kontribusi

```text
== Commit per anggota (sesi-1, tanpa merge commit)
    13	Rokkett2500
     9	Pradipta-ihsan-ariyanto
     8	mrdaknsh
     6	victor-isMe
     2	belazahra

== Baris ditambah dan dihapus per anggota
belazahra    +805 -81
mrdaknsh     +511 -26
Pradipta-ihsan-ariyanto +2437 -255
Rokkett2500  +14381 -521
victor-isMe  +754 -0

== PR yang masuk ke main
   Merge pull request #17 from Kelompok-5-D3TIB-25/finalisasi-fix
   Merge pull request #16 from Kelompok-5-D3TIB-25/sesi-1/penutup-dipta
   Merge pull request #15 from Kelompok-5-D3TIB-25/sesi-1/buku-besar-ida
   Merge pull request #14 from Kelompok-5-D3TIB-25/sesi-1/buku-besar-ida
   Merge pull request #13 from Kelompok-5-D3TIB-25/sesi-1/buku-besar-ida
   Merge pull request #12 from Kelompok-5-D3TIB-25/sesi-1/belanja-bela
   Merge pull request #11 from Kelompok-5-D3TIB-25/perbaikan-1to15-lintar
   Merge pull request #10 from Rokkett2500/sesi-1/katalog-faiz
   Merge pull request #9 from Rokkett2500/sesi-1/registrasi-dipta
   Merge pull request #8 from Rokkett2500/sesi-1/galat-dan-ci-lintar
   Merge pull request #5 from Rokkett2500/sesi-1/kontrak-dompet-dimas
   Merge pull request #7 from Rokkett2500/sesi-1/kontrak-katalog-faiz
   Merge pull request #4 from Rokkett2500/sesi-1/kontrak-uang-kelompok5
   Merge pull request #6 from Rokkett2500/kontrak-respons-lintar
   Merge pull request #3 from Rokkett2500/sesi-1/fondasi
   Merge pull request #2 from Rokkett2500/sesi-1/fondasi
   Merge pull request #1 from Rokkett2500/sesi-1/fondasi
```

## Catatan penutup

### Kendala

- Konflik `DatabaseSeeder.php` saat rebase diselesaikan dengan mempertahankan `PenggunaSeeder`, `KatalogSeeder`, dan `DompetSeeder`.
- Nama migration keranjang sempat tidak mengikuti pola `2026_10_01_300001`; sudah dirapikan pada PR finalisasi.
- `CODEOWNERS` sempat menukar kepemilikan migration D3 dan D4; sudah diperbaiki.
- `phpunit.xml` sempat tidak memaksa SQLite sehingga tes dari container berpotensi memakai MySQL; sudah diperbaiki dengan `force="true"`.
- Atribusi A2/A5 tertukar dari pembagian nama di HandsOn: A2 dikerjakan Mirda dan A5 oleh Bela. Kontrak kode tetap sesuai.

### Titipan untuk integrator Sesi 2

- `main` hijau dan tag `sesi-1` sudah dipasang.
- `docs/sesi/SESI-2.md` sudah disalin dari kerangka.
- Latihan mandiri 1–6 wajib selesai sebelum Sesi 2.
- CI memakai SQLite file (`src/database/database.sqlite`).
- `phpunit.xml` sudah memaksa SQLite di memori; jangan menghapus atribut `force="true"`.

## Catatan histori

Histori `main` ditulis ulang (identitas penulis dan pesan komit diacak demi keterbacaan); sehingga rekap `git shortlog` pada papan sesi ini tidak lagi bisa direproduksi, dan seluruh SHA yang disebut sebagai patokan di atas tidak lagi geçerli. Isi pohon (tree) tidak berubah.

