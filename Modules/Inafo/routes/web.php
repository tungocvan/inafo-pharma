<?php

use Illuminate\Support\Facades\Route;
use Modules\Inafo\Http\Controllers\AuthController;
use Modules\Inafo\Http\Controllers\HomeController;

$routePrefix = trim((string) config('inafo.inafo.route_prefix', 'inafo'), '/');
$routeName = trim((string) config('inafo.inafo.route_name', 'inafo'), '.');

Route::middleware('web')
    ->prefix($routePrefix)
    ->name($routeName . '.')
    ->group(function () {
        Route::get('/', [HomeController::class, 'index'])->name('home');
        Route::get('/login', [AuthController::class, 'login'])->name('login');
        Route::get('/register', [AuthController::class, 'register'])->name('register');
        Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
    });
