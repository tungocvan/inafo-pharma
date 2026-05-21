<?php

use Illuminate\Support\Facades\Route;
use Modules\Pharma\Http\Controllers\PharmaController;

Route::middleware(['web', 'auth:admin'])
    ->prefix('/admin/pharma')
    ->name('admin.pharma.')
    ->group(function () {
        Route::get('/', [PharmaController::class, 'index'])->name('index');
        Route::get('/create', [PharmaController::class, 'create'])->name('create');
        Route::get('/{id}/edit', [PharmaController::class, 'edit'])->name('edit');
    });
