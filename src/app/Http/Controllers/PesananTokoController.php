<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\AlasanBatalRequest;
use App\Http\Requests\DaftarPesananTokoRequest;
use App\Http\Requests\KirimPesananRequest;
use App\Http\Resources\PesananTokoResource;
use App\Models\Pesanan;
use App\Models\Toko;
use App\Services\LayananPesanan;
use App\Domain\Pesanan\PelakuPesanan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PesananTokoController extends Controller
{
    public function __construct(private readonly LayananPesanan $layanan) {}

    public function index(DaftarPesananTokoRequest $request): JsonResponse
    {
        $halaman = Pesanan::query()
            ->milikToko($this->tokoSaya($request))
            ->with('butir')
            ->when(
                $request->validated('status'),
                fn ($kueri, string $status) => $kueri->where('status', $status),
            )
            ->orderByDesc('id')
            ->paginate(config('lapak.katalog.per_halaman_bawaan'), ['*'], 'halaman');

        return $this->berhalaman($halaman, PesananTokoResource::class);
    }

    public function show(Request $request, string $nomor): JsonResponse
    {
        return $this->jawab($this->pesananToko($request, $nomor));
    }

    public function kemas(Request $request, string $nomor): JsonResponse
    {
        return $this->jawab($this->layanan->kemas($this->pesananToko($request, $nomor)));
    }

    public function kirim(KirimPesananRequest $request, string $nomor): JsonResponse
    {
        return $this->jawab($this->layanan->kirim(
            $this->pesananToko($request, $nomor),
            $request->validated('nomor_resi'),
        ));
    }

    public function batal(AlasanBatalRequest $request, string $nomor): JsonResponse
    {
        return $this->jawab($this->layanan->batalkan(
            $this->pesananToko($request, $nomor),
            PelakuPesanan::Penjual,
            $request->user(),
            $request->alasan(),
        ));
    }

    private function tokoSaya(Request $request): Toko
    {
        return Toko::query()->milik($request->user())->firstOrFail();
    }

    private function pesananToko(Request $request, string $nomor): Pesanan
    {
        return Pesanan::query()
            ->milikToko($this->tokoSaya($request))
            ->where('nomor', $nomor)
            ->firstOrFail();
    }

    private function jawab(Pesanan $pesanan): JsonResponse
    {
        return $this->sukses(PesananTokoResource::make($pesanan->load('butir')));
    }
}