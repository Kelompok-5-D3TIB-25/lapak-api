<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\NamaPeran;
use App\Enums\PeristiwaAudit;
use App\Enums\StatusToko;
use App\Exceptions\TransisiTokoTidakSah;
use App\Models\Pengguna;
use App\Models\Toko;
use Illuminate\Support\Facades\DB;

final class ModerasiToko
{
    public function __construct(
        private readonly PengelolaPeran $peran,
        private readonly PencatatAudit $audit,
    ) {}

    public function setujui(Toko $toko, Pengguna $admin): Toko
    {
        return DB::transaction(function () use ($toko, $admin): Toko {
            $asal = [StatusToko::Menunggu, StatusToko::Ditangguhkan];
            $this->pindahkan($toko, $asal, StatusToko::Aktif, $admin);
            $this->peran->beri($toko->pemilik()->firstOrFail(), NamaPeran::Penjual, $admin);

            $this->audit->catat(PeristiwaAudit::TokoDisetujui, $admin, "toko:{$toko->id}", [
                'slug' => $toko->slug,
            ]);

            return $toko->refresh();
        });
    }

    public function tangguhkan(Toko $toko, Pengguna $admin, string $alasan): Toko
    {
        return DB::transaction(function () use ($toko, $admin, $alasan): Toko {
            $this->pindahkan($toko, [StatusToko::Aktif], StatusToko::Ditangguhkan, $admin, $alasan);

            $this->audit->catat(PeristiwaAudit::TokoDitangguhkan, $admin, "toko:{$toko->id}", [
                'slug' => $toko->slug, 'alasan' => $alasan,
            ]);

            return $toko->refresh();
        });
    }

    /**
     * @param  list<StatusToko>  $dari
     */
    private function pindahkan(
        Toko $toko,
        array $dari,
        StatusToko $ke,
        Pengguna $admin,
        ?string $catatan = null,
    ): void {
        $berubah = Toko::query()
            ->whereKey($toko->id)
            ->whereIn('status', $dari)
            ->update([
                'status' => $ke,
                'dimoderasi_oleh' => $admin->id,
                'dimoderasi_pada' => now(),
                'catatan_moderasi' => $catatan,
            ]);
        if ($berubah === 0) {
            throw new TransisiTokoTidakSah($ke);
        }
    }
}