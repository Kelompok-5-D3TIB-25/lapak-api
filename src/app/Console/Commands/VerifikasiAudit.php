<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\PemeriksaRantaiAudit;
use Illuminate\Console\Command;

final class VerifikasiAudit extends Command
{
    protected $signature = 'lapak:verifikasi-audit {--jangkar=}';

    protected $description = 'Memverifikasi rantai log audit.';

    public function handle(PemeriksaRantaiAudit $pemeriksa): int
    {
        try {
            $hasil = $pemeriksa->periksa($this->option('jangkar'));
        } catch (\InvalidArgumentException $galat) {
            $this->error($galat->getMessage());

            return self::INVALID;
        }

        if (! $hasil->utuh()) {
            foreach ($hasil->temuan as $temuan) {
                $this->error($temuan);
            }
            $this->error('Rantai log audit PUTUS: '.count($hasil->temuan).' temuan.');

            return self::FAILURE;
        }

        $this->info("Rantai log audit utuh: {$hasil->jumlah} baris.");
        $this->line('Jangkar: '.$hasil->jangkar());
        $this->line('Simpan jangkar ini di luar basis data, lalu berikan lewat --jangkar kelak.');

        return self::SUCCESS;
    }
}