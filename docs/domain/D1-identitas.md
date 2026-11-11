# D1 — Identitas dan Sesi

- Pemilik: @ihsanpradipta45-alt
- Tabel: `pengguna`, `sesi`, `alamat`
- Aturan: LP-14, LP-15, LP-2 (lewat izin)

## Pertanyaan yang dijawab

Siapa yang sedang berbicara dengan sistem, apa buktinya, dan dari mana pun ia boleh membuka pesanan.

## Yang dibutuhkan dari domain lain

- `pengguna.id` dipakai seluruh domain lain sebagai tanda pemilik baris.
- Tidak ada; D1 adalah fondasi. Domain lain hanya merujuk `pengguna.id`.

## Yang disediakan untuk domain lain

- `App\Models\Pengguna` — `id`, `nama`, `surel`, `status`, relasi `peran`, `izin`.
- `App\Models\Sesi` — `scopeBerlaku()`, `segar()`; kunci sesi disimpan sebagai hash (LP-14).
- `App\Models\Alamat` — `potret()`, `scopeMilik()`.
- `App\Services\PenerbitSesi` — menerbitkan dan merotasi kunci sesi.
- `App\Services\BukuAlamat` — satu-satunya pintu perubahan alamat; mengunci baris pengguna (D1, Sesi 4).
- Status akun: `aktif`, `ditangguhkan`.

## Kontrak Sesi 1

- `POST /api/v1/registrasi`
- `status` tidak dapat diisi klien; selalu `aktif` saat registrasi.
- `kata_sandi` disimpan sebagai hash dan tidak pernah keluar pada respons.
- `surel` dinormalkan ke huruf kecil sebelum pemeriksaan keunikan.
- `telepon` opsional.

## Kontrak Sesi 2

- `POST /api/v1/masuk` — batas laju per surel dan per IP.
- `POST /api/v1/keluar` — mencabut sesi perangkat ini.
- `GET /api/v1/saya` — identitas, peran, izin.
- `GET /api/v1/sesi` — daftar sesi milik pengguna.
- `POST /api/v1/sesi/konfirmasi` — konfirmasi kata sandi; membuat sesi segar (LP-15).
- `DELETE /api/v1/sesi/{pengenal}` — mencabut sesi perangkat lain; sesi segar wajib.
- Kunci sesi disimpan sebagai hash (`hash_kunci`); kunci utuh hanya tampil sekali saat login (LP-14).
- `KunciSesi`, `PenerbitSesi`, `PemeriksaSesi` — `App\Domain\Sesi`.
- `lapak:pangkas-sesi` — menghapus sesi mati harian; LP-14.
- `phpunit.xml` memaksa SQLite di memori (`force="true"`); jangan dihapus.

## Kontrak Sesi 3

- `GET /api/v1/saya` menyertakan `peran` dan `izin`.
- `PUT /api/v1/admin/pengguna/{id}/peran/{peran}` — memberi peran; sesi segar wajib.
- `DELETE /api/v1/admin/pengguna/{id}/peran/{peran}` — mencabut peran; sesi segar wajib.
- Pemberian/pencabutan peran mencabut seluruh sesi akun itu; klien melihat 401 dan masuk ulang.
- `PengelolaPeran::beri()` / `cabut()` mengembalikan boolean bila tidak ada yang berubah.

## Kontrak Sesi 4

- Tabel `alamat`: tujuh kolom identitas, `utama` dijaga `BukuAlamat`, satu utama per pengguna.
- `POST /api/v1/alamat` — tambah; maksimal 5 per pengguna. Alamat pertama otomatis menjadi utama.
- `PUT /api/v1/alamat/{id}/utama` — memindahkan utama; idempoten.
- `DELETE /api/v1/alamat/{id}` — menghapus; utama yang dihapus digantikan yang tertua tersisa.
- `Alamat::potret()` — salinan untuk `pesanan.alamat_kirim`; dipakai `LayananCheckout` (D3).
- Skenario balapan `alamat-pertama`; satu utama, tidak melebihi batas.
- `PenjagaTransaksi` — menuntut sesi segar untuk checkout bernilai besar; dipakai D3, bukan middleware (nilainya baru diketahui setelah total dihitung server).

## Batas domain

D1 tidak menghitung harga, tidak membaca stok, tidak memindahkan saldo. Itu milik D2, D3, dan D4.
D1 tidak menyimpan peran; itu D5 lewat `PengelolaPeran`.
