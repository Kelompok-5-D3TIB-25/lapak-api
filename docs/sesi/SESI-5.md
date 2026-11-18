# Sesi 5 — Escrow, Saldo, Audit, dan Rilis

- Integrator: @Rokkett2500
- Tag penutup: `v1.0.0`
- Status: selesai

## Papan unggahan

| PR | Unggahan | Cabang | Penulis | Isi | Status |
|---|---|---|---|---|---|
| #65 | Pembuka | `sesi-5/pembuka-sekar` | @Rokkett2500 | Papan sesi, daftar periksa rilis, penataan dua berkas bersama | Merged |
| #66 | A1 | `sesi-5/kontrak-audit-sekar` | @Rokkett2500 | Tabel log audit dan kepala rantai, daftar peristiwa, rantai hash, PencatatAudit | Merged |
| #67 | A2 | `sesi-5/kontrak-dana-dimas` | @nabelazahra-ptr26 | Tabel pencairan, dompet penahan, LayananDana, tagihan dapat dibatalkan | Merged |
| #68 | A3 | `sesi-5/kontrak-pesanan-rani` | @mrdaknsh | Cap waktu proses, LayananPesanan, pendukung uji | Merged |
| #69 | B1 | `sesi-5/jejak-identitas-bambang` | @ihsanpradipta45-alt | Kunci lama terdeteksi; jejak masuk, keluar, dan peran | Merged |
| #77 | B2 | `sesi-5/pesanan-toko-bagas` | @victor-isMe | Izin pesanan.proses, lima rute pesanan toko, jejak moderasi | Merged |
| #78 | B3 | `sesi-5/tindakan-pembeli-rani` | @mrdaknsh | Terima, batal, batal checkout, penyelesaian otomatis, dua skenario | Merged |
| #79 | B4 | `sesi-5/pencairan-dimas` | @nabelazahra-ptr26 | Pembayaran terlambat, pencairan, invarian keenam, dua skenario | Merged |
| #80 | B5 | `sesi-5/rantai-audit-sekar` | @Rokkett2500 | Kunci kepala rantai, verifikasi, pintu auditor, invarian CI | Merged |
| #81 | Rilis | `sesi-5/rilis-sekar` | @Rokkett2500 | Uji penutup, versi 1.0.0, README, papan sesi | Merged |
| #82 | P1 | `sesi-5/jejak-stok-bagas` | @victor-isMe | Penyesuaian stok berjejak: temuan kandidat pertama | Merged |
| #83 | Catatan | `sesi-5/catatan-perubahan-bambang` | @ihsanpradipta45-alt | CHANGELOG.md | Merged |

## Rekap kontribusi

```text
== Commit per anggota (sesi-4..sesi-5, tanpa merge commit)
    11	belazahra
     6	Rokkett2500
     6	mrdaknsh
     4	victor-isMe
     4	Pradipta Ihsan

== PR yang masuk ke main
   Merge pull request #80 from Kelompok-5-D3TIB-25/sesi-5/rantai-audit-sekar
   Merge pull request #79 from Kelompok-5-D3TIB-25/sesi-5/pencairan-dimas
   Merge pull request #78 from Kelompok-5-D3TIB-25/sesi-5/tindakan-pembeli-rani
   Merge pull request #77 from Kelompok-5-D3TIB-25/sesi-5/pesanan-toko-bagas
   Merge pull request #69 from Kelompok-5-D3TIB-25/sesi-5/jejak-identitas-bambang
   Merge pull request #68 from Kelompok-5-D3TIB-25/sesi-5/kontrak-pesanan-rani
   Merge pull request #67 from Kelompok-5-D3TIB-25/sesi-5/kontrak-dana-dimas
   Merge pull request #66 from Kelompok-5-D3TIB-25/sesi-5/kontrak-audit-sekar
   Merge pull request #65 from Kelompok-5-D3TIB-25/sesi-5/pembuka-sekar
```

## Catatan penutup

### Kendala

- Dua peninjau per PR memaksa semua orang meninjau; hanya #65 dan #66 tidak menunggu lingkar penuh.
- Temuan kandidat pertama: penyesuaian stok belum berjejak (dikelola lewat PR #82).
- Tag v1.0.0 dipasang setelah `lapak:periksa-buku` hijau dan MySQL lolos seluruh `lapak:balapan`.

### Titipan untuk rilis v1.0.0

- main hijau pada Pest (SQLite) dan CI; tag v1.0.0 dipasang pada merge PR penutup.
- Naik versi dari sesi-4 diuji lewat `php artisan migrate` di atas data lama, bukan `migrate:fresh`.
- Jangkar `lapak:verifikasi-audit` disimpan di berita acara rilis.
