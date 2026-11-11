<?php

namespace Database\Factories;

use App\Models\Alamat;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Alamat>
 */
class AlamatFactory extends Factory
{
    protected $model = Alamat::class;

    public function definition(): array
    {
        return [
            'label' => 'Rumah',
            'nama_penerima' => fake()->name(),
            'telepon' => '08'.fake()->numerify('##########'),
            'jalan' => fake()->streetAddress(),
            'kota' => fake()->city(),
            'kode_pos' => fake()->numerify('#####'),
            'utama' => true,
        ];
    }

    /**
     * Alamat kedua dan seterusnya: bukan utama.
     * Dipakai uji yang menuntut lebih dari satu alamat milik satu pengguna.
     */
    public function cadangan(): static
    {
        return $this->state([
            'label' => 'Kantor',
            'utama' => false,
        ]);
    }
}
