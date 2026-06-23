<?php

use Illuminate\Support\Facades\Route;
use Modules\WebsiteV2\Http\Controllers\Api\WebsiteController;

Route::prefix('website-v2')->name('website-v2.api.')->group(function () {
    Route::get('/', [WebsiteController::class, 'index'])->name('index');
});
