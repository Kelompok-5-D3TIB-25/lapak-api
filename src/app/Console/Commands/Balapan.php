<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Balapan\SkenarioBalapan;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

/**
 * Menjalankan satu atau seluruh skenario uji balapan: beberapa proses PHP
 * serentak pada basis data yang sama, lalu keadaan akhirnya diperiksa
 * terhadap aturan yang tidak boleh dilanggar.
 *
 * Contoh: php artisan lapak:balapan serempak
 *         php artisan lapak:balapan --semua
 */
final class Balapan extends Command
{
    protected $signature = 'lapak:balapan
                            {skenario? : Nama skenario, misalnya serempak}
                            {--proses= : Batasi jumlah proses serentak}
                            {--semua : Jalankan seluruh skenario yang ada}';

    protected $description = 'Uji balapan: melepas banyak proses serempak pada MySQL';

    public function handle(): int
    {
        if (app()->isProduction()) {
            $this->error('lapak:balapan tidak boleh dijalankan di produksi.');

            return self::FAILURE;
        }

        $driver = DB::connection()->getDriverName();
        if ($driver !== 'mysql') {
            $this->error('Basis data saat ini '.$driver.', yang hanya mengizinkan satu penulis dan');

            $this->error('tidak mengenal SELECT ... FOR UPDATE. Uji balapan hanya berarti pada MySQL.');

            return self::FAILURE;
        }

        if ($this->option('semua')) {
            $gagal = 0;
            foreach (self::muatSemua() as $nama => $_) {
                if (! $this->jalankan($nama)) {
                    $gagal++;
                }
            }

            return $gagal === 0 ? self::SUCCESS : self::FAILURE;
        }

        $nama = $this->argument('skenario');
        if ($nama === null) {
            $this->error('Sebutkan skenario, misalnya lapak:balapan serempak, atau pakai --semua.');

            return self::FAILURE;
        }

        return $this->jalankan($nama) ? self::SUCCESS : self::FAILURE;
    }

    /** Memuat skenario dari nama kebab-case: rebutan-stok -> App\Balapan\RebutanStok. */
    public static function muat(string $nama): SkenarioBalapan
    {
        $kelas = 'App\\Balapan\\'.Str::studly(str_replace('-', ' ', $nama));

        if (! class_exists($kelas) || ! is_subclass_of($kelas, SkenarioBalapan::class)) {
            $dikenal = implode(', ', array_keys(self::muatSemua()));

            throw new \RuntimeException("Skenario {$nama} tidak ditemukan. Skenario yang tersedia: {$dikenal}");
        }

        /** @var SkenarioBalapan $skenario */
        $skenario = app($kelas);

        return $skenario;
    }

    /**
     * Seluruh skenario yang ada di folder App\Balapan/.
     * Menambah skenario baru tidak memerlukan perubahan pada perintah ini.
     *
     * @return array<string, class-string<SkenarioBalapan>>
     */
    public static function muatSemua(): array
    {
        $peta = [];

        foreach (glob(app_path('Balapan/*.php')) ?: [] as $berkas) {
            $kelas = 'App\\Balapan\\'.basename($berkas, '.php');

            if (is_subclass_of($kelas, SkenarioBalapan::class)) {
                $peta[Str::kebab(basename($berkas, '.php'))] = $kelas;
            }
        }
        ksort($peta);

        return $peta;
    }

    private function jalankan(string $nama): bool
    {
        $skenario = self::muat($nama);
        $jumlah = (int) ($this->option('proses') ?: $skenario->proses());

        $this->newLine();
        $this->line("<options=bold>{$nama}</>: {$skenario->keterangan()}");

        $konteks = $skenario->siapkan();

        // Setiap anak perlu waktu untuk menyalakan Laravel. Semuanya menunggu sampai detik ini,
        // lalu berangkat bersama. Tanpa garis mulai, anak pertama sudah selesai sebelum
        // anak terakhir lahir, dan tidak ada yang berlomba.
        $konteks['mulai'] = microtime(true) + max(2.0, $jumlah * 0.3);
        $muatan = base64_encode(json_encode($konteks, JSON_THROW_ON_ERROR));

        $anak = [];
        for ($nomor = 1; $nomor <= $jumlah; $nomor++) {
            $proses = new Process(
                [PHP_BINARY, 'artisan', 'lapak:balapan-anak', $nama, (string) $nomor, $muatan],
                base_path(),
            );
            $proses->setTimeout(120);
            $proses->start();
            $anak[] = $proses;
        }

        $hasil = [];
        foreach ($anak as $proses) {
            $proses->wait();
            $keluaran = trim($proses->getOutput());
            $hasil[] = $keluaran !== ''
                ? $keluaran
                : 'GALAT '.Str::limit(trim($proses->getErrorOutput()), 90);
        }

        $rekap = array_count_values($hasil);
        ksort($rekap);
        $this->table(
            ['Hasil', 'Jumlah proses'],
            array_map(null, array_keys($rekap), array_values($rekap)),
        );

        $pelanggaran = $skenario->periksa($konteks, $hasil);

        foreach ($pelanggaran as $pesan) {
            $this->error('PELANGGARAN: '.$pesan);
        }
        if ($pelanggaran === []) {
            $this->info("LULUS: {$jumlah} proses serentak, tidak ada aturan yang dilanggar.");
        }

        return $pelanggaran === [];
    }
}
