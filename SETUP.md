# Panduan Setup

## Kebutuhan

- Git
- Docker Desktop / Docker Engine + Docker Compose
- (opsional) PHP 8.4 + Composer bila ingin menjalankan tanpa Docker

## Pertama Kali

### 1. Clone repository

```bash
git clone https://github.com/Kelompok-5-D3TIB-25/lapak-api.git
cd lapak-api
```

### 2. Siapkan berkas lingkungan

```bash
cp src/.env.example src/.env
```

### 3. Jalankan Docker

```bash
docker compose up -d
docker compose ps
```

Tunggu sampai container `lapak` dan `lapak-db` berstatus `running`.

### 4. Pasang dependensi

```bash
docker compose exec app composer install --no-interaction
```

### 5. Buat application key

```bash
docker compose exec app php artisan key:generate
```

### 6. Migrasi dan seeder

```bash
docker compose exec app php artisan migrate:fresh --seed
```

### 7. Buka aplikasi

Buka [http://localhost:8000](http://localhost:8000).

## Pemeriksaan sebelum PR

```bash
docker compose exec app ./vendor/bin/pint --test
docker compose exec app php artisan test
docker compose exec app php artisan lapak:periksa-buku
docker compose exec app php artisan route:list --path=api/v1
```

`phpunit.xml` sudah memaksa SQLite di memori (`force="true"`), sehingga `php artisan test`
tidak menyentuh database MySQL.

## Alur kerja harian

```bash
git switch main
git pull origin main
git switch -c sesi-<n>/<jenis>-<nama>
```

Setelah selesai:

```bash
git add -A
git commit -m "jelaskan perubahan secara singkat"
git push -u origin sesi-<n>/<jenis>-<nama>
```

Buka PR ke `main`, isi templat PR, dan minta satu rekan meninjau.

## Catatan

- Jangan meng-commit `src/.env`, `src/vendor/`, atau `src/database/database.sqlite`.
- Database Docker memakai MySQL; tidak perlu membuat SQLite untuk menjalankan aplikasi.
- Bila konfigurasi berubah: `docker compose exec app php artisan config:clear`.
- Bila migrasi gagal karena nama migration berubah:
  `docker compose exec app php artisan migrate:fresh --seed`.
