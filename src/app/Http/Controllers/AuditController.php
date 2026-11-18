<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\DaftarAuditRequest;
use App\Http\Resources\LogAuditResource;
use App\Models\LogAudit;
use App\Services\PemeriksaRantaiAudit;
use Illuminate\Http\JsonResponse;

final class AuditController extends Controller
{
    public function __construct(private readonly PemeriksaRantaiAudit $pemeriksa) {}

    public function index(DaftarAuditRequest $request): JsonResponse
    {
        $kueri = LogAudit::query()->orderByDesc('id');
        if ($request->validated('peristiwa')) {
            $kueri->where('peristiwa', $request->validated('peristiwa'));
        }
        $halaman = $kueri->paginate(config('lapak.katalog.per_halaman_bawaan'), ['*'], 'halaman');

        return $this->berhalaman($halaman, LogAuditResource::class);
    }

    public function rantai(DaftarAuditRequest $request): JsonResponse
    {
        $hasil = $this->pemeriksa->periksa($request->validated('jangkar'));

        return $this->sukses([
            'utuh' => $hasil->utuh(),
            'jumlah' => $hasil->jumlah,
            'jangkar' => $hasil->jangkar(),
            'temuan' => $hasil->temuan,
        ]);
    }
}