# Lapak

Marketplace UMKM Solo Raya. Studi kasus tim Praktik Pemrograman Back End, D3 Teknik Informatika UNS.

## Tim

| Nama | NIM | GitHub | Domain |
|---|---|---|---|
| Muhammad Halilintar Jagat Kertapati | V3425071 | @Rokkett2500 | D5 |
| Pradipta Ihsan Ariyanto | V3425079 | @ihsanpradipta45-alt | D1 |
| Muhammad Faiz Niwansyah | V3425013 | @victor-isMe | D2 |
| Nabela Azzahra Putri | V3425015 | @nabelazahra-ptr26 | D3 |
| Mirda Kinasih | V3425070 | @mrdaknsh | D4 |

## Menjalankan

Lihat [`SETUP.md`](SETUP.md).

```bash
docker compose up -d
docker compose exec app composer install
docker compose exec app php artisan migrate:fresh --seed
```

Aplikasi: `http://localhost:8000`.

## Pemeriksaan kualitas

```bash
docker compose exec app ./vendor/bin/pint --test
docker compose exec app php artisan test
docker compose exec app php artisan lapak:periksa-buku
```

## Endpoint Sesi 1

| Metode | Path |
|---|---|
| GET | `/api/v1/health` |
| GET | `/api/v1/produk` |
| GET | `/api/v1/produk/{slug}` |
| GET | `/api/v1/toko/{slug}` |
| POST | `/api/v1/registrasi` |
| POST | `/api/v1/simulasi-belanja` |

## Dokumen

- [`docs/TIM.md`](docs/TIM.md) — susunan tim dan integrator
- [`docs/PETA-SKEMA.md`](docs/PETA-SKEMA.md) — kepemilikan tabel dan migration
- [`docs/KONTRAK-API.md`](docs/KONTRAK-API.md) — amplop respons dan kode galat
- [`docs/ATURAN-BISNIS.md`](docs/ATURAN-BISNIS.md) — LP-1 sampai LP-17
- [`docs/domain/`](docs/domain/) — piagam tiap domain
- [`docs/sesi/SESI-1.md`](docs/sesi/SESI-1.md) — papan Sesi 1
- [`docs/sesi/SESI-2.md`](docs/sesi/SESI-2.md) — papan Sesi 2
- [`CONTRIBUTING.md`](CONTRIBUTING.md) — aturan cabang dan PR
- [`scripts/rekap-sesi.sh`](scripts/rekap-sesi.sh) — rekap kontribusi per sesi
