# D3 — Keranjang dan Pesanan

- Pemilik: @nabelazahra-ptr26
- Tabel: `keranjang`, `butir_keranjang`, `checkout`, `pesanan`, `butir_pesanan`
- Aturan: LP-1, LP-4, LP-5, LP-6, LP-7, LP-8, LP-11

## Pertanyaan yang dijawab

Berapa yang harus dibayar, dan pesanan mana yang sah?

## Yang dibutuhkan dari domain lain

- `pengguna.id` dari D1 untuk `keranjang.pengguna_id`, `checkout.pembeli_id`, `pesanan.pembeli_id`.
- `Produk::query()->tampil()` dan `toko.ongkir_rupiah` dari D2.
- `App\Models\Alamat` dan `Alamat::potret()` dari D1.
- `PenjagaStok::ambil()` dari D2 untuk checkout.
- `MesinStatusPesanan` (milik kontrak) untuk perpindahan status.
- Kewenangan dari D5 (`izin:keranjang.kelola`, `izin:pesanan.buat`).

## Yang disediakan untuk domain lain

- `App\Domain\Uang` — value object uang bulat.
- `App\Domain\Belanja\PenghitungBelanja` — subtotal, ongkir, komisi, total.
- `App\Services\PenyusunBelanja` — menyusun rincian dari `produk_id` dan `kuantitas`.
- `RincianToko` — satu rincian per toko.
- `App\Models\Checkout` — induk transaksi (pesanan dan pembayaran menunjuknya).
- `App\Models\Pesanan` — `scopeMilikPembeli()` untuk seluruh pintu pembeli.
- `App\Services\LayananCheckout` — `proses()`; satu transaksi, semua atau tidak sama sekali.
- `App\Services\GembokCheckout` — gembok induk atas `checkout`.
- `App\Services\LayananKeranjang` — satu-satunya pintu perubahan keranjang.

## Kontrak Sesi 1

- `POST /api/v1/simulasi-belanja`

```json
{
  "butir": [
    { "produk_id": 2, "kuantitas": 2 },
    { "produk_id": 5, "kuantitas": 3 }
  ]
}
```

## Kontrak Sesi 2

- `GET/DELETE /api/v1/keranjang` — isi/kosongkan keranjang.
- `PUT/DELETE /api/v1/keranjang/butir/{produkId}` — setel kuantitas (idempoten).
- `LayananKeranjang` — satu-satunya tempat keranjang diubah; penjual tidak dapat menggunakannya.
- Matriks otorisasi: seluruh rute keranjang milik peran `pembeli` (`izin:keranjang.kelola`).

## Kontrak Sesi 3

- Gerbang wajib `izin:keranjang.kelola`; admin tidak berbelanja memakai akun dinas.
- Aturan status pesanan ditulis dan diuji pada `AturanTransisi` (Sesi 3) tanpa tabel; dipakai `MesinStatusPesanan` pada Sesi 4.

## Kontrak Sesi 4

- Tabel `checkout`, `pesanan`, `butir_pesanan`.
- `POST /api/v1/checkout` — satu transaksi: keranjang dikunci, produk diambil berkunci urut nomor, pesanan per toko lahir, tagihan terbit, keranjang kosong; gagal di mana pun membatalkan semuanya (LP-7, LP-11).
- `GET /api/v1/pesanan` — daftar milik pembeli, paginate 12.
- `GET /api/v1/pesanan/{nomor}` — satu pesanan + tagihan; nomor orang lain 404 (LP-9, LP-15).
- `Alamat::potret()` disimpan sebagai `alamat_kirim` — salinan beku (LP-6).
- `PembatalanOtomatis` dan `lapak:batalkan-kedaluwarsa` — pembatalan otomatis dan pengembalian stok (LP-10).
- `MesinStatusPesanan::pindahkan()` — pintu satu-satunya perubahan status (LP-10).
- Skenario balapan: `keranjang-ganda`, `kunci-ganda`, `rebutan-checkout` (`App\Balapan`).
- Endpoint `POST /checkout` wajib `Idempotency-Key`; dikembalikan bersama respons yang sama jika diulang.

## Aturan

- Harga tidak disimpan di keranjang; selalu dibaca dari `produk` saat dihitung (LP-6).
- Satu toko menghasilkan satu rincian (LP-7).
- Komisi 5% dari subtotal, dibulatkan setengah ke atas; ongkir utuh untuk penjual (LP-8).
- Produk tersembunyi dan produk tidak ada dijawab dengan pesan yang sama agar tidak membocorkan keberadaan produk (LP-4).
- Simulasi tidak mengunci harga maupun stok; kepastian baru ada saat checkout.
- Checkout dihitung dari baris yang dikunci `PenjagaStok`, bukan dari `Produk::query()->tampil()` biasa.
- Satu checkout melahirkan satu pesanan per toko; satu tagihan per checkout (LP-7).

## Batas domain

D3 tidak memindahkan saldo. Perpindahan uang adalah D4 lewat `BukuBesar`.

D3 tidak membaca katalog kecuali untuk checkout; keranjang hidup di atas D2 hanya untuk harga dan stok.

D3 tidak menerbitkan tagihan; itu D4 lewat `LayananPembayaran`.
