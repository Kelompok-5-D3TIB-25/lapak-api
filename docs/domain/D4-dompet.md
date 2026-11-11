# D4 — Pembayaran dan Saldo

- Pemilik: @mrdaknsh
- Tabel: `dompet`, `mutasi_saldo`, `pembayaran`
- Aturan: LP-12, LP-13, LP-16

## Pertanyaan yang dijawab

Di mana setiap rupiah berada saat ini?

## Yang dibutuhkan dari domain lain

- `pengguna.id` dari D1 untuk dompet penjual dan tagihan milik pembeli.
- `App\Domain\Uang` dari D3 untuk nilai pemindahan.
- `checkout.id` dan `pesanan` dari D3 untuk pemindahan dana dan status.
- Kunci sesi (`auth:sesi`) dari D1 untuk pintu `pembayaran/{kode}`.
- Kewenangan (`izin:pesanan.buat`) dari D5 untuk membaca tagihan sendiri.
- `MesinStatusPesanan` dari D3 (dipakai `tahanDana()`).
- `GembokCheckout` dari D3 (mengunci `checkout` selama pelunasan).

## Yang disediakan untuk domain lain

- `App\Services\BukuBesar` — satu-satunya pintu perubahan saldo.
- `dompetSistem(JenisDompet)` dan `dompetPenjual(Pengguna)`.
- `pindahkan()` — mencatat sepasang mutasi berjumlah nol.
- `App\Services\LayananPembayaran` — `buat()`, `untuk()`, `tandaiKedaluwarsa()`, `terima()`.
- `php artisan lapak:periksa-buku` — memeriksa lima invarian LP-13.
- `php artisan lapak:simulasi-bayar` — gerbang pembayaran disimulasikan.

## Kontrak Sesi 1

Belum ada endpoint HTTP. Pintu HTTP ke uang baru dibuat setelah identitas (Sesi 2) dan otorisasi tersedia.

## Kontrak Sesi 2

- `GET /api/v1/dompet` — saldo dompet penjual (`izin:dompet.lihat`).
- `GET /api/v1/dompet/mutasi` — riwayat mutasi; sesi segar wajib (LP-15).

## Kontrak Sesi 3

- `GET /api/v1/buku-besar/dompet` — seluruh dompet (`izin:buku-besar.lihat`).
- `GET /api/v1/buku-besar/dompet/{id}/mutasi` — mutasi sebuah dompet (`izin:buku-besar.lihat`).
- `lapak:matriks-izin` — memverifikasi hanya izin yang dipakai terdaftar.
- Matriks dompet: dompet milik penjual; buku besar untuk keuangan/auditor.

## Kontrak Sesi 4

- Tabel `pembayaran`: satu untuk setiap checkout (LP-7), `kode` `BYR-` + ULID.
- `POST /webhook/pembayaran` — satu-satunya rute tulis tanpa kunci sesi; pemanggilnya gerbang pembayaran (LP-12).
- `TandaTanganGateway::buat()/sah()` — HMAC-SHA256 atas `<t>.<badan mentah>`; rahasia kosong menolak semua; selisih waktu 5 menit ditolak.
- `LayananPembayaran::terima(kode, jumlahRupiah, idPeristiwa)`:
  - Tagihan dibaca ulang di bawah gembok.
  - Sudah lunas → `sudah_diproses`. Kedaluwarsa → 409 `LP-10`. Jumlah berbeda → 422 `JUMLAH_TIDAK_COCOK`.
  - Tagihan jadi `lunas`, pesanannya `dibayar`, dan per pesanan dipindahkan dari `eksternal` ke `escrow` (LP-12).
  - Satu transaksi; kabar kembar tidak membukukan dua kali.
- `GET /api/v1/pembayaran/{kode}` — tagihan milik sesi; read-only.
- `lapak:periksa-buku` kini dengan lima invarian: escrow = total Dibayar+Dikemas+Dikirim.
- Skenario balapan: `webhook-ganda` (LP-12) serta `keranjang-ganda` dan `kunci-ganda` yang menegakkan LP-11/LP-7.
- `lapak:simulasi-bayar` — gerbang disimulasikan untuk latihan.

## Aturan

- Saldo hanya berubah lewat pemindahan antardompet (LP-13).
- Setiap pemindahan menghasilkan tepat dua baris `mutasi_saldo` dengan jumlah nol.
- Mutasi tidak boleh diubah atau dihapus.
- Hanya dompet `eksternal` yang boleh bersaldo negatif.
- Pemindahan nol rupiah dan pemindahan ke dompet yang sama ditolak.
- Baris dikunci urut `id` menaik untuk mencegah deadlock.
- `lapak:periksa-buku` dijalankan pada gerbang CI.
- Jumlah yang dibayar harus sama persis dengan tagihan; selisih satu rupiah pun ditolak (LP-6).
- Uang penjual harus menunggu sampai pembeli konfirmasi (LP-12; bagian kedua menyusul di Sesi 5).

## Batas domain

D4 tidak menghitung harga belanja; itu milik D3.
D4 tidak menerbitkan pesanan; itu milik D3.
