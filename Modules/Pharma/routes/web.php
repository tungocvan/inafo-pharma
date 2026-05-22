<?php

use Illuminate\Support\Facades\Route;
use Modules\Pharma\Http\Controllers\PharmaController;
use Modules\Pharma\Http\Controllers\DrugBidAwardController;

Route::prefix('admin')->name('admin.')->middleware(['web', 'auth:admin'])->group(function () {

    // Khối Quản lý Hồ sơ sản phẩm thuốc gốc
    Route::prefix('pharma')->name('pharma.')->group(function () {
        Route::get('/', [PharmaController::class, 'index'])->name('index');
        Route::get('/create', [PharmaController::class, 'create'])->name('create');
        Route::get('/{id}/edit', [PharmaController::class, 'edit'])->name('edit');
    });

    // Khối Quản lý Hồ sơ thuốc trúng thầu mới bổ sung
    Route::prefix('drug-bid-awards')->name('drug-bid-awards.')->group(function () {
        Route::get('/', [DrugBidAwardController::class, 'index'])->name('index');
        Route::get('/create', [DrugBidAwardController::class, 'create'])->name('create');
        Route::get('/{id}/edit', [DrugBidAwardController::class, 'edit'])->name('edit');
    });

});
