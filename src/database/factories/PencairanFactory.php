<?php

namespace Database\Factories;

use App\Enums\StatusPencairan;
use App\Models\Pencairan;
use App\Models\Pengguna;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PencairanFactory extends Factory
{
    protected $model = Pencairan::class;

    public function definition(): array
    {
        return [
            'kode' => 'CAIR-'.Str::ulid(),
            'pengaju_id' => Pengguna::factory(),
            'jumlah_rupiah' => 100000,
            'rekening_tujuan' => '1234567890',
            'status' => StatusPencairan::Menunggu,
        ];
    }
}