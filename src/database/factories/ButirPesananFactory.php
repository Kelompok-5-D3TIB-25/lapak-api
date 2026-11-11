<?php

namespace Database\Factories;

use App\Models\ButirPesanan;
use App\Models\Pesanan;
use App\Models\Produk;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ButirPesanan>
 */
class ButirPesananFactory extends Factory
{
    protected $model = ButirPesanan::class;

    public function definition(): array
    {
        return [
            'pesanan_id' => Pesanan::factory(),
            'produk_id' => Produk::factory(),
            'nama_produk' => ucfirst(Str::words(fake()->sentence(3), true)),
            'harga_rupiah' => fake()->numberBetween(50, 2_000) * 1_000,
            'kuantitas' => 1,
            // subtotal diturunkan dari harga dan kuantitas, bukan diisi per permintaan: LP-6.
            'subtotal_rupiah' => fn (array $isi) => $isi['harga_rupiah'] * $isi['kuantitas'],
        ];
    }
}
