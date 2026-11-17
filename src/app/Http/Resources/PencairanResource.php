<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Pencairan;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class PencairanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'kode' => $this->kode,
            'jumlah_rupiah' => $this->jumlah_rupiah,
            'rekening_tujuan' => $this->rekening_tujuan,
            'status' => $this->status->value,
            'diputuskan_pada' => $this->diputuskan_pada?->toIso8601String(),
            'catatan' => $this->catatan,
            'dibuat_pada' => $this->created_at->toIso8601String(),
        ];
    }
}