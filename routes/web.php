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

// SEO Technical Foundation - Robots.txt & Sitemap.xml
Route::get('/robots.txt', function () {
    $canonicalUrl = rtrim(config('seo.canonical_url', 'https://sumberproteinjogja.com'), '/');
    $sitemapUrl = $canonicalUrl . '/sitemap.xml';

    $content = "User-agent: *\n"
        . "Allow: /\n"
        . "Disallow: /admin\n\n"
        . "Sitemap: " . $sitemapUrl . "\n";

    return response($content, 200, [
        'Content-Type' => 'text/plain',
    ]);
})->name('robots');

Route::get('/sitemap.xml', function () {
    $baseUrl = rtrim(config('seo.canonical_url', 'https://sumberproteinjogja.com'), '/');

    $routes = [
        '/',
        '/produk',
        '/knowledge',
        '/tentang-kami',
        '/kontak',
    ];

    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

    foreach ($routes as $route) {
        $loc = ($route === '/') ? $baseUrl . '/' : $baseUrl . $route;
        $xml .= "    <url>\n";
        $xml .= "        <loc>" . htmlspecialchars($loc, ENT_XML1, 'UTF-8') . "</loc>\n";
        $xml .= "    </url>\n";
    }

    $xml .= '</urlset>' . "\n";

    return response($xml, 200, [
        'Content-Type' => 'application/xml; charset=UTF-8',
    ]);
})->name('sitemap');

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
