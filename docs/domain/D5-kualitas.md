# D5 — Otorisasi, Audit, dan Kualitas

- Pemilik: @Rokkett2500
- Tabel: `peran`, `izin`, `log_audit` (Sesi 5), `kunci_idempotensi` (Sesi 4)
- Aturan: LP-2, LP-17, LP-1 (lewat uji arsitektur), LP-11 (lewat middleware), dan bentuk galat seluruh domain

## Pertanyaan yang dijawab

Siapa boleh melakukan apa, siapa yang sudah melakukan apa, dan apakah `main` layak dipercaya?

## Yang dibutuhkan dari domain lain

Tidak ada. D5 menyediakan kontrak yang dipakai semua domain.

## Yang disediakan untuk domain lain

- `App\Http\Concerns\ResponsApi` — amplop `sukses`, `data`, `meta`.
- `App\Exceptions\GalatDomain` — induk galat aturan bisnis.
- `App\Exceptions\PenanganGalatApi` — satu pintu galat API.
- `App\Http\Middleware\PaksaJson` — paksa `Accept: application/json`.
- `App\Http\Middleware\IdPermintaan` — `X-Request-Id` di respons dan log.
- `App\Http\Middleware\PeriksaIzin` — `middleware('izin:<nama>')`, satu izin per rute.
- `App\Services\PemeriksaIzin` — pemeriksa izin ber-cache per generasi.
- `App\Services\PengelolaPeran` — satu-satunya pintu memberi/mencabut peran; mencabut seluruh sesi akun.
- `App\Enums\NamaPeran`, `App\Enums\NamaIzin` — enum; pemetaan peran → izin ditulis satu-satunya di sini.
- `App\Models\Peran`, `App\Models\Izin` — tabel otorisasi.
- `tests/Feature/Sistem/matriks/<domain>.php` — satu-satunya baris otorisasi per rute.
- `tests/Feature/Sistem/RuteTerlindungTest.php` — setiap rute punya satu baris matriks
  yang menyebut peran dikenal; rute tanpa otorisasi `'*'` diukur, tidak ditulis kosong.
- `tests/Feature/Sistem/RuteIdempotenTest.php` — setiap rute POST terdaftar (dikunci / bukan).
- `App\Balapan\SkenarioBalapan`, `App\Console\Commands\Balapan`, `BalapanAnak`, `Serempak`,
  `KunciGanda`, `RebutanStok`, `RebutanCheckout`, `KeranjangGanda`, `BatalSaatCheckout`,
  `PenyesuaianStok`, `AlamatPertama`, `WebhookGanda` — uji serentak pada MySQL.
- `tests/Feature/Sistem/MatriksOtorisasiTest.php` — matriks otorisasi per rute.
- `tests/Feature/Sistem/IzinTest.php`, `tests/Feature/Sistem/IngatanIzinTest.php` — unit otorisasi.
- `GET /api/v1/health`.
- `.github/workflows/ci.yml` — gerbang `kualitas` dan `kualitas-mysql`.
- `php artisan lapak:periksa-buku` — invarian LP-13 (lima invarian).
- `php artisan lapak:matriks-izin` — laporan izin yang belum dipakai.
- `php artisan lapak:balapan`, `lapak:balapan-anak` — skenario balapan.

## Kontrak galat

Semua galat API memakai amplop:

```json
{
  "sukses": false,
  "galat": {
    "kode": "LP-13",
    "pesan": "Saldo tidak mencukupi untuk pemindahan ini.",
    "rincian": { "dompet": "penjual:1", "saldo": 0, "diminta": 5000 }
  }
}
```

Kode HTTP: 401 `BELUM_MASUK`, 403 `DITOLAK`, 404 `TIDAK_DITEMUKAN`, 405 `METODE_TIDAK_DIIZINKAN`, 422 `VALIDASI`, 429 `TERLALU_SERING`, 500 `GALAT_SERVER`.

## Aturan

- Controller tidak merakit galat sendiri.
- Galat tak terduga tidak membocorkan pesan asli saat `APP_DEBUG=false`.
- Setiap respons API membawa `X-Request-Id` buatan server.
- CI berjalan pada setiap PR ke `main`: Pint, migrate:fresh --seed, `lapak:periksa-buku`, dan Pest (SQLite di memori, di kedua tugas). Di `kualitas-mysql`, yang menyentuh MySQL hanyalah `migrate:fresh --seed`, `lapak:balapan --semua`, dan `lapak:periksa-buku`; Pest tetap berjalan di SQLite di memori (lihat SESI-4).
- Rute terlindung menyebut paling banyak satu izin. Rute yang tidak butuh izin khusus ditulis `'*'`.
- `PeranIzinSeeder` menyalin `NamaPeran::izinBawaan()` ke basis data; tidak ada peran yang memegang seluruh.
- Perubahan `.github/`, `config/lapak.php`, `composer.json`, `CODEOWNERS` wajib ditinjau pemilik D5.
- Kualitas diukur, bukan diklaim: skenario balapan yang tidak pernah merah tidak membuktikan apa pun.

## Batas domain

D5 tidak memiliki data bisnis. Tabel otorisasi; alat bantu kualitas dan pemantauan.
