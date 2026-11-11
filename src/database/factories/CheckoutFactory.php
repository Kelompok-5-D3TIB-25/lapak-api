<?php

namespace Database\Factories;

use App\Models\Checkout;
use App\Models\Pengguna;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Checkout>
 */
class CheckoutFactory extends Factory
{
    protected $model = Checkout::class;

    public function definition(): array
    {
        return [
            'kode' => Str::ulid(),
            'pembeli_id' => Pengguna::factory()->pembeli(),
            'total_rupiah' => fake()->numberBetween(50, 2_000) * 1_000,
            'pengenal_sesi' => Str::ulid(),
        ];
    }
}
