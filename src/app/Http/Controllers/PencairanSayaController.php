<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Uang;
use App\Http\Resources\PencairanResource;
use App\Models\Pencairan;
use App\Services\LayananPencairan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PencairanSayaController extends Controller
{
    public function __construct(private readonly LayananPencairan $layanan) {}

    public function index(Request $request): JsonResponse
    {
        $halaman = Pencairan::query()
            ->where('pengaju_id', $request->user()->id)
            ->orderByDesc('id')
            ->paginate(config('lapak.katalog.per_halaman_bawaan'), ['*'], 'halaman');

        return $this->berhalaman($halaman, PencairanResource::class);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'jumlah_rupiah' => ['required', 'integer', 'min:1'],
            'rekening_tujuan' => ['required', 'string', 'max:80'],
        ]);

        $pencairan = $this->layanan->ajukan(
            $request->user(),
            Uang::dari($data['jumlah_rupiah']),
            $data['rekening_tujuan'],
        );

        return $this->sukses(PencairanResource::make($pencairan), 201);
    }
}
