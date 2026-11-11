<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ButirPesananFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ButirPesanan extends Model
{
    /** @use HasFactory<ButirPesananFactory> */
    use HasFactory;

    protected $table = 'butir_pesanan';

    // LP-6: nama dan harga disalin dari produk pada saat checkout, bukan dibaca dari katalog.
    // Tiga kolom angkanya diisi sebagai salinan beku; tidak ada kolom yang diisi dari klien.
    protected $fillable = [];

    // Tabel butir_pesanan tidak punya created_at/updated_at.
    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'harga_rupiah' => 'integer',
            'kuantitas' => 'integer',
            'subtotal_rupiah' => 'integer',
        ];
    }

    /** @return BelongsTo<Pesanan, $this> */
    public function pesanan(): BelongsTo
    {
        return $this->belongsTo(Pesanan::class, 'pesanan_id');
    }

    /** @return BelongsTo<Produk, $this> */
    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }
}
