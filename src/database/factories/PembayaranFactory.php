<?php

namespace Database\Factories;

use App\Enums\StatusPembayaran;
use App\Models\Checkout;
use App\Models\Pembayaran;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Pembayaran>
 */
class PembayaranFactory extends Factory
{
    protected $model = Pembayaran::class;

    public function definition(): array
    {
        return [
            'checkout_id' => Checkout::factory(),
            // Yang disebut pembeli dan gerbang pembayaran, misalnya BYR-01K7...
            'kode' => 'BYR-'.Str::ulid(),
            'jumlah_rupiah' => fake()->numberBetween(50, 2_000) * 1_000,
            'status' => StatusPembayaran::Menunggu,
            'batas_bayar_pada' => now()->addHours(24),
        ];
    }

    /** Tagihan yang sudah lunas. */
    public function lunas(): static
    {
        return $this->state([
            'status' => StatusPembayaran::Lunas,
            'lunas_pada' => now(),
        ]);
    }
}
