# Peta Skema

## Penomoran migration

Pola: `2026_10_SS_DNNNNN_nama.php`

- `SS` = nomor sesi (01)
- `D` = nomor domain (1–5)
- `NNNNN` = nomor urut

Contoh: `2026_10_01_200003_buat_tabel_produk.php` adalah migration ketiga D2 pada Sesi 1.

Dengan pola ini urutan migrasi mengikuti urutan domain, bukan siapa yang lebih dulu mengetik `make:migration`.

## Kepemilikan tabel

| Domain | Tabel | Migration |
|---|---|---|
| D1 | `pengguna` | `2026_10_01_100001_buat_tabel_pengguna.php` |
| D2 | `toko` | `2026_10_01_200001_buat_tabel_toko.php` |
| D2 | `kategori` | `2026_10_01_200002_buat_tabel_kategori.php` |
| D2 | `produk` | `2026_10_01_200003_buat_tabel_produk.php` |
| D3 | `keranjang` | `2026_10_01_300001_buat_tabel_keranjang.php` |
| D3 | `butir_keranjang` | (satu migration dengan `keranjang`) |
| D4 | `dompet` | `2026_10_01_400001_buat_tabel_dompet.php` |
| D4 | `mutasi_saldo` | `2026_10_01_400002_buat_tabel_mutasi_saldo.php` |
| D1 | `sesi` | Sesi 2 |
| D1 | `alamat` | Sesi 4 |
| D3 | `checkout` | `2026_10_04_300001_buat_tabel_checkout_dan_pesanan.php` |
| D3 | `pesanan` | `2026_10_04_300001_buat_tabel_checkout_dan_pesanan.php` |
| D3 | `butir_pesanan` | `2026_10_04_300001_buat_tabel_checkout_dan_pesanan.php` |
| D4 | `pembayaran` | `2026_10_04_400001_buat_tabel_pembayaran.php` |
| D5 | `peran`, `izin` (`log_audit` — Sesi 5) | Sesi 3 |
| D2 | kolom moderasi `dimoderasi_oleh`, `dimoderasi_pada`, `catatan_moderasi` pada `toko` | `2026_10_03_200001_tambah_moderasi_pada_toko.php` |
| D5 | `kunci_idempotensi` | `2026_10_04_500001_buat_tabel_kunci_idempotensi.php` |

## Arah foreign key

Foreign key hanya boleh menunjuk domain bernomor sama atau lebih kecil.

```text
pengguna (D1) <-- toko (D2) <-- produk (D2) <-- butir_keranjang (D3)
pengguna (D1) <-- keranjang (D3)
pengguna (D1) <-- dompet (D4) <-- mutasi_saldo (D4)
pengguna (D1) <-- sesi (D1)
pengguna (D1) <-- alamat (D1)
pengguna (D1) <-- checkout (D3) <-- pesanan (D3) <-- butir_pesanan (D3)
produk (D2) <-- butir_pesanan (D3)
toko (D2) <-- pesanan (D3)
checkout (D3) <-- pembayaran (D4)
pengguna (D1) <-- kunci_idempotensi (D5)
pengguna (D1) <-- peran (D5) <-- peran_izin (D5) --> izin (D5)
pengguna (D1) <-- pengguna_peran (D5) --> peran (D5)
```

`pesanan` tidak menunjuk ke `pembayaran`, walau itu yang pertama terpikir: D3 tidak boleh
menunjuk ke D4. Keduanya menunjuk ke `checkout`, induk bersama milik D3 (LP-7). `pesanan` juga
tidak menunjuk ke `alamat`: alamat disalin ke kolom `alamat_kirim` pada saat checkout.

Kontrak tanpa tabel: `ResponsApi` dan `GalatDomain` (D5) dipakai semua domain; `Uang` (D3) dipakai D2, D3, dan D4.

## Rincian kolom

### `pengguna` (D1)

| Kolom | Tipe | Catatan |
|---|---|---|
| `id` | bigint PK | |
| `nama` | string(100) | |
| `surel` | string(150) | unik, dinormalkan |
| `telepon` | string(20) | nullable |
| `kata_sandi` | string | hash bcrypt |
| `status` | string(20) | `aktif`, `ditangguhkan`; default `aktif` |
| `created_at`, `updated_at` | timestamp | |

### `toko` (D2)

| Kolom | Tipe | Catatan |
|---|---|---|
| `id` | bigint PK | |
| `pemilik_id` | FK `pengguna.id` | unik (LP-3) |
| `nama` | string(80) | |
| `slug` | string(100) | unik |
| `kota` | string(60) | |
| `deskripsi` | text | nullable |
| `ongkir_rupiah` | unsignedBigInteger | default 0 |
| `status` | string(20) | `menunggu`, `aktif`, `ditangguhkan`; index |

### `kategori` (D2)

| Kolom | Tipe | Catatan |
|---|---|---|
| `id` | bigint PK | |
| `nama` | string(60) | |
| `slug` | string(80) | unik |

### `produk` (D2)

| Kolom | Tipe | Catatan |
|---|---|---|
| `id` | bigint PK | |
| `toko_id` | FK `toko.id` | restrict on delete |
| `kategori_id` | FK `kategori.id` | restrict on delete |
| `nama` | string(120) | |
| `slug` | string(140) | unik |
| `deskripsi` | text | nullable |
| `harga_rupiah` | unsignedBigInteger | index |
| `stok` | unsignedInteger | default 0 |
| `berat_gram` | unsignedInteger | default 0 |
| `status` | string(20) | `draf`, `aktif`, `arsip` |

Index tambahan: `['status', 'kategori_id']`, `harga_rupiah`.

### `keranjang` (D3)

| Kolom | Tipe | Catatan |
|---|---|---|
| `id` | bigint PK | |
| `pengguna_id` | FK `pengguna.id` | unik; cascade delete |

### `butir_keranjang` (D3)

| Kolom | Tipe | Catatan |
|---|---|---|
| `id` | bigint PK | |
| `keranjang_id` | FK `keranjang.id` | cascade delete |
| `produk_id` | FK `produk.id` | restrict delete |
| `kuantitas` | unsignedInteger | |
| unik | `['keranjang_id', 'produk_id']` | satu baris per produk |

### `dompet` (D4)

| Kolom | Tipe | Catatan |
|---|---|---|
| `id` | bigint PK | |
| `kode` | string(40) | unik |
| `jenis` | string(20) | `eksternal`, `escrow`, `pendapatan`, `penjual` |
| `pengguna_id` | FK `pengguna.id` | nullable untuk dompet sistem |
| `saldo_rupiah` | bigInteger | hanya berubah lewat `BukuBesar` |

### `mutasi_saldo` (D4)

| Kolom | Tipe | Catatan |
|---|---|---|
| `id` | bigint PK | |
| `dompet_id` | FK `dompet.id` | |
| `kelompok` | uuid | satu pemindahan = dua baris |
| `jenis` | string(20) | `dana_masuk`, `lepas_dana`, `komisi`, `pengembalian`, `pencairan` |
| `jumlah_rupiah` | bigInteger | bertanda |
| `saldo_setelah` | bigInteger | |
| `rujukan` | string(60) | |
| `keterangan` | string(160) | nullable |
| `created_at` | timestamp | tanpa `updated_at` (append-only) |

### `sesi` (D1, Sesi 2)

| Kolom | Tipe | Catatan |
|---|---|---|
| `id` | bigint PK | |
| `pengenal` | char(26) | unik, ULID |
| `pengguna_id` | FK `pengguna.id` | cascade on delete; index `['pengguna_id','dicabut_pada']` |
| `hash_kunci` | char(64) | sha256; kunci utuh hanya tampil sekali saat login |
| `nama_perangkat` | string(60) | |
| `ip` | string(45) | nullable |
| `agen_pengguna` | string(255) | nullable |
| `dikonfirmasi_pada` | timestamp | nullable; dasar sesi segar (LP-15) |
| `terakhir_dipakai_pada` | timestamp | dasar pangkas sesi menganggur |
| `kedaluwarsa_pada` | timestamp | batas mutlak 7 hari |
| `dicabut_pada` | timestamp | nullable |
| `created_at`, `updated_at` | timestamp | |

### `alamat` (D1, Sesi 4)

| Kolom | Tipe | Catatan |
|---|---|---|
| `id` | bigint PK | |
| `pengguna_id` | FK `pengguna.id` | cascade delete |
| `label` | string(30) | |
| `nama_penerima` | string(80) | |
| `telepon` | string(20) | |
| `jalan` | string(200) | |
| `kota` | string(60) | |
| `kode_pos` | string(5) | |
| `utama` | boolean | tepat satu utama per pengguna (dijaga `BukuAlamat`) |
| `created_at`, `updated_at` | timestamp | |

Index: `['pengguna_id', 'utama']`.

### `checkout` (D3, Sesi 4)

| Kolom | Tipe | Catatan |
|---|---|---|
| `id` | bigint PK | |
| `kode` | char(26) | unik, ULID; yang disebut klien dan gerbang |
| `pembeli_id` | FK `pengguna.id` | restrict on delete |
| `total_rupiah` | unsignedBigInteger | |
| `pengenal_sesi` | char(26) | id sesi; sengaja tanpa FK (sesi dipangkas) |
| `created_at`, `updated_at` | timestamp | |

### `pesanan` (D3, Sesi 4)

| Kolom | Tipe | Catatan |
|---|---|---|
| `id` | bigint PK | |
| `checkout_id` | FK `checkout.id` | restrict on delete |
| `nomor` | string(24) | unik, LPK-YYYYMMDD-NNNNNN (LP-9); nullable — diisi lewat `Pesanan::booted`; index `['pembeli_id','id']` |
| `pembeli_id` | FK `pengguna.id` | restrict on delete |
| `toko_id` | FK `toko.id` | restrict on delete |
| `status` | string(20) | `menunggu_pembayaran`, `dibayar`, `dikemas`, `dikirim`, `selesai`, `dibatalkan`; index |
| `subtotal_rupiah` | unsignedBigInteger | |
| `ongkir_rupiah` | unsignedBigInteger | |
| `total_rupiah` | unsignedBigInteger | |
| `alamat_kirim` | JSON | salinan `Alamat::potret()` saat checkout |
| `dibayar_pada` | timestamp | nullable |
| `dibatalkan_pada` | timestamp | nullable |
| `alasan_batal` | string(120) | nullable |
| `created_at`, `updated_at` | timestamp | |

### `butir_pesanan` (D3, Sesi 4)

| Kolom | Tipe | Catatan |
|---|---|---|
| `id` | bigint PK | |
| `pesanan_id` | FK `pesanan.id` | cascade delete |
| `produk_id` | FK `produk.id` | restrict on delete |
| `nama_produk` | string(120) | salinan saat checkout (LP-6) |
| `harga_rupiah` | unsignedBigInteger | salinan saat checkout |
| `kuantitas` | unsignedInteger | |
| `subtotal_rupiah` | unsignedBigInteger | |

Tanpa `updated_at`/`created_at`; butir bersifat beku begitu disimpan.

### `pembayaran` (D4, Sesi 4)

| Kolom | Tipe | Catatan |
|---|---|---|
| `id` | bigint PK | |
| `checkout_id` | FK `checkout.id` | unik (LP-7); restrict on delete |
| `kode` | string(30) | unik, `BYR-` + ULID; yang disebut gerbang |
| `jumlah_rupiah` | unsignedBigInteger | |
| `status` | string(20) | `menunggu`, `lunas`, `kedaluwarsa`; index |
| `batas_bayar_pada` | timestamp | 24 jam setelah dibuat |
| `lunas_pada` | timestamp | nullable |
| `id_peristiwa` | string(64) | unik, nullable; pengenal peristiwa dari gerbang |
| `created_at`, `updated_at` | timestamp | |

### `kunci_idempotensi` (D5, Sesi 4)

| Kolom | Tipe | Catatan |
|---|---|---|
| `id` | bigint PK | |
| `pengguna_id` | FK `pengguna.id` | cascade delete |
| `kunci` | string(64) | unik per pengguna |
| `rute` | string(120) | metode + path |
| `sidik` | char(64) | sha256 atas rute + badan permintaan |
| `kode_status` | unsignedSmallInteger | nullable |
| `badan_respons` | longText | nullable |
| `created_at` | timestamp | tidak di-update otomatis; belum ada pemangkas (Sesi 5) |

### `peran` dan `izin` (D5, Sesi 3)

| Kolom | Tipe | Catatan |
|---|---|---|
| `peran.id` | bigint PK | |
| `peran.nama` | string(30) | unik, enum `NamaPeran` |
| `izin.id` | bigint PK | |
| `izin.nama` | string(60) | unik, enum `NamaIzin` |
| `peran_izin` | composite PK (`peran_id`,`izin_id`) | tanpa id surrogate; tabel pivot peran–izin |
| `pengguna_peran` | composite PK (`pengguna_id`,`peran_id`) | tanpa id surrogate; tabel pivot pengguna–peran |

## Aturan skema

- Semua nilai uang adalah bilangan bulat: `bigInteger`/`unsignedBigInteger` dengan akhiran `_rupiah`. Tidak ada `decimal`, `float`, atau `double` (LP-1).
- Status disimpan sebagai string(20) dan dipetakan ke enum PHP.
- `mutasi_saldo` tidak boleh diubah atau dihapus (LP-13).

## Urutan kunci baris

Berlaku sejak Sesi 4. Transaksi yang mengunci lebih dari satu baris mengambil kuncinya menurut
urutan di bawah ini. Sebuah transaksi boleh melompati anak tangga, tetapi tidak pernah naik kembali.

| Urut | Baris | Dikunci oleh |
| --- | --- | --- |
| 1 | `pengguna`, lalu `alamat` miliknya | `BukuAlamat` |
| 2 | `keranjang`, lalu `butir_keranjang` | `LayananCheckout` |
| 3 | `checkout` | `GembokCheckout` |
| 4 | `pembayaran` | `LayananPembayaran` |
| 5 | `pesanan` | `MesinStatusPesanan` |
| 6 | `produk`, urut nomor menaik | `PenjagaStok` |
| 7 | `dompet`, urut nomor menaik | `BukuBesar` |

Middleware `idempoten` mencatat kuncinya sebelum anak tangga pertama. Catatan itu baris baru,
dan tidak ada transaksi yang memintanya setelah memegang kunci dari tabel di atas.

Tiga aturan menyertainya:

1. Kunci hanya diambil di dalam `DB::transaction()`. Di luar transaksi, kunci lepas begitu
   pernyataannya selesai.
2. Kunci dulu, baca kemudian. Data yang menjadi dasar keputusan dibaca dengan `lockForUpdate()`
   setelah kunci induknya didapat. Pada MySQL, `SELECT` biasa di dalam transaksi melihat potret
   lama, bukan keadaan terkini.
3. Pengurutan tidak boleh berubah antar transaksi. Bila dua jalur mengunci baris yang sama dengan
   arah terbalik, keduanya dapat saling menunggu selamanya (kebuntuan). Lapak mengurutkan selalu
   dari anak tangga terkecil ke terbesar.
