<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\AdminController;

// Customer Landing Page Routes
Route::get('/', [LandingController::class, 'index'])->name('home');
Route::get('/produk', [LandingController::class, 'index'])->name('products');
Route::get('/knowledge', [LandingController::class, 'index'])->name('knowledge');
Route::get('/tentang-kami', [LandingController::class, 'index'])->name('about');
Route::get('/kontak', [LandingController::class, 'index'])->name('contact');

// Admin Panel CMS Prototype Routes
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/hero', [AdminController::class, 'hero'])->name('hero');
    Route::get('/kategori', [AdminController::class, 'kategori'])->name('kategori');
    Route::get('/produk', [AdminController::class, 'produk'])->name('produk');
    Route::get('/keunggulan', [AdminController::class, 'keunggulan'])->name('keunggulan');
    Route::get('/knowledge', [AdminController::class, 'knowledge'])->name('knowledge');
    Route::get('/footer', [AdminController::class, 'footer'])->name('footer');
    Route::get('/seo', [AdminController::class, 'seo'])->name('seo');
    Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
});
