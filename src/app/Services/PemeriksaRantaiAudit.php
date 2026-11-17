<?php

declare(strict_types=1);

namespace App\Services;

use App\Domain\Audit\HasilPemeriksaan;
use App\Domain\Audit\RantaiHash;
use App\Models\LogAudit;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class PemeriksaRantaiAudit
{
    private const POLA_JANGKAR = '/^(\d+):([0-9a-f]{64})$/';

    public function periksa(?string $jangkar = null): HasilPemeriksaan
    {
        [$barisJangkar, $hashJangkar] = $this->urai($jangkar);

        return DB::transaction(function () use ($barisJangkar, $hashJangkar): HasilPemeriksaan {
            $kepala = DB::table('rantai_audit')->where('id', 1)->first();
            $sebelumnya = RantaiHash::awal();
            $jumlah = 0;
            $temuan = [];

            foreach (LogAudit::query()->lazyById(500) as $log) {
                $jumlah++;

                if ($log->hash_sebelumnya !== $sebelumnya) {
                    $temuan[] = "Baris {$log->id} tidak menyambung ke baris sebelumnya: "
                        .'ada baris yang dihapus, disisipkan, atau ditukar.';
                }

                $hitung = RantaiHash::hitung($log->hash_sebelumnya, $log->isiTerlindung());

                if ($hitung !== $log->hash) {
                    $temuan[] = "Baris {$log->id} tidak cocok dengan hash-nya: isinya diubah.";
                }

                if ($jumlah === $barisJangkar && $log->hash !== $hashJangkar) {
                    $temuan[] = "Baris ke-{$jumlah} tidak cocok dengan jangkar: "
                        .'rantai dihitung ulang setelah jangkar itu dicatat.';
                }

                $sebelumnya = $log->hash;
            }

            if ($kepala === null) {
                $temuan[] = 'Kepala rantai tidak ada.';
            } elseif ($kepala->hash_terakhir !== $sebelumnya || (int) $kepala->jumlah !== $jumlah) {
                $temuan[] = "Kepala rantai mencatat {$kepala->jumlah} baris, log berisi {$jumlah}: "
                    .'ekor rantai dipotong, atau ada baris yang ditulis di luar PencatatAudit.';
            }

            if ($barisJangkar !== null && $jumlah < $barisJangkar) {
                $temuan[] = "Jangkar mencatat {$barisJangkar} baris, log hanya berisi {$jumlah}.";
            }

            return new HasilPemeriksaan($jumlah, $sebelumnya, $temuan);
        });
    }

    /** @return array{0: int|null, 1: string|null} */
    private function urai(?string $jangkar): array
    {
        if ($jangkar === null) {
            return [null, null];
        }

        if (preg_match(self::POLA_JANGKAR, $jangkar, $bagian) !== 1) {
            throw new InvalidArgumentException('Jangkar harus berbentuk <jumlah>:<hash 64 huruf>.');
        }

        return [(int) $bagian[1], $bagian[2]];
    }
}