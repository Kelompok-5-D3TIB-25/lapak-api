# Sesi 2 — Identitas dan Sesi dengan Kunci

- Integrator: @ihsanpradipta45-alt
- Tag penutup: `sesi-2`
- Status: selesai

## Papan unggahan

| PR | Unggahan | Cabang | Penulis | Isi | Status |
|---|---|---|---|---|---|
| #18 | Finalisasi | `finalisasi-rekap` | @Rokkett2500 | Rekap kontribusi Sesi 1 | Merged |
| #19 | Pembuka | `sesi-2/pembuka-faiz` | @victor-isMe | Papan sesi, pembaruan `CODEOWNERS` | Merged |
| #20 | A1 | `sesi-2/kontrak-sesi-dipta` | @ihsanpradipta45-alt | Format kunci sesi beserta ujinya, tabel sesi, penerbit dan pemeriksa | Merged |
| #21 | A2 | `sesi-2/kontrak-guard-lintar` | @Rokkett2500 | Guard sesi, sesi segar, pembatas laju, alat bantu uji | Merged |
| #22 | A3 | `sesi-2/kontrak-toko-faiz` | @victor-isMe | Penanda kepemilikan toko dan produk | Merged |
| #23 | A4 | `sesi-2/kontrak-keranjang-ida` | @mrdaknsh | Skema dan model keranjang | Merged |
| #24 | A5 | `sesi-2/kontrak-dompet-bela` | @nabelazahra-ptr26 | Skema dan model dompet, riwayat mutasi (LP-15) | Merged |
| #25 | B1 | `sesi-2/masuk-dipta` | @ihsanpradipta45-alt | Masuk, keluar, sesi, konfirmasi kata sandi | Merged |
| #26 | B2 | `sesi-2/gerbang-lintar` | @Rokkett2500 | Rute terlindung, izin, matriks otorisasi | Merged |
| #27 | B3 | `sesi-2/toko-saya-faiz` | @victor-isMe | Buka/ubah toko, kelola produk sendiri | Merged |
| #28 | B4 | `sesi-2/keranjang-ida` | @mrdaknsh | Endpoint lihat, taruh, hapus, kosongkan keranjang | Merged |
| #29 | B5 | `sesi-2/mutasi-bela` | @nabelazahra-ptr26 | Endpoint saldo dompet sendiri, riwayat mutasi | Merged |
| #30 | Penutup | `sesi-2/penutup-faiz` | @victor-isMe | Penutup sesi 2, tag, rekap | Merged |

## Rekap kontribusi

```text
== Commit per anggota (sesi-1..sesi-2, tanpa merge commit)
     7	Rokkett2500
     7	victor-isMe
     5	mrdaknsh
     4	Pradipta-ihsan-ariyanto
     4	belazahra

== Baris ditambah dan dihapus per anggota
belazahra    +243 -5
mrdaknsh     +468 -23
Pradipta-ihsan-ariyanto +846 -11
Rokkett2500  +415 -10
victor-isMe  +545 -45

== PR yang masuk ke main
   Merge pull request #30 from Kelompok-5-D3TIB-25/sesi-2/penutup-faiz
   Merge pull request #29 from Kelompok-5-D3TIB-25/sesi-2/mutasi-bela
   Merge pull request #28 from Kelompok-5-D3TIB-25/sesi-2/keranjang-ida
   Merge pull request #27 from Kelompok-5-D3TIB-25/sesi-2/toko-saya-faiz
   Merge pull request #26 from Kelompok-5-D3TIB-25/sesi-2/gerbang-lintar
   Merge pull request #25 from Kelompok-5-D3TIB-25/sesi-2/masuk-dipta
   Merge pull request #24 from Kelompok-5-D3TIB-25/sesi-2/kontrak-dompet-bela
   Merge pull request #23 from Kelompok-5-D3TIB-25/sesi-2/kontrak-keranjang-ida
   Merge pull request #22 from Kelompok-5-D3TIB-25/sesi-2/kontrak-toko-faiz
   Merge pull request #21 from Kelompok-5-D3TIB-25/sesi-2/kontrak-guard-lintar
   Merge pull request #20 from Kelompok-5-D3TIB-25/sesi-2/kontrak-sesi-dipta
   Merge pull request #19 from Kelompok-5-D3TIB-25/sesi-2/pembuka-faiz
   Merge pull request #18 from Kelompok-5-D3TIB-25/finalisasi-rekap
```

## Catatan penutup

### Kendala

- Email Bela di `Credentials Kelompok 5.txt` mengandung karakter tak terlihat (U+2060 Word Joiner), sehingga commit Bela tidak teratribusi ke profil GitHub. Berkas diperbaiki dan kredensial baru dipakai; tag `sesi-2` menunjuk ke merge akhir Sesi 2 (`a07520d`).

### Titipan untuk integrator Sesi 3

- `main` hijau dan tag `sesi-2` sudah dipasang.
- `docs/sesi/SESI-3.md` sudah disalin dari kerangka.
- `phpunit.xml` memaksa SQLite di memori (`force="true"`); jangan menghapus atribut ini.
