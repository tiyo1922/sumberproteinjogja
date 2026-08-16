<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LandingController;

Route::get('/', [LandingController::class, 'index'])->name('home');
Route::get('/produk', [LandingController::class, 'index'])->name('products');
Route::get('/knowledge', [LandingController::class, 'index'])->name('knowledge');
Route::get('/tentang-kami', [LandingController::class, 'index'])->name('about');
Route::get('/kontak', [LandingController::class, 'index'])->name('contact');
