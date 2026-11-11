<?php

namespace Database\Seeders;

use App\Models\Alamat;
use App\Models\Pengguna;
use Illuminate\Database\Seeder;

/**
 * Alamat contoh Sesi 4: Andi dua (Rumah utama + Kantor), Dewi satu, Bu Sri satu.
 * Nomor alamat mengikuti urutan pembuatan: 1, 2, 3, 4 (dijalankan setelah PenggunaSeeder).
 */
class AlamatSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command?->warn('AlamatSeeder dilewati: alamat contoh tidak boleh ada di produksi.');

            return;
        }

        $this->beri('andi@lapak.test', 'Rumah', 'Andi Nugroho', 'Jl. Slamet Riyadi No. 12', 'Solo', '57111', true);
        $this->beri('andi@lapak.test', 'Kantor', 'Andi Nugroho', 'Jl. Ir. Sutami No. 5', 'Solo', '57136', false);
        $this->beri('dewi@lapak.test', 'Rumah', 'Dewi Anggraini', 'Jl. Adi Sucipto No. 88', 'Solo', '57143', true);
        $this->beri('sri@lapak.test', 'Rumah', 'Sri Wahyuni', 'Jl. Mgr. Soegijopranoto No. 21', 'Solo', '57129', true);
    }

    private function beri(string $surel, string $label, string $nama, string $jalan, string $kota, string $kodePos, bool $utama): void
    {
        $pemilik = Pengguna::query()->where('surel', $surel)->firstOrFail();

        Alamat::updateOrCreate(
            ['pengguna_id' => $pemilik->id, 'label' => $label],
            [
                'nama_penerima' => $nama,
                'telepon' => '08'.substr(md5($surel), 0, 10),
                'jalan' => $jalan,
                'kota' => $kota,
                'kode_pos' => $kodePos,
                'utama' => $utama,
            ],
        );
    }
}
