# Changelog

Semua perubahan penting Lapak dicatat di berkas ini. Format mengikuti
[Keep a Changelog](https://keepachangelog.com/id/1.0.0/), dan nomor versi mengikuti
[SemVer](https://semver.org/lang/id/).

## [1.0.0] — 2026-11-18

### Ditambahkan

- Dana dilepas ke penjual tepat satu kali: lapisan LayananPesanan di atas mesin status, dan
  penolak kedua di LayananDana bila jalan lain mencoba mengulang (LP-10, LP-12).
- Pencairan saldo penjual dengan dana ditahan sejak diajukan dan putusan orang kedua
  (`pencairan.ajukan`, `pencairan.setujui`) (LP-16).
- Log audit yang hanya dapat ditambah dan berantai hash SHA-256, dengan kepala rantai dan
  `lapak:verifikasi-audit`; pintu auditor baca-saja (`GET /audit`, `GET /audit/rantai`) (LP-17).
- Penyelesaian otomatis pesanan yang dikirim lebih dari 3 hari, dan pembatalan otomatis checkout
  yang kedaluwarsa.
- Pintu penjual atas pesanan tokonya: `GET` dan `POST /toko-saya/pesanan/...` (`pesanan.proses`).
- Tiga pintu pembeli: terima, batal pesanan, batal checkout (idempoten, batas laju, 404 untuk
  pesanan orang lain).
- Pembayaran yang tiba setelah tagihannya ditutup dibukukan masuk lalu dikembalikan, dengan status
  tagihan `dikembalikan`.
- Cap waktu proses pesanan: `dikemas_pada`, `dikirim_pada`, `nomor_resi`, `selesai_pada`,
  `dibatalkan_oleh`.
- Izin baru: `pesanan.proses`, `pencairan.ajukan`, `pencairan.setujui`, `audit.lihat`.
  Keuangan dan auditor kini dipisah dari admin; admin tidak memegang izin uang.
- Kunci sesi lama yang muncul lagi setelah rotasi mencabut sesinya dan dicatat di log audit (LP-14).
- Percobaan masuk gagal dicatat pada log audit (surel yang dicoba, tidak pernah kata sandinya).
- Invarian keenam `lapak:periksa-buku`: uang menunggu pencairan sama dengan jumlah pengajuan menunggu.

### Diperbaiki

- Kebuntuan pada pembatalan otomatis: urutan kunci stok kini mengikuti urutan checkout.
- Dompet penjual yang lahir serempak pada pelepasan dana pertama kini dijawab dengan dompet yang
  baru dibuat, bukan gagal karena unik.
- Penyesuaian stok relatif penjual kini menulis jejak `stok.disesuaikan` di log audit.
- `lapak:periksa-buku` memeriksa enam invarian; alur dari `sesi-4` ke `v1.0.0` diuji dengan
  `php artisan migrate` di atas data lama.

### Diubah

- Klien `POST /sesi/konfirmasi` wajib mengganti kunci simpanan sebelum mengirim permintaan lain.
  Memakai kunci lama setelah rotasi mencabut sesi (LP-14).
- `POST /masuk` menolak surel yang tidak berbentuk surel dengan 422 (sebelumnya 401).
- Webhook pembayaran untuk tagihan yang sudah ditutup dijawab 200 dengan
  `hasil: dikembalikan` (sebelumnya 409).
- Nomor versi `config('lapak.versi')` kini `1.0.0`; tag `v1.0.0` dan `sesi-5` dipasang pada commit
  yang sama.

### Dihapus

- Tidak ada.

[1.0.0]: https://github.com/Kelompok-5-D3TIB-25/lapak-api/releases/tag/v1.0.0
