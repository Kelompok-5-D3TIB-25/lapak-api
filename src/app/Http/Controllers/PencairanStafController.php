<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\PencairanStafResource;
use App\Models\Pencairan;
use App\Services\LayananPencairan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PencairanStafController extends Controller
{
    public function __construct(private readonly LayananPencairan $layanan) {}

    public function index(Request $request): JsonResponse
    {
        $halaman = Pencairan::query()
            ->where('status', \App\Enums\StatusPencairan::Menunggu)
            ->orderByDesc('id')
            ->paginate(config('lapak.katalog.per_halaman_bawaan'), ['*'], 'halaman');

        return $this->berhalaman($halaman, PencairanStafResource::class);
    }

    public function setujui(Request $request, string $kode): JsonResponse
    {
        $pencairan = Pencairan::query()->where('kode', $kode)->firstOrFail();
        $hasil = $this->layanan->setujui($pencairan, $request->user());

        return $this->sukses(PencairanStafResource::make($hasil));
    }

    public function tolak(Request $request, string $kode): JsonResponse
    {
        $data = $request->validate(['catatan' => ['required', 'string', 'max:200']]);
        $pencairan = Pencairan::query()->where('kode', $kode)->firstOrFail();
        $hasil = $this->layanan->tolak($pencairan, $request->user(), $data['catatan']);

        return $this->sukses(PencairanStafResource::make($hasil));
    }
}