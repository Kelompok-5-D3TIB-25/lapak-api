<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\NamaPeran;
use App\Enums\PeristiwaAudit;
use App\Models\Pengguna;
use App\Models\Peran;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use LogicException;

final class PengelolaPeran
{
    public function __construct(
        private readonly PemeriksaIzin $izin,
        private readonly PenerbitSesi $sesi,
        private readonly PencatatAudit $audit,
    ) {}

    public function beri(Pengguna $pengguna, NamaPeran $peran, ?Pengguna $oleh = null): bool
    {
        $idPeran = $this->idPeran($peran);

        return DB::transaction(function () use ($pengguna, $peran, $oleh, $idPeran): bool {
            if ($pengguna->peran()->where('peran.id', $idPeran)->exists()) {
                return false;
            }
            try {
                $pengguna->peran()->attach($idPeran, ['diberikan_oleh' => $oleh?->id]);
            } catch (UniqueConstraintViolationException) {
                return false;
            }
            $this->setelahBerubah($pengguna, PeristiwaAudit::PeranDiberikan, $peran, $oleh);

            return true;
        });
    }

    public function cabut(Pengguna $pengguna, NamaPeran $peran, ?Pengguna $oleh = null): bool
    {
        $idPeran = $this->idPeran($peran);

        return DB::transaction(function () use ($pengguna, $peran, $oleh, $idPeran): bool {
            if ($pengguna->peran()->detach($idPeran) === 0) {
                return false;
            }
            $this->setelahBerubah($pengguna, PeristiwaAudit::PeranDicabut, $peran, $oleh);

            return true;
        });
    }

    public function daftar(Pengguna $pengguna): array
    {
        return $pengguna->peran()->orderBy('peran.nama')->pluck('peran.nama')->all();
    }

    private function setelahBerubah(
        Pengguna $pengguna,
        PeristiwaAudit $peristiwa,
        NamaPeran $peran,
        ?Pengguna $oleh,
    ): void {
        $this->izin->lupakan($pengguna);
        $this->sesi->cabutSemua($pengguna);

        $this->audit->catat($peristiwa, $oleh, "pengguna:{$pengguna->id}", [
            'peran' => $peran->value,
        ]);
    }

    private function idPeran(NamaPeran $peran): int
    {
        $id = Peran::query()->where('nama', $peran->value)->value('id');
        if ($id === null) {
            throw new LogicException(
                "Peran {$peran->value} belum ada di basis data. Jalankan PeranIzinSeeder.",
            );
        }

        return (int) $id;
    }
}