<?php

use Illuminate\Support\Facades\Route;

function daftarPost(): array
{
    $gabung = ['berkunci' => [], 'tanpa_kunci' => []];

    foreach (glob(__DIR__.'/idempoten/*.php') as $berkas) {
        $domain = require $berkas;
        $gabung['berkunci'] = [...$gabung['berkunci'], ...$domain['berkunci']];
        $gabung['tanpa_kunci'] += $domain['tanpa_kunci'];
    }

    return $gabung;
}

/** @return array<string, list<string>> "POST alamat" => middleware rute itu */
function rutePost(): array
{
    $daftar = [];

    foreach (Route::getRoutes() as $rute) {
        if (! str_starts_with($rute->uri(), 'api/v1') || ! in_array('POST', $rute->methods(), true)) {
            continue;
        }

        $daftar['POST '.$rute->uri()] = $rute->gatherMiddleware();
    }

    return $daftar;
}

$daftar = daftarPost();
const POST_BERKUNCI = []; // placeholder, tests use daftarPost()

it('mencatat setiap rute POST pada tepat satu daftar', function () {
    $gabung = daftarPost();
    $tercatat = [...$gabung['berkunci'], ...array_keys($gabung['tanpa_kunci'])];
    $rute = array_keys(rutePost());

    $belum = array_values(array_diff($rute, $tercatat));
    $usang = array_values(array_diff($tercatat, $rute));
    $ganda = array_values(array_intersect($gabung['berkunci'], array_keys($gabung['tanpa_kunci'])));

    expect($belum)->toBeEmpty('Rute POST belum diklasifikasi: '.implode(', ', $belum))
        ->and($usang)->toBeEmpty('Tercatat tetapi rutenya tidak ada: '.implode(', ', $usang))
        ->and($ganda)->toBeEmpty('Tercatat pada kedua daftar: '.implode(', ', $ganda));
});

it('memasang idempoten pada setiap rute berkunci, di belakang kunci sesi', function () {
    $gabung = daftarPost();
    $salah = [];

    foreach ($gabung['berkunci'] as $nama) {
        $middleware = rutePost()[$nama] ?? [];
        $auth = array_search('auth:sesi', $middleware, true);
        $idempoten = array_search('idempoten', $middleware, true);

        if ($auth === false || $idempoten === false || $idempoten < $auth) {
            $salah[] = $nama;
        }
    }

    expect($salah)->toBeEmpty('Tanpa idempoten atau urutannya salah: '.implode(', ', $salah));
});

it('tidak memasang idempoten pada rute yang dinyatakan tidak memerlukannya', function () {
    $gabung = daftarPost();
    $salah = array_keys(array_filter(
        array_intersect_key(rutePost(), $gabung['tanpa_kunci']),
        fn (array $middleware) => in_array('idempoten', $middleware, true),
    ));

    expect($salah)->toBeEmpty('Tanpa kunci tetapi memasang idempoten: '.implode(', ', $salah));
});

it('menuliskan alasan untuk setiap rute tanpa kunci', function () {
    $gabung = daftarPost();
    foreach ($gabung['tanpa_kunci'] as $nama => $alasan) {
        expect(mb_strlen($alasan))->toBeGreaterThan(20, "Alasan {$nama} terlalu singkat.");
    }
});