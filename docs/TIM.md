# Tim Lapak

Marketplace UMKM Solo Raya — Praktik Pemrograman Back End, D3 Teknik Informatika UNS.

## Susunan tim

| Nama | NIM | Akun GitHub | Domain |
|---|---|---|---|
| Muhammad Halilintar Jagat Kertapati | V3425071 | @Rokkett2500 | D5 Otorisasi, Audit, dan Kualitas |
| Pradipta Ihsan Ariyanto | V3425079 | @ihsanpradipta45-alt | D1 Identitas dan Sesi |
| Muhammad Faiz Niwansyah | V3425013 | @victor-isMe | D2 Toko dan Katalog |
| Nabela Azzahra Putri | V3425015 | @nabelazahra-ptr26 | D3 Keranjang dan Pesanan |
| Mirda Kinasih | V3425070 | @mrdaknsh | D4 Pembayaran dan Saldo |

## Domain

| Kode | Domain | Tabel | Pemilik |
|---|---|---|---|
| D1 | Identitas dan Sesi | `pengguna` | @ihsanpradipta45-alt |
| D2 | Toko dan Katalog | `toko`, `kategori`, `produk` | @victor-isMe |
| D3 | Keranjang dan Pesanan | `keranjang`, `butir_keranjang` | @nabelazahra-ptr26 |
| D4 | Pembayaran dan Saldo | `dompet`, `mutasi_saldo` | @mrdaknsh |
| D5 | Otorisasi, Audit, dan Kualitas | `peran`, `izin`, `log_audit` (Sesi 3+) | @Rokkett2500 |

## Integrator bergilir

| Sesi | Integrator | Tag penutup |
|---|---|---|
| 1 | @Rokkett2500 | `sesi-1` |
| 2 | @ihsanpradipta45-alt | `sesi-2` |
| 3 | @victor-isMe | `sesi-3` (+ `sesi-3-1`) |
| 4 | @nabelazahra-ptr26 | `sesi-4` |
| 5 | @mrdaknsh | `v1.0.0` |

Tugas integrator:

1. Membuka sesi: menulis papan sesi dan memperbarui `.github/CODEOWNERS`.
2. Menjaga urutan merge; hanya integrator yang menekan tombol merge.
3. Menjalankan `main` dari basis data kosong setelah setiap jendela unggah.
4. Menutup sesi: mengisi papan sesi, memasang tag, menjalankan rekap kontribusi, dan serah terima.

Integrator tidak menyetujui PR miliknya sendiri.

## Lingkar tinjauan Sesi 1

| Penulis | Peninjau |
|---|---|
| @ihsanpradipta45-alt | @victor-isMe |
| @victor-isMe | @nabelazahra-ptr26 |
| @nabelazahra-ptr26 | @mrdaknsh |
| @mrdaknsh | @Rokkett2500 |
| @Rokkett2500 | @ihsanpradipta45-alt |

Reviewer menarik cabang, menjalankan migrasi dan pengujian, lalu mencoba langkah "Cara menguji" pada PR. Persetujuan tanpa menjalankan bukan review.

## Aturan dasar

- Setiap anggota commit atas nama dan surel sendiri.
- Nama cabang: `sesi-<n>/<jenis>-<nama>`.
- Migration: `2026_10_SS_DNNNNN_nama.php`.
- Berkas bersama hanya disunting pemiliknya; lihat `CONTRIBUTING.md`.