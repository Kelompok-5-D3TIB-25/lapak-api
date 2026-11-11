# D2 — Toko dan Katalog

- Pemilik: @victor-isMe
- Tabel: `toko`, `kategori`, `produk`
- Aturan: LP-3, LP-4, LP-6

## Pertanyaan yang dijawab

Apa yang dijual, oleh siapa, dan mana yang boleh tampil?

## Yang dibutuhkan dari domain lain

- `pengguna.id` dari D1 untuk `toko.pemilik_id`.
- Token sesi (`auth:sesi`) dari D1 untuk pintu toko/produk milik sesi.
- Kewenangan dari D5 (`izin:produk.kelola`, `izin:toko.buka`, `izin:toko.moderasi`).

## Yang disediakan untuk domain lain

- `Produk::query()->tampil()` — produk aktif dari toko aktif.
- `Produk::sedangTampil()` — syarat yang sama, untuk baris yang sudah di tangan.
- `Toko` — `ongkir_rupiah` untuk perhitungan belanja.
- `Produk` — `harga_rupiah`, `stok`, `berat_gram`, `slug`.
- `App\Services\PenjagaStok` — satu-satunya pintu pengurang stok; dalam transaksi.

## Kontrak Sesi 1

- `GET /api/v1/produk` — katalog berhalaman.
- `GET /api/v1/produk/{slug}` — detail produk tampil.
- `GET /api/v1/toko/{slug}` — profil toko aktif, tanpa membocorkan `pemilik_id`.

## Kontrak Sesi 2

- `GET/PATCH /toko-saya` — lihat dan benahi toko sendiri (tak menuntut izin khusus).
- `POST /toko-saya` — ajukan pembukaan toko (`izin:toko.buka`).
- `GET/POST/PATCH/DELETE /toko-saya/produk{,/{id}}` — kelola produk sendiri (`izin:produk.kelola`).
- Produk yang baru dibuat berstatus `draf`.
- Menyaring produk tampak: `Produk::scopeTampil()`; checkout membaca scope yang sama lewat `PenjagaStok::ambil()`.

## Kontrak Sesi 3

- Admin: `GET /admin/toko`, `POST /admin/toko/{id}/setujui`, `POST /admin/toko/{id}/tangguhkan`.
- Menyetujui: status toko jadi aktif, pemilik belum penjual, lalu pemilik diberi peran `penjual`; sesi pemilik dicabut.
- Menangguhkan: alasan wajib; bukan berdasarkan nama peran; lewat `TokoPolicy`.
- Admin tidak boleh memoderasi toko miliknya sendiri.

## Kontrak Sesi 4

- `Produk::sedangTampil()`, `PenjagaStok::ambil()` mengunci baris `produk` urut nomor (LP-4).
- `PATCH /toko-saya/produk/{id}` menolak `stok` (`'prohibited'`); gunakan `POST /toko-saya/produk/{id}/stok` dengan selisih (LP-4, LP-11, wajib `Idempotency-Key`).
- `PenjagaStok::sesuaikan()` — selisih relatif; harga diverifikasi dengan `harga_rupiah` bawaan, tidak ada pembacaan angka mutlak dari layar.
- Uji `PenjagaStokTest`, `PenyesuaianStokTest`; skenario balapan `rebutan-stok`, `penyesuaian-stok`, `rebutan-checkout`.

## Aturan

- Satu pengguna paling banyak satu toko (LP-3).
- Toko baru berstatus `menunggu` sampai disetujui admin.
- Produk hanya tampil bila `status = aktif` dan toko `aktif` (LP-4). Stok diperiksa saat simulasi, keranjang, dan checkout, bukan saat menampilkan katalog.
- Harga selalu dari basis data; kiriman klien diabaikan (LP-6).
- Pencarian memperlakukan `%` dan `_` sebagai teks, bukan wildcard.
- `stok` berubah lewat selisih oleh penjual; tidak pernah lewat angka mutlak dari klien (LP-4).

## Batas domain

D2 tidak menghitung total belanja dan tidak mengubah stok saat simulasi. Itu milik D3.
