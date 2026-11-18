<?php

it('memakai versi 1.0.0 pada konfigurasi aplikasi', function () {
    expect(config('lapak.versi'))->toBe('1.0.0');
});

it('memakai tiga bagian nomor versi', function () {
    expect(config('lapak.versi'))->toMatch('/^\d+\.\d+\.\d+$/');
});
