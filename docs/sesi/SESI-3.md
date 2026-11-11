# Sesi 3 — Peran, Izin, dan Kewenangan atas Objek

- Integrator: @victor-isMe
- Tag penutup: `sesi-3` (+= `sesi-3-1`)
- Status: selesai

## Papan unggahan

| PR | Unggahan | Cabang | Penulis | Isi | Status |
|---|---|---|---|---|---|
| #31 | Pembuka | `sesi-3/pembuka-mirda` | @mrdaknsh | Papan sesi, pembaruan `CODEOWNERS`, matriks keamanan | Merged |
| #32 | A1 | `sesi-3/kontrak-izin-lintar` | @Rokkett2500 | NamaIzin, NamaPeran, middleware PeriksaIzin, matriks otorisasi | Merged |
| #33 | A2 | `sesi-3/kontrak-peran-dipta` | @ihsanpradipta45-alt | PengelolaPeran, state factory peran | Merged |
| #34 | B1 | `sesi-3/peran-pengguna-dipta` | @ihsanpradipta45-alt | Registrasi peran pembeli, pintu admin peran | Merged |
| #35 | B2 | `sesi-3/ingatan-izin-lintar` | @Rokkett2500 | Ingatan izin, penjaga pintu izin | Merged |
| #36 | B3 | `sesi-3/moderasi-toko-faiz` | @victor-isMe | Izin toko dan produk, moderasi admin, policy | Merged |
| #37 | B4 | `sesi-3/gerbang-keranjang-ida` | @mrdaknsh | Izin keranjang, aturan status pesanan | Merged |
| #38 | B5 | `sesi-3/buku-besar-bela` | @nabelazahra-ptr26 | Buku besar untuk staf, perintah `lapak:matriks-izin` | Merged |
| #39 | Penutup | `sesi-3/penutup-ida` | @mrdaknsh | Penutup, tag sesi-3 | Merged |

## Rekap kontribusi

```text
    == Commit per anggota (sesi-2..sesi-3, tanpa merge commit)
         5	Rokkett2500
         4	Pradipta-ihsan-ariyanto
         4	mrdaknsh
         2	belazahra
         1	victor-isMe

    == Baris ditambah dan dihapus per anggota
    belazahra    +256 -14
    mrdaknsh     +304 -8
    Pradipta-ihsan-ariyanto +789 -131
    Rokkett2500  +708 -10
    victor-isMe  +494 -17

    == PR yang masuk ke main
       Merge pull request #39 from Kelompok-5-D3TIB-25/sesi-3/penutup-ida
       Merge pull request #38 from Kelompok-5-D3TIB-25/sesi-3/buku-besar-bela
       Merge pull request #37 from Kelompok-5-D3TIB-25/sesi-3/gerbang-keranjang-ida
       Merge pull request #36 from Kelompok-5-D3TIB-25/sesi-3/moderasi-toko-faiz
       Merge pull request #35 from Kelompok-5-D3TIB-25/sesi-3/ingatan-izin-lintar
       Merge pull request #34 from Kelompok-5-D3TIB-25/sesi-3/peran-pengguna-dipta
       Merge pull request #33 from Kelompok-5-D3TIB-25/sesi-3/kontrak-peran-dipta
       Merge pull request #32 from Kelompok-5-D3TIB-25/sesi-3/kontrak-izin-lintar
       Merge pull request #31 from Kelompok-5-D3TIB-25/sesi-3/pembuka-mirda
```

## Catatan penutup

### Kendala

- PR #39 dan main merah setelah Sesi 3: Pint gagal (`IzinTerpakaiTest.php`) dan 6 uji Pest gagal (missing import controller, matriks identitas yang belum dibuat, dan `use NamaIzin` yang belum ada). Diperbaiki lewat PR perbaikan dan tag `sesi-3-1`.
- Matriks otorisasi `identitas.php` belum dibuat saat PR B1, sehingga `MatriksOtorisasiTest::it memiliki tepat satu baris ...` gagal (8 rute tanpa baris matriks).
- Command `php artisan route:list --path=api/v1/admin` gagal ReflectionException karena missing import `ModerasiTokoController`.

### Titipan untuk integrator Sesi 4

- `main` hijau dan tag `sesi-3` (`+ sesi-3-1`) sudah dipasang.
- `docs/sesi/SESI-4.md` sudah disalin dari kerangka.
- Latihan mandiri 1–6 wajib selesai sebelum Sesi 4.
- Repository: LAPAK board (Hanggeoon? no). CI memakai SQLite; jangan hapus `force="true"` pada `src/phpunit.xml`.

## Catatan histori

Histori `main` ditulis ulang (identitas penulis dan pesan komit diacak demi keterbacaan); sehingga rekap `git shortlog` pada papan sesi ini tidak lagi bisa direproduksi, dan seluruh SHA yang disebut sebagai patokan di atas tidak lagi geçerli. Isi pohon (tree) tidak berubah.

