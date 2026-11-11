# Sesi 4 — Keamanan Transaksi

- Integrator: @nabelazahra-ptr26
- Tag penutup: `sesi-4`
- Status: selesai

## Papan unggahan

| PR | Unggahan | Cabang | Penulis | Isi | Status |
|---|---|---|---|---|---|
| #40 | Perbaikan | `fix/s3-ci-hijau` | @Rokkett2500 | CI Sesi 3: import controller, matriks identitas, import NamaIzin, Pint | Merged |
| #41 | Finalisasi | `fix/docs-3-1` | @Rokkett2500 | Sesi 2 & 3 selesai: papan sesi, aturan bisnis, tag sesi-3-1 | Merged |
| #42 | Pembuka | `sesi-4/pembuka-bela` | @nabelazahra-ptr26 | Papan sesi, urutan kunci baris, CODEOWNERS | Merged |
| #43 | A1 | `sesi-4/kontrak-idempoten-lintar` | @Rokkett2500 | Kunci idempotensi, middleware idempoten, `lapak:balapan` | Merged |
| #44 | A2 | `sesi-4/kontrak-alamat-dipta` | @ihsanpradipta45-alt | Izin `pesanan.buat`, tabel alamat, `PenjagaTransaksi` | Merged |
| #45 | A3 | `sesi-4/kontrak-stok-faiz` | @victor-isMe | `PenjagaStok` versi kontrak | Merged |
| #46 | A4 | `sesi-4/kontrak-pesanan-ida` | @mrdaknsh | Tabel checkout & pesanan, penomor, gembok, mesin status | Merged |
| #47 | A5 | `sesi-4/kontrak-pembayaran-bela` | @nabelazahra-ptr26 | Tabel pembayaran, `LayananPembayaran` | Merged |
| #48 | B1 | `sesi-4/alamat-dipta` | @ihsanpradipta45-alt | `BukuAlamat` dan tiga rute alamat | Merged |
| #49 | B2 | `sesi-4/checkout-mirda` | @mrdaknsh | Checkout atomik, pesanan saya, pembatalan otomatis | Merged |
| #50 | B2 | `sesi-4/keranjang-ganda-mirda` | @mrdaknsh | Skenario balapan `keranjang-ganda` | Merged |
| #51 | B3 | `sesi-4/kunci-stok-faiz` | @victor-isMe | Kunci baris produk, penyesuaian stok relatif | Merged |
| #52 | B4 | `sesi-4/webhook-bela` | @nabelazahra-ptr26 | Webhook bertanda tangan, dana tertahan di escrow | Merged |
| #53 | B5 | `sesi-4/balapan-ci-lintar` | @Rokkett2500 | Klasifikasi rute POST, skenario lintas domain, CI MySQL | Merged |
| #54 | P1 | `sesi-4/urutan-kunci-mirda` | @mrdaknsh | Perbaikan kebuntuan: kembalikan stok urut nomor produk | Merged |
| #55 | Penutup | `sesi-4/penutup-bela` | @nabelazahra-ptr26 | Penutup sesi 4, tag, rekap | Merged |

## Rekap kontribusi

```text
== Commit per anggota (sesi-3..sesi-4, tanpa merge commit)
     9	mrdaknsh
     8	Rokkett2500
     6	Pradipta-ihsan-ariyanto
     5	belazahra
     5	victor-isMe

== Baris ditambah dan dihapus per anggota
Pradipta-ihsan-ariyanto +920 -95
belazahra                +656 -6
victor-isMe              +541 -4
mrdaknsh                 +1870 -14
Rokkett2500              +1243 -30

== PR yang masuk ke main
   Merge pull request #53 from Kelompok-5-D3TIB-25/sesi-4/balapan-ci-lintar
   Merge pull request #54 from Kelompok-5-D3TIB-25/sesi-4/urutan-kunci-mirda
   Merge pull request #52 from Kelompok-5-D3TIB-25/sesi-4/webhook-bela
   Merge pull request #51 from Kelompok-5-D3TIB-25/sesi-4/kunci-stok-faiz
   Merge pull request #50 from Kelompok-5-D3TIB-25/sesi-4/keranjang-ganda-mirda
   Merge pull request #49 from Kelompok-5-D3TIB-25/sesi-4/checkout-mirda
   Merge pull request #48 from Kelompok-5-D3TIB-25/sesi-4/alamat-dipta
   Merge pull request #47 from Kelompok-5-D3TIB-25/sesi-4/kontrak-pembayaran-bela
   Merge pull request #46 from Kelompok-5-D3TIB-25/sesi-4/kontrak-pesanan-ida
   Merge pull request #45 from Kelompok-5-D3TIB-25/sesi-4/kontrak-stok-faiz
   Merge pull request #44 from Kelompok-5-D3TIB-25/sesi-4/kontrak-alamat-dipta
   Merge pull request #43 from Kelompok-5-D3TIB-25/sesi-4/kontrak-idempoten-lintar
   Merge pull request #42 from Kelompok-5-D3TIB-25/sesi-4/pembuka-bela
   Merge pull request #41 from Kelompok-5-D3TIB-25/fix/docs-3-1
   Merge pull request #40 from Kelompok-5-D3TIB-25/fix/s3-ci-hijau
```

## Catatan penutup

### Kendala

- `sesi-3` lama menunjuk pada main yang merah (Pint gagal dan 6 uji gagal). Diperbaiki lewat PR #40 (perbaikan kode) dan PR #41 (papan sesi Sesi 2 & 3, aturan bisnis), lalu tag `sesi-3-1` dipasang.
- Uji `AlamatTest` dari PR #44 gagal di main — `Class "Alamat" not found`, lalu `pengguna_id` NOT NULL — dan `phpunit.xml` tidak mengunggulkan SQLite atas `$_SERVER`, sehingga `env('DB_*')` di container docker masih menunjuk MySQL dan pengujian menulis-ulang tabel `lapak`. Diperbaiki di cabang A4 (`1676882`): impor dan `beforeEach` `AlamatTest`, `pengguna_id` di `AlamatFactory`, dan `<server>` `DB_CONNECTION=sqlite`/`DB_DATABASE=:memory:` di `phpunit.xml`. Sejak itu Pest berjalan di SQLite di memori (±12–15 detik).
- Skenario B5 `batal-saat-checkout` menemukan kebuntuan `1213` di MySQL: `PembatalanOtomatis` mengembalikan stok menurut urutan butir pesanan, sedangkan `PenjagaStok::ambil()` mengunci menurut nomor produk. Diperbaiki lewat PR #54; penyebabnya dikunci dengan uji yang memeriksa urutan `UPDATE produk` lewat query log, jadi regresi akan merah di SQLite juga. Setelah itu seluruh skenario balapan lulus konsisten.
- PR #48 (B1), #49 (B2), #50 (B2), #51 (B3), #52 (B4), dan #53 (B5) mergerg setelah masing-masing tinjauan lingkar (Dipta, Faiz, Mirda, Bela, Lintar), seperti yang diatur di papan sesi.
- Beberapa commit lokal ditulis dengan identitas pemilik cabang di mesin yang sama (mis. A5 dan penutup ditulis as `belazahra`); daftar penulis pada rekap mengikuti `git log --format='%an'`.
- `Pest` pada MySQL lewat flag `-e DB_CONNECTION=mysql` **tidak efektif** pada pengaturan ini, karena `phpunit.xml` memaksa `sqlite :memory:` lewat `<server>`. Pengujian Go-backed yang berjalan pada MySQL adalah migrate + balapan (`lapak:balapan --semua`) dan `lapak:periksa-buku`, di dalam CI tugas `kualitas-mysql`.

### Titipan untuk integrator Sesi 5

- `main` hijau pada Pest (SQLite) dan CI; tag `sesi-4` dipasang pada merge PR penutup.
- CI `kualitas-mysql` wajib lulus; bila lewat batas waktu atau skenario, perbaiki di tugas CI, bukan dengan melonggarkan aturan skenario.
- Lima titipan pada papan sesi: (1) pembayaran yang tiba setelah tagihan kedaluwarsa masih ditolak dan belum dikembalikan; (2) pintu penjual, konfirmasi terima, dan pembatalan oleh pembeli belum ada; (3) setiap rute POST baru wajib masuk `RuteIdempotenTest`; (4) catatan `kunci_idempotensi` belum dipangkas; (5) pelunasan dan pembatalan otomatis belum masuk log audit.
- Latihan 1–6 (bagian G dokumen Sesi 4) wajib selesai sebelum Sesi 5.
