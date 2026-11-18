<?php

use App\Http\Controllers\AuditController;
use App\Http\Controllers\KesehatanController;
use Illuminate\Support\Facades\Route;

Route::get('health', KesehatanController::class)->name('health');

Route::middleware(['auth:sesi', 'izin:audit.lihat', 'sesi.segar'])
    ->prefix('audit')
    ->name('audit.')
    ->group(function (): void {
        Route::get('/', [AuditController::class, 'index'])->name('index');
        Route::get('rantai', [AuditController::class, 'rantai'])->name('rantai');
    });