<?php

declare(strict_types=1);

namespace App\Services;

use App\Domain\Uang;
use App\Enums\JenisDompet;
use App\Enums\JenisMutasi;
use App\Enums\PeristiwaAudit;
use App\Exceptions\GalatDomain;
use App\Enums\StatusPencairan;
use App\Models\Pencairan;
use App\Models\Pengguna;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use LogicException;

final class LayananPencairan
{
    public function __construct(
        private readonly BukuBesar $buku,
        private readonly PencatatAudit $audit,
    ) {}

    public function ajukan(Pengguna $penjual, Uang $jumlah, string $rekeningTujuan): Pencairan
    {
        $minimum = Uang::dari(config('lapak.pencairan_minimum_rupiah'));

        if ($jumlah->rupiah < $minimum->rupiah) {
            throw new GalatDomain('LP-16', "Pencairan minimal {$minimum->format()}.", 422);
        }

        return DB::transaction(function () use ($penjual, $jumlah, $rekeningTujuan): Pencairan {
            $pencairan = new Pencairan;
            $pencairan->forceFill([
                'kode' => 'CAIR-'.Str::ulid(),
                'pengaju_id' => $penjual->id,
                'jumlah_rupiah' => $jumlah->rupiah,
                'rekening_tujuan' => $rekeningTujuan,
                'status' => StatusPencairan::Menunggu,
            ])->save();

            $this->buku->pindahkan(
                $this->buku->dompetPenjual($penjual),
                $this->buku->dompetSistem(JenisDompet::Pencairan),
                $jumlah,
                JenisMutasi::TahanPencairan,
                $pencairan->kode,
                'Pencairan diajukan',
            );

            $this->catat(PeristiwaAudit::PencairanDiajukan, $penjual, $pencairan, []);

            return $pencairan;
        });
    }

    public function setujui(Pencairan $pencairan, Pengguna $petugas): Pencairan
    {
        return $this->putuskan($pencairan, $petugas, StatusPencairan::Disetujui, null);
    }

    public function tolak(Pencairan $pencairan, Pengguna $petugas, string $catatan): Pencairan
    {
        return $this->putuskan($pencairan, $petugas, StatusPencairan::Ditolak, $catatan);
    }

    private function putuskan(
        Pencairan $pencairan,
        Pengguna $petugas,
        StatusPencairan $putusan,
        ?string $catatan,
    ): Pencairan {
        return DB::transaction(function () use ($pencairan, $petugas, $putusan, $catatan): Pencairan {
            $terkini = Pencairan::query()->lockForUpdate()->findOrFail($pencairan->id);

            if ($terkini->pengaju_id === $petugas->id) {
                throw new GalatDomain(
                    'LP-16', 'Pencairan tidak boleh diputuskan oleh pengajunya sendiri.', 403,
                );
            }

            if ($terkini->status !== StatusPencairan::Menunggu) {
                throw new GalatDomain('LP-16', 'Pencairan ini sudah diputuskan.', 409);
            }

            $terkini->forceFill([
                'status' => $putusan,
                'diputuskan_oleh' => $petugas->id,
                'diputuskan_pada' => now(),
                'catatan' => $catatan,
            ])->save();

            $disetujui = $putusan === StatusPencairan::Disetujui;
            $tujuan = $disetujui
                ? $this->buku->dompetSistem(JenisDompet::Eksternal)
                : $this->buku->dompetPenjual($terkini->pengaju);

            $this->buku->pindahkan(
                $this->buku->dompetSistem(JenisDompet::Pencairan),
                $tujuan,
                Uang::dari($terkini->jumlah_rupiah),
                $disetujui ? JenisMutasi::Pencairan : JenisMutasi::BatalPencairan,
                $terkini->kode,
                $disetujui ? 'Pencairan disetujui' : 'Pencairan ditolak',
            );

            $peristiwa = $disetujui
                ? PeristiwaAudit::PencairanDisetujui
                : PeristiwaAudit::PencairanDitolak;
            $tambahan = $catatan === null ? [] : ['catatan' => $catatan];
            $this->catat($peristiwa, $petugas, $terkini, $tambahan);

            return $terkini;
        });
    }

    /** @param array<string, mixed> $tambahan */
    private function catat(
        PeristiwaAudit $peristiwa,
        Pengguna $pelaku,
        Pencairan $pencairan,
        array $tambahan = [],
    ): void {
        $this->audit->catat($peristiwa, $pelaku, "pencairan:{$pencairan->kode}", [
            'jumlah_rupiah' => $pencairan->jumlah_rupiah,
            'pengaju_id' => $pencairan->pengaju_id,
            ...$tambahan,
        ]);
    }
}