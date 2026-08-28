<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;

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

// Authentication Routes (Login & Logout)
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.authenticate');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Admin Panel CMS Routes
Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/hero', [AdminController::class, 'hero'])->name('hero');
    Route::post('/hero', [AdminController::class, 'heroUpdate'])->name('hero.update');

    // Category CRUD
    Route::get('/kategori', [AdminController::class, 'kategori'])->name('kategori');
    Route::post('/kategori', [AdminController::class, 'categoryStore'])->name('kategori.store');
    Route::put('/kategori/{id}', [AdminController::class, 'categoryUpdate'])->name('kategori.update');
    Route::delete('/kategori/{id}', [AdminController::class, 'categoryDestroy'])->name('kategori.destroy');
    Route::patch('/kategori/{id}/toggle', [AdminController::class, 'categoryToggle'])->name('kategori.toggle');
    Route::post('/kategori/reorder', [AdminController::class, 'categoryReorder'])->name('kategori.reorder');

    // Product CRUD
    Route::get('/produk', [AdminController::class, 'produk'])->name('produk');
    Route::post('/produk', [AdminController::class, 'productStore'])->name('produk.store');
    Route::put('/produk/{id}', [AdminController::class, 'productUpdate'])->name('produk.update');
    Route::delete('/produk/{id}', [AdminController::class, 'productDestroy'])->name('produk.destroy');
    Route::patch('/produk/{id}/toggle', [AdminController::class, 'productToggle'])->name('produk.toggle');
    Route::post('/produk/reorder', [AdminController::class, 'productReorder'])->name('produk.reorder');

    // Flash Sale Management
    Route::post('/flash-sale/toggle', [AdminController::class, 'flashSaleToggle'])->name('flash_sale.toggle');
    Route::post('/flash-sale/settings', [AdminController::class, 'flashSaleSettings'])->name('flash_sale.settings');
    Route::post('/flash-sale/assign', [AdminController::class, 'flashSaleAssign'])->name('flash_sale.assign');
    Route::delete('/flash-sale/remove/{id}', [AdminController::class, 'flashSaleRemove'])->name('flash_sale.remove');
    Route::post('/flash-sale/reorder', [AdminController::class, 'flashSaleReorder'])->name('flash_sale.reorder');

    // Review Management & Mode Switch
    Route::get('/reviews', [AdminController::class, 'footer'])->name('reviews');
    Route::post('/reviews', [AdminController::class, 'reviewStore'])->name('reviews.store');
    Route::put('/reviews/{id}', [AdminController::class, 'reviewUpdate'])->name('reviews.update');
    Route::delete('/reviews/{id}', [AdminController::class, 'reviewDestroy'])->name('reviews.destroy');
    Route::patch('/reviews/{id}/toggle', [AdminController::class, 'reviewToggle'])->name('reviews.toggle');
    Route::post('/reviews/reorder', [AdminController::class, 'reviewReorder'])->name('reviews.reorder');
    Route::post('/reviews/mode', [AdminController::class, 'reviewMode'])->name('reviews.mode');
    Route::post('/reviews/google-config', [AdminController::class, 'reviewGoogleConfig'])->name('reviews.google_config');

    // Knowledge & Edukasi CMS
    Route::get('/knowledge', [AdminController::class, 'knowledge'])->name('knowledge');
    Route::post('/knowledge-categories', [AdminController::class, 'knowledgeCategoryStore'])->name('knowledge_categories.store');
    Route::put('/knowledge-categories/{id}', [AdminController::class, 'knowledgeCategoryUpdate'])->name('knowledge_categories.update');
    Route::delete('/knowledge-categories/{id}', [AdminController::class, 'knowledgeCategoryDestroy'])->name('knowledge_categories.destroy');
    Route::patch('/knowledge-categories/{id}/toggle', [AdminController::class, 'knowledgeCategoryToggle'])->name('knowledge_categories.toggle');
    Route::post('/knowledge-categories/reorder', [AdminController::class, 'knowledgeCategoryReorder'])->name('knowledge_categories.reorder');

    Route::post('/knowledge-articles', [AdminController::class, 'knowledgeArticleStore'])->name('knowledge_articles.store');
    Route::put('/knowledge-articles/{id}', [AdminController::class, 'knowledgeArticleUpdate'])->name('knowledge_articles.update');
    Route::delete('/knowledge-articles/{id}', [AdminController::class, 'knowledgeArticleDestroy'])->name('knowledge_articles.destroy');
    Route::patch('/knowledge-articles/{id}/toggle', [AdminController::class, 'knowledgeArticleToggle'])->name('knowledge_articles.toggle');
    Route::post('/knowledge-articles/reorder', [AdminController::class, 'knowledgeArticleReorder'])->name('knowledge_articles.reorder');

    Route::get('/keunggulan', [AdminController::class, 'keunggulan'])->name('keunggulan');
    Route::get('/footer', [AdminController::class, 'footer'])->name('footer');
    Route::post('/footer', [AdminController::class, 'footerUpdate'])->name('footer.update');
    Route::get('/seo', [AdminController::class, 'seo'])->name('seo');
    Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
});
