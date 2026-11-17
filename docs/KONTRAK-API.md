# Kontrak API

Base path: `/api/v1`

API hanya berbicara JSON. Middleware `PaksaJson` menetapkan `Accept: application/json` agar galat tidak keluar sebagai HTML.

## Amplop sukses

```json
{
  "sukses": true,
  "data": {},
  "meta": null
}
```

### Meta halaman

```json
{
  "halaman": 1,
  "per_halaman": 12,
  "total": 10,
  "halaman_terakhir": 1
}
```

## Amplop galat

```json
{
  "sukses": false,
  "galat": {
    "kode": "LP-4",
    "pesan": "Stok Selendang Batik Sutra tidak mencukupi.",
    "rincian": {
      "produk_id": 3,
      "diminta": 1,
      "tersedia": 0
    }
  }
}
```

## Kode galat

| HTTP | Kode | Kapan |
|---|---|---|
| 401 | `BELUM_MASUK` | Belum masuk atau sesi tidak berlaku |
| 403 | `DITOLAK` | Tidak berwenang |
| 404 | `TIDAK_DITEMUKAN` | Sumber daya tidak ada |
| 405 | `METODE_TIDAK_DIIZINKAN` | Metode HTTP salah |
| 422 | `VALIDASI` | Validasi masukan gagal |
| 422/409 | `LP-*` | Pelanggaran aturan bisnis (`GalatDomain`) |
| 429 | `TERLALU_SERING` | Pembatasan laju |
| 500 | `GALAT_SERVER` | Galat tak terduga; rincian tidak dikirim ke klien |

Pelanggaran aturan bisnis membawa kode `LP-1` sampai `LP-17` pada `galat.kode`. Selain itu domain memakai kode non-LP: `KERANJANG_KOSONG`, `KERANJANG_PENUH`, `ALAMAT_PENUH`, `JUMLAH_TIDAK_COCOK`, `TANDA_TANGAN_SALAH`; HTTP umum tak dikenal dipetakan ke `GALAT_HTTP`. Status HTTP ditentukan pemilik aturan (umumnya 422, stok 409).

## Header

| Header | Arah | Catatan |
|---|---|---|
| `Accept: application/json` | permintaan | Disarankan; dipaksa middleware |
| `X-Request-Id` | respons | UUID buatan server, juga muncul di log |

## Uang dan waktu

- Semua nilai uang adalah bilangan bulat rupiah. Field berakhiran `_rupiah`.
- JSON uang berupa angka bulat, misalnya `185000`, bukan `"185000"` dan bukan `185000.0`.
- Untuk tampilan, `Uang::format()` menghasilkan `Rp 1.250.000`.
- Waktu memakai ISO 8601, misalnya `2026-10-06T04:25:36+00:00`.

## Endpoint Sesi 1

| Metode | Path | Nama rute | Keterangan |
|---|---|---|---|
| GET | `/api/v1/health` | `v1.health` | Status layanan, versi, waktu |
| GET | `/api/v1/produk` | `v1.produk.index` | Katalog berhalaman |
| GET | `/api/v1/produk/{slug}` | `v1.produk.show` | Detail produk tampil |
| GET | `/api/v1/toko/{slug}` | `v1.toko.show` | Profil toko aktif |
| POST | `/api/v1/registrasi` | `v1.registrasi` | Daftar pengguna |
| POST | `/api/v1/simulasi-belanja` | `v1.belanja.simulasi` | Simulasi belanja lintas toko |

## Aturan per endpoint

### `GET /produk`

Parameter: `kategori`, `toko`, `cari`, `harga_min`, `harga_maks`, `tersedia`, `urut` (`terbaru`, `harga_naik`, `harga_turun`), `per_halaman` (1–60), `halaman`.

Hanya produk berstatus `aktif` dan berasal dari toko `aktif` (LP-4). Stok tidak menyaring katalog; ketersediaan stok diperiksa saat simulasi dan checkout.

### `POST /registrasi`

```json
{
  "nama": "Rina Kartika",
  "surel": "Rina@Contoh.test",
  "telepon": "081234567890",
  "kata_sandi": "BatikSolo2026",
  "konfirmasi_kata_sandi": "BatikSolo2026"
}
```

Respons `201`: `id`, `nama`, `surel` (huruf kecil), `telepon`, `status`, `terdaftar_pada`. Tidak pernah memuat `kata_sandi`.

### `POST /simulasi-belanja`

```json
{
  "butir": [
    { "produk_id": 2, "kuantitas": 2 },
    { "produk_id": 5, "kuantitas": 3 }
  ]
}
```

Hanya `produk_id` dan `kuantitas` yang divalidasi. `harga_satuan`, `total_bayar`, dan kolom lain dari klien diabaikan (LP-6). Satu toko menghasilkan satu rincian (LP-7). Komisi dihitung dari subtotal, ongkir utuh untuk penjual (LP-8).

## Endpoint Sesi 2

| Metode | Path | Nama rute | Keterangan |
|---|---|---|---|
| POST | `/api/v1/masuk` | `v1.masuk` | Masuk; batas laju per akun dan per IP |
| POST | `/api/v1/keluar` | `v1.keluar` | Cabut sesi perangkat ini |
| GET | `/api/v1/saya` | `v1.saya` | Identitas + peran + izin |
| GET | `/api/v1/sesi` | `v1.sesi.index` | Daftar sesi milik pengguna |
| POST | `/api/v1/sesi/konfirmasi` | `v1.sesi.konfirmasi` | Konfirmasi kata sandi, membuat sesi segar |
| DELETE | `/api/v1/sesi/{pengenal}` | `v1.sesi.destroy` | Cabut sesi perangkat lain (sesi segar) |
| GET | `/api/v1/toko-saya` | `v1.toko-saya.show` | Toko milik sesi |
| PATCH | `/api/v1/toko-saya` | `v1.toko-saya.update` | Perbarui toko |
| POST | `/api/v1/toko-saya` | `v1.toko-saya.store` | Ajukan pembukaan toko |
| GET | `/api/v1/toko-saya/produk` | `v1.toko-saya.produk.index` | Produk toko milik sesi |
| POST | `/api/v1/toko-saya/produk` | `v1.toko-saya.produk.store` | Produk baru (`draf`) |
| PATCH | `/api/v1/toko-saya/produk/{id}` | `v1.toko-saya.produk.update` | Ubah detail; `stok` ditutup (422) |
| DELETE | `/api/v1/toko-saya/produk/{id}` | `v1.toko-saya.produk.destroy` | Arsipkan |
| GET | `/api/v1/keranjang` | `v1.keranjang.show` | Keranjang milik sesi |
| DELETE | `/api/v1/keranjang` | `v1.keranjang.kosongkan` | Kosongkan |
| PUT | `/api/v1/keranjang/butir/{produkId}` | `v1.keranjang.simpan` | Setel kuantitas |
| DELETE | `/api/v1/keranjang/butir/{produkId}` | `v1.keranjang.hapus` | Hapus butir |
| GET | `/api/v1/dompet` | `v1.dompet.show` | Saldo dompet sendiri (penjual) |
| GET | `/api/v1/dompet/mutasi` | `v1.dompet.mutasi` | Riwayat mutasi (sesi segar) |

## Endpoint Sesi 3

| Metode | Path | Nama rute | Keterangan |
|---|---|---|---|
| GET | `/api/v1/admin/pengguna` | `v1.admin.pengguna.index` | Daftar pengguna (admin) |
| PUT | `/api/v1/admin/pengguna/{id}/peran/{peran}` | `v1.admin.pengguna.peran.beri` | Beri peran (sesi segar) |
| DELETE | `/api/v1/admin/pengguna/{id}/peran/{peran}` | `v1.admin.pengguna.peran.cabut` | Cabut peran (sesi segar) |
| GET | `/api/v1/admin/toko` | `v1.admin.toko.index` | Daftar toko (admin) |
| POST | `/api/v1/admin/toko/{id}/setujui` | `v1.admin.toko.setujui` | Setujui: toko aktif, pemilik jadi penjual |
| POST | `/api/v1/admin/toko/{id}/tangguhkan` | `v1.admin.toko.tangguhkan` | Tangguhkan dengan alasan |
| GET | `/api/v1/buku-besar/dompet` | `v1.buku-besar.dompet.index` | Seluruh dompet (staf keuangan/auditor) |
| GET | `/api/v1/buku-besar/dompet/{id}/mutasi` | `v1.buku-besar.dompet.mutasi` | Mutasi sebuah dompet (staf keuangan/auditor) |

Admin/pengguna dan admin/toko menuntut sesi segar untuk tindakan penulisan.

## Endpoint Sesi 4

| Metode | Path | Nama rute | Keterangan |
|---|---|---|---|
| GET | `/api/v1/alamat` | `v1.alamat.index` | Alamat milik sesi, utama lebih dulu |
| POST | `/api/v1/alamat` | `v1.alamat.store` | Tambah (maksimal 5) |
| PUT | `/api/v1/alamat/{id}/utama` | `v1.alamat.utama` | Jadikan utama (idempoten) |
| DELETE | `/api/v1/alamat/{id}` | `v1.alamat.destroy` | Hapus |
| POST | `/api/v1/toko-saya/produk/{id}/stok` | `v1.toko-saya.produk.stok` | Penyesuaian stok relatif; wajib `Idempotency-Key` |
| POST | `/api/v1/checkout` | `v1.checkout` | Checkout atomik lintas toko; wajib `Idempotency-Key` |
| GET | `/api/v1/pesanan` | `v1.pesanan.index` | Daftar pesanan milik sesi |
| GET | `/api/v1/pesanan/{nomor}` | `v1.pesanan.show` | Satu pesanan beserta tagihannya |
| GET | `/api/v1/pembayaran/{kode}` | `v1.pembayaran.show` | Tagihan milik sesi (hanya membaca) |
| POST | `/api/v1/webhook/pembayaran` | `v1.webhook.pembayaran` | Kabar dari gerbang; dipanggil gerbang, bukan klien |

## Otorisasi
- Kewenangan ditentukan izin yang melekat pada peran, bukan nama peran (LP-2). Satu akun boleh
  memegang beberapa peran; izinnya adalah gabungan izin seluruh perannya.
- Rute terlindung menyebut paling banyak satu izin: `->middleware('izin:<nama>')`. Bila dua peran
  membutuhkan pintu yang sama, keduanya diberi izin yang sama. Rute tidak pernah menyebut nama peran.
- Setiap rute terlindung memiliki tepat satu baris pada `tests/Feature/Sistem/matriks/<domain>.php`.
  Rute yang terbuka bagi siapa pun yang sudah masuk ditulis `'*'`, tidak dibiarkan tanpa baris.
- Urutan pemeriksaan selalu sama: kunci sah (401), memegang izin (403 `DITOLAK`), sesi segar bila
  rutenya menuntut (403 `LP-15`). Baru setelah ketiganya lolos, masukan divalidasi dan objek dicari.
  Orang yang tidak berwenang karenanya menerima 403 yang sama untuk nomor yang ada dan yang tidak ada.
- Pertanyaan "objek yang ini boleh?" dijawab di dalam controller: kueri berlingkup (objek milik
  orang lain dijawab 404) atau Policy (403 `DITOLAK`). Lihat `docs/domain/` untuk pilihan tiap domain.
- Badan 403 `DITOLAK` selalu sama dan tidak menyebut izin apa yang kurang.
- `GET /saya` menyertakan `pengguna.peran` dan `izin`. Klien memakainya untuk menata tampilan saja;
  server memutuskan ulang pada setiap permintaan.
- Peran hanya berubah lewat `App\Services\PengelolaPeran`. Setiap perubahan mencabut seluruh sesi
  akun itu. Klien yang tiba-tiba menerima 401 mengarahkan penggunanya masuk ulang.

| Peran | Izin |
| --- | --- |
| `pembeli` | `keranjang.kelola`, `toko.buka`, `pesanan.buat` |
| `penjual` | `produk.kelola`, `dompet.lihat` |
| `admin` | `toko.moderasi`, `peran.kelola` |
| `keuangan` | `buku-besar.lihat` |
| `auditor` | `buku-besar.lihat` |

Tabel di atas disalin dari `App\Enums\NamaPeran::izinBawaan()`, satu-satunya tempat pemetaan ini
ditulis. `PeranIzinSeeder` menyalinnya ke basis data. Tidak ada peran yang memegang seluruh izin.

## Idempotensi
- Setiap permintaan yang mengubah uang atau stok wajib membawa header `Idempotency-Key` (LP-11):
  16 sampai 64 huruf, angka, garis bawah, atau tanda hubung. UUID memenuhi syarat ini.
- Klien membuat SATU kunci untuk satu niat, misalnya satu kali menekan tombol bayar, dan memakai
  kunci yang sama setiap kali mengulang permintaan itu karena jaringan putus atau batas waktu habis.
- Permintaan pertama yang berhasil (2xx) dicatat bersama responsnya. Pengulangan dengan kunci dan
  badan yang sama menerima respons yang sama, ditandai header `Idempotency-Replayed: true`, dan
  tidak berefek lagi.
- Permintaan yang gagal tidak meninggalkan catatan. Setelah sebabnya dibetulkan, kunci yang sama
  boleh dipakai lagi.
- Kunci yang sama dengan badan yang berbeda dijawab 422 `LP-11`. Kunci berlaku per pengguna.
- Catatan kunci dan seluruh perubahan data berada dalam satu transaksi basis data. Rute yang
  memasang middleware `idempoten` dijamin tidak pernah mencatat efek tanpa mencatat kuncinya,
  dan tidak pernah mencatat kunci tanpa mencatat efeknya.

## Webhook pembayaran

- `POST /webhook/pembayaran` adalah satu-satunya rute tulis tanpa kunci sesi. Pemanggilnya
  gerbang pembayaran. Pembeli tidak memiliki pintu apa pun untuk menandai tagihannya lunas.
- Gerbang mengirim header `X-Gateway-Signature: t=<detik unix>,v1=<tanda>`, dengan `<tanda>`
  berupa HMAC-SHA256 atas teks `<t>.<badan mentah>` memakai rahasia bersama `LAPAK_GATEWAY_RAHASIA`.
- Tanpa header, rahasia salah, badan diubah, atau selisih waktu lebih dari 5 menit: jawabannya
  selalu 401 `TANDA_TANGAN_SALAH` yang sama. Rahasia yang kosong menolak seluruh webhook.
- Badan: `id_peristiwa`, `kode_pembayaran`, `jumlah_rupiah`. Jumlah dicocokkan dengan tagihan di
  basis data; selisih satu rupiah pun dijawab 422 `JUMLAH_TIDAK_COCOK` dan tidak melunasi apa pun.
- Kabar yang sama boleh tiba berkali-kali. Yang pertama dijawab `"hasil": "dilunasi"`, sisanya
  `"hasil": "sudah_diproses"`, dan uangnya dibukukan satu kali.

## Log audit

- Tindakan yang mengubah uang, stok, atau kewenangan, dan percobaan masuk, meninggalkan jejak pada
  `log_audit` (LP-17). Yang lain tidak.
- Penulisan hanya lewat `PencatatAudit`, di dalam transaksi yang sama dengan tindakannya.
- Rahasia (`kata_sandi`, `kunci`, `hash_kunci`, `rahasia`, `tanda_tangan`) tidak pernah masuk ke log.
- Pemeriksaan rantai: `php artisan lapak:verifikasi-audit`, atau `GET /audit/rantai` (izin `audit.lihat`).
- Tidak ada pintu tulis pada `/audit`.

## Pencairan

- `POST /dompet/pencairan` (izin `pencairan.ajukan`): memindahkan saldo penjual ke dompet `pencairan`
  sejak diajukan (LP-16). Pencairan minimal `LAPAK_PENCAIRAN_MINIMUM` (bawaan Rp 50.000).
- Staf keuangan (izin `pencairan.setujui`): `GET /keuangan/pencairan` daftar menunggu,
  `POST /keuangan/pencairan/{kode}/setujui` dan `.../tolak`.
- Penyetuju tidak boleh orang yang sama dengan pengaju. Uang baru keluar atas keputusan orang kedua.
- `LayananDana::lepaskan()` memindahkan komisi lalu bagian penjual; `kembalikan()` mengembalikan
  seluruh nilai pesanan termasuk ongkir ke dompet `eksternal`.

## Daur hidup pesanan

- Status pesanan hanya dapat berpindah lewat `LayananPesanan` (satu pintu). Perubahan hampir tidak
  pernah berdiri sendiri: stok, uang, dan jejak audit ikut dalam transaksi yang sama.
- `POST /pesanan/{nomor}/terima` menyelesaikan pesanan dan melepas dana (LP-12).
  `POST /pesanan/{nomor}/batal` membatalkan satu pesanan yang sudah dibayar dan belum dikirim.
  `POST /checkout/{kode}/batal` membatalkan checkout yang belum dibayar (LP-7, LP-10).
- Cap waktu proses: `dikemas_pada`, `dikirim_pada`, `selesai_pada`, `nomor_resi`, `dibatalkan_oleh`.
