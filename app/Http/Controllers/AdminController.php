<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\KnowledgeArticle;
use App\Models\KnowledgeCategory;
use App\Models\Product;
use App\Models\Review;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Repositories\Contracts\KnowledgeRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\ReviewRepositoryInterface;
use App\Repositories\Contracts\SiteSettingRepositoryInterface;
use App\Services\AnalyticsService;
use App\Services\KnowledgeArticleParser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    protected CategoryRepositoryInterface $categoryRepo;
    protected ProductRepositoryInterface $productRepo;
    protected KnowledgeRepositoryInterface $knowledgeRepo;
    protected ReviewRepositoryInterface $reviewRepo;
    protected SiteSettingRepositoryInterface $siteSettingRepo;

    /**
     * Inject canonical database repositories.
     */
    public function __construct(
        CategoryRepositoryInterface $categoryRepo,
        ProductRepositoryInterface $productRepo,
        KnowledgeRepositoryInterface $knowledgeRepo,
        ReviewRepositoryInterface $reviewRepo,
        SiteSettingRepositoryInterface $siteSettingRepo
    ) {
        $this->categoryRepo = $categoryRepo;
        $this->productRepo = $productRepo;
        $this->knowledgeRepo = $knowledgeRepo;
        $this->reviewRepo = $reviewRepo;
        $this->siteSettingRepo = $siteSettingRepo;
    }

    /**
     * Isolated Media Library list for Partners (scans storage/app/public/partners/).
     */
    private function getPartnerMediaLibrary(): array
    {
        $partners = [];

        $disk = Storage::disk('public');
        if ($disk->exists('partners')) {
            $files = $disk->files('partners');
            foreach ($files as $idx => $file) {
                $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                if (in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'svg'])) {
                    $filename = basename($file);
                    $sizeKb = round($disk->size($file) / 1024);
                    $partners[] = [
                        'id' => 'partner_' . ($idx + 1),
                        'filename' => $filename,
                        'path' => 'storage/' . $file,
                        'url' => asset('storage/' . $file),
                        'title' => $filename,
                        'size' => $sizeKb . ' KB',
                    ];
                }
            }
        }

        return $partners;
    }

    /**
     * Upload Partner Logo to isolated Partner Media Storage (storage/app/public/partners/).
     */
    public function partnerMediaUpload(Request $request)
    {
        $request->validate([
            'image' => 'required|file|image|mimes:jpeg,jpg,png,webp|max:2048',
        ]);

        $file = $request->file('image');
        $extension = $file->getClientOriginalExtension() ?: 'png';
        $filename = 'partner_' . time() . '_' . Str::random(8) . '.' . $extension;

        $path = $file->storeAs('partners', $filename, 'public');

        $item = [
            'id' => 'partner_' . uniqid(),
            'filename' => $filename,
            'path' => 'storage/' . $path,
            'url' => asset('storage/' . $path),
            'title' => $file->getClientOriginalName() ?: $filename,
            'size' => round($file->getSize() / 1024) . ' KB',
        ];

        return response()->json([
            'success' => true,
            'message' => 'Logo mitra berhasil diunggah ke storage terisolasi!',
            'media' => $item,
        ]);
    }

    /**
     * Delete a partner media image from isolated partner storage.
     */
    public function partnerMediaDelete(Request $request)
    {
        $request->validate([
            'path' => 'required|string',
        ]);

        $rawPath = $request->input('path');
        // Extract strictly the base filename to prevent directory traversal
        $filename = basename($rawPath);

        $disk = Storage::disk('public');
        if ($disk->exists('partners/' . $filename)) {
            $disk->delete('partners/' . $filename);

            return response()->json([
                'success' => true,
                'message' => 'Logo mitra berhasil dihapus dari storage.',
                'filename' => $filename,
                'path' => 'storage/partners/' . $filename,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'File tidak ditemukan di storage mitra.',
        ], 404);
    }

    /**
     * System preset media items (protected canonical assets).
     */
    private function getSystemPresetMedia(): array
    {
        return [
            [
                'id' => 1,
                'filename' => 'hero_meat_poultry_1786889302143.jpg',
                'path' => 'storage/media/hero_meat_poultry_1786889302143.jpg',
                'title' => 'Daging Sapi & Ayam Segar (Hero)',
                'resolution' => '1920 × 1080',
                'ratio' => '16:9',
                'size' => '769 KB',
                'is_recommended' => true,
                'is_deletable' => false,
            ],
            [
                'id' => 2,
                'filename' => 'hero_seafood_fish_1786889522926.jpg',
                'path' => 'storage/media/hero_seafood_fish_1786889522926.jpg',
                'title' => 'Seafood & Ikan Fillet Pilihan (Hero)',
                'resolution' => '1920 × 1080',
                'ratio' => '16:9',
                'size' => '914 KB',
                'is_recommended' => true,
                'is_deletable' => false,
            ],
            [
                'id' => 3,
                'filename' => 'hero_ready_cook_1786889537358.jpg',
                'path' => 'storage/media/hero_ready_cook_1786889537358.jpg',
                'title' => 'Ready to Cook & Sayur Siap Masak (Hero)',
                'resolution' => '1920 × 1080',
                'ratio' => '16:9',
                'size' => '1055 KB',
                'is_recommended' => true,
                'is_deletable' => false,
            ],
            [
                'id' => 4,
                'filename' => 'cat_daging_1786889601901.jpg',
                'path' => 'storage/media/cat_daging_1786889601901.jpg',
                'title' => 'Daging Sapi Slice & Sengkel Rawon',
                'resolution' => '800 × 600',
                'ratio' => '4:3',
                'size' => '605 KB',
                'is_recommended' => true,
                'is_deletable' => false,
            ],
            [
                'id' => 5,
                'filename' => 'cat_ayam_1786889762714.jpg',
                'path' => 'storage/media/cat_ayam_1786889762714.jpg',
                'title' => 'Ayam Broiler & Dada Fillet Boneless',
                'resolution' => '800 × 600',
                'ratio' => '4:3',
                'size' => '620 KB',
                'is_recommended' => true,
                'is_deletable' => false,
            ],
            [
                'id' => 6,
                'filename' => 'cat_ikan_1786889777193.jpg',
                'path' => 'storage/media/cat_ikan_1786889777193.jpg',
                'title' => 'Ikan Gurame & Dori Fillet Segar',
                'resolution' => '800 × 600',
                'ratio' => '4:3',
                'size' => '617 KB',
                'is_recommended' => true,
                'is_deletable' => false,
            ],
            [
                'id' => 7,
                'filename' => 'cat_sayur_1786889823537.jpg',
                'path' => 'storage/media/cat_sayur_1786889823537.jpg',
                'title' => 'Sayuran Segar Organik & Siap Olah',
                'resolution' => '800 × 600',
                'ratio' => '4:3',
                'size' => '692 KB',
                'is_recommended' => true,
                'is_deletable' => false,
            ],
            [
                'id' => 8,
                'filename' => 'know_thawing_1786890543832.jpg',
                'path' => 'storage/media/know_thawing_1786890543832.jpg',
                'title' => 'Dapur & Edukasi Thawing Higienis',
                'resolution' => '1200 × 800',
                'ratio' => '3:2',
                'size' => '754 KB',
                'is_recommended' => true,
                'is_deletable' => false,
            ],
            [
                'id' => 9,
                'filename' => 'prod_beef_slice_1786890263309.jpg',
                'path' => 'storage/media/prod_beef_slice_1786890263309.jpg',
                'title' => 'Daging Sapi Shortplate Slice 500g',
                'resolution' => '1200 × 900',
                'ratio' => '4:3',
                'size' => '603 KB',
                'is_recommended' => true,
                'is_deletable' => false,
            ],
            [
                'id' => 10,
                'filename' => 'prod_ayam_bumbu_1786889976117.jpg',
                'path' => 'storage/media/prod_ayam_bumbu_1786889976117.jpg',
                'title' => 'Ayam Ungkep Bumbu Kuning Lengkuas',
                'resolution' => '1200 × 900',
                'ratio' => '4:3',
                'size' => '817 KB',
                'is_recommended' => true,
                'is_deletable' => false,
            ],
            [
                'id' => 11,
                'filename' => 'prod_ikan_gurame_1786890735183.jpg',
                'path' => 'storage/media/prod_ikan_gurame_1786890735183.jpg',
                'title' => 'Fillet Ikan Gurame Segar Bersih',
                'resolution' => '1200 × 900',
                'ratio' => '4:3',
                'size' => '694 KB',
                'is_recommended' => true,
                'is_deletable' => false,
            ],
            [
                'id' => 12,
                'filename' => 'prod_sayur_mix_1786890756703.jpg',
                'path' => 'storage/media/prod_sayur_mix_1786890756703.jpg',
                'title' => 'Paket Sayur Sop Komplit Higienis',
                'resolution' => '1200 × 900',
                'ratio' => '4:3',
                'size' => '574 KB',
                'is_recommended' => true,
                'is_deletable' => false,
            ],
        ];
    }

    /**
     * Dynamic usage detection across active database entities.
     * Returns a map: [ filename => [ 'is_in_use' => bool, 'usage_count' => int, 'usage_locations' => string[] ] ]
     */
    private function getAllMediaUsage(): array
    {
        $usageMap = [];

        // 1. Products
        try {
            $products = DB::table('products')->select('id', 'name', 'image')->get();
            foreach ($products as $p) {
                if (!empty($p->image)) {
                    $fn = basename($p->image);
                    $usageMap[$fn][] = "Produk: " . $p->name;
                }
            }
        } catch (\Throwable $e) {}

        // 2. Categories
        try {
            $categories = DB::table('categories')->select('id', 'name', 'image')->get();
            foreach ($categories as $c) {
                if (!empty($c->image)) {
                    $fn = basename($c->image);
                    $usageMap[$fn][] = "Kategori: " . $c->name;
                }
            }
        } catch (\Throwable $e) {}

        // 3. Knowledge Articles
        try {
            $articles = DB::table('knowledge_articles')->select('id', 'title', 'image')->get();
            foreach ($articles as $a) {
                if (!empty($a->image)) {
                    $fn = basename($a->image);
                    $usageMap[$fn][] = "Edukasi: " . $a->title;
                }
            }
        } catch (\Throwable $e) {}

        // 4. Users (Avatar)
        try {
            $users = DB::table('users')->select('id', 'name', 'avatar')->get();
            foreach ($users as $u) {
                if (!empty($u->avatar)) {
                    $fn = basename($u->avatar);
                    $usageMap[$fn][] = "Avatar Admin: " . $u->name;
                }
            }
        } catch (\Throwable $e) {}

        // 5. Site Settings (hero, hero_drafts, seo, site, etc.)
        try {
            $settings = DB::table('site_settings')->select('id', 'key', 'value')->get();
            $disk = Storage::disk('public');
            $files = $disk->exists('media') ? $disk->files('media') : [];

            foreach ($settings as $s) {
                $val = (string) $s->value;
                if (empty($val)) continue;

                foreach ($files as $file) {
                    $fn = basename($file);
                    if (str_contains($val, $fn)) {
                        $locLabel = match ($s->key) {
                            'hero' => 'Hero Slider Utama',
                            'hero_drafts' => 'Draft Hero Slider',
                            'seo' => 'SEO OpenGraph Meta',
                            'site' => 'Identitas Brand & Situs',
                            default => 'Pengaturan: ' . $s->key,
                        };
                        if (!isset($usageMap[$fn]) || !in_array($locLabel, $usageMap[$fn], true)) {
                            $usageMap[$fn][] = $locLabel;
                        }
                    }
                }
            }
        } catch (\Throwable $e) {}

        return $usageMap;
    }

    /**
     * Centralized Media Library list for Global Media Picker.
     */
    private function getMediaLibrary(): array
    {
        $presetMeta = [];
        foreach ($this->getSystemPresetMedia() as $pm) {
            $presetMeta[$pm['filename']] = $pm;
        }

        $usageMap = $this->getAllMediaUsage();

        $items = [];
        $disk = Storage::disk('public');
        if ($disk->exists('media')) {
            $files = $disk->files('media');
            rsort($files);
            foreach ($files as $idx => $file) {
                $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                if (in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'svg', 'ico'])) {
                    $filename = basename($file);
                    $fullPath = $disk->path($file);
                    $sizeKb = round($disk->size($file) / 1024);

                    $locations = $usageMap[$filename] ?? [];
                    $isInUse = !empty($locations);

                    // Check if file has preset metadata
                    $preset = $presetMeta[$filename] ?? null;

                    $title = $preset['title'] ?? $filename;
                    $resolution = $preset['resolution'] ?? 'Custom Upload';
                    $ratio = $preset['ratio'] ?? 'Auto';
                    $isRecommended = $preset['is_recommended'] ?? false;

                    // If resolution is not in preset, attempt getimagesize
                    if ($resolution === 'Custom Upload' && file_exists($fullPath)) {
                        $imgInfo = @getimagesize($fullPath);
                        if ($imgInfo && !empty($imgInfo[0]) && !empty($imgInfo[1])) {
                            $resolution = $imgInfo[0] . ' × ' . $imgInfo[1];
                        }
                    }

                    $items[] = [
                        'id' => 'media_' . ($idx + 1) . '_' . md5($file),
                        'filename' => $filename,
                        'path' => 'storage/' . $file,
                        'url' => asset('storage/' . $file),
                        'title' => $title,
                        'resolution' => $resolution,
                        'ratio' => $ratio,
                        'size' => $sizeKb . ' KB',
                        'is_recommended' => $isRecommended,
                        'is_deletable' => true,
                        'is_in_use' => $isInUse,
                        'usage_count' => count($locations),
                        'usage_locations' => $locations,
                    ];
                }
            }
        }

        return $items;
    }

    /**
     * Upload Media file to centralized Public Media Storage (storage/app/public/media/).
     */
    public function mediaUpload(Request $request)
    {
        $request->validate([
            'image' => 'required|file|image|mimes:jpeg,jpg,png,webp|max:5120',
        ]);

        $file = $request->file('image');
        $extension = strtolower($file->getClientOriginalExtension() ?: 'png');
        $filename = 'media_' . time() . '_' . Str::random(8) . '.' . $extension;

        $path = $file->storeAs('media', $filename, 'public');

        $item = [
            'id' => 'media_' . uniqid(),
            'filename' => $filename,
            'path' => 'storage/' . $path,
            'url' => asset('storage/' . $path),
            'title' => $file->getClientOriginalName() ?: $filename,
            'resolution' => 'Custom Upload',
            'ratio' => 'Auto',
            'size' => round($file->getSize() / 1024) . ' KB',
            'is_recommended' => false,
            'is_deletable' => true,
            'is_in_use' => false,
            'usage_count' => 0,
            'usage_locations' => [],
        ];

        return response()->json([
            'success' => true,
            'message' => 'File media berhasil diunggah ke storage!',
            'media' => $item,
        ]);
    }

    /**
     * Delete Media file from centralized Public Media Storage (storage/app/public/media/).
     */
    public function mediaDestroy(Request $request)
    {
        $request->validate([
            'path' => 'required|string',
        ]);

        $rawPath = $request->input('path');
        // Extract strictly the base filename to prevent directory traversal
        $filename = basename($rawPath);

        // Path safety verification
        if (empty($filename) || $filename === '.' || $filename === '..' || str_contains($rawPath, '..')) {
            return response()->json([
                'success' => false,
                'message' => 'Path file tidak valid.',
            ], 422);
        }

        $disk = Storage::disk('public');
        if ($disk->exists('media/' . $filename)) {
            $disk->delete('media/' . $filename);

            return response()->json([
                'success' => true,
                'message' => 'File media "' . $filename . '" berhasil dihapus dari server.',
                'filename' => $filename,
                'path' => 'storage/media/' . $filename,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'File tidak ditemukan di server.',
        ], 404);
    }

    /**
     * Contact settings helper from canonical config.
     */
    private function getContactSettings(): array
    {
        return config('site.contact', []);
    }

    /**
     * Master Category Section Settings (Header).
     */
    private function getCategorySectionSettings(): array
    {
        return $this->siteSettingRepo->get('category_section', [
            'label' => 'Kategori Utama',
            'title' => 'Mau Masak Apa Hari Ini?',
            'subtitle' => 'Pilih bahan masak sesuai kebutuhanmu. Dari potongan daging segar, ayam bumbu, ikan laut, hingga sayuran siap cemplung.'
        ]);
    }

    /**
     * SEC-04: Sanitize External Navigation URL (Category A).
     * Only allows valid http:// and https:// URLs with a valid host.
     */
    private function sanitizeExternalUrl(?string $url): ?string
    {
        if ($url === null) {
            return null;
        }

        $clean = trim($url);
        if ($clean === '') {
            return null;
        }

        // Reject control characters or null bytes
        if (preg_match('/[\x00-\x1F\x7F]/', $clean)) {
            return null;
        }

        // Explicitly reject dangerous pseudo-protocols
        if (preg_match('/^(javascript|vbscript|data|file|blob|about):/i', $clean)) {
            return null;
        }

        // Scheme validation
        $scheme = strtolower((string) parse_url($clean, PHP_URL_SCHEME));
        if (!in_array($scheme, ['http', 'https'], true)) {
            return null;
        }

        // Host validation
        $host = parse_url($clean, PHP_URL_HOST);
        if (empty($host) || !filter_var($clean, FILTER_VALIDATE_URL)) {
            return null;
        }

        return $clean;
    }

    /**
     * SEC-04: Sanitize Internal Navigation / Anchor / Path URL (Category B).
     * Allows #anchors, /internal/paths, and valid http/https URLs.
     */
    private function sanitizeNavigationUrl(?string $url, string $fallback = '#'): string
    {
        if ($url === null) {
            return $fallback;
        }

        $clean = trim($url);
        if ($clean === '') {
            return $fallback;
        }

        // Reject control characters or null bytes
        if (preg_match('/[\x00-\x1F\x7F]/', $clean)) {
            return $fallback;
        }

        // Explicitly reject dangerous pseudo-protocols
        if (preg_match('/^(javascript|vbscript|data|file|blob|about):/i', $clean)) {
            return $fallback;
        }

        // Reject protocol-relative URLs (//evil.com)
        if (str_starts_with($clean, '//')) {
            return $fallback;
        }

        // Allow Section Anchors (#produk, #kategori, #hero)
        if (str_starts_with($clean, '#')) {
            return $clean;
        }

        // Allow Internal Paths (/produk, /tentang-kami, /kebijakan-privasi)
        if (str_starts_with($clean, '/')) {
            return $clean;
        }

        // Allow Contact Registry alphanumeric keys (e.g. order_wa, admin_wa, cs_care)
        if (preg_match('/^[a-zA-Z0-9_\-]+$/', $clean)) {
            return $clean;
        }

        // Allow valid external http/https URLs
        $scheme = strtolower((string) parse_url($clean, PHP_URL_SCHEME));
        if (in_array($scheme, ['http', 'https'], true) && filter_var($clean, FILTER_VALIDATE_URL)) {
            return $clean;
        }

        return $fallback;
    }

    /**
     * SEC-04: Sanitize Image & Media Asset URLs (Category C).
     * Allows local storage paths (storage/media/..., storage/partners/..., images/...)
     * and valid external http/https image URLs.
     */
    private function sanitizeImageUrl(?string $url, ?string $fallback = null): ?string
    {
        if ($url === null) {
            return $fallback;
        }

        $clean = trim($url);
        if ($clean === '') {
            return $fallback;
        }

        // Reject control characters
        if (preg_match('/[\x00-\x1F\x7F]/', $clean)) {
            return $fallback;
        }

        // Explicitly reject dangerous pseudo-protocols
        if (preg_match('/^(javascript|vbscript|data|file|blob|about):/i', $clean)) {
            return $fallback;
        }

        // Reject directory traversal
        if (str_contains($clean, '..')) {
            return $fallback;
        }

        // Allow local storage and image assets
        if (str_starts_with($clean, 'storage/') || str_starts_with($clean, 'images/')) {
            return $clean;
        }

        // Allow valid external image URLs
        $scheme = strtolower((string) parse_url($clean, PHP_URL_SCHEME));
        if (in_array($scheme, ['http', 'https'], true) && filter_var($clean, FILTER_VALIDATE_URL)) {
            return $clean;
        }

        return $fallback;
    }

    /**
     * SEC-04: Sanitize Google Maps Embed URL (Category D).
     * Strictly allows only HTTPS Google Maps embed endpoints.
     */
    private function sanitizeMapsEmbedUrl(?string $url): ?string
    {
        if ($url === null) {
            return null;
        }

        $clean = trim($url);
        if ($clean === '') {
            return null;
        }

        // Reject control characters
        if (preg_match('/[\x00-\x1F\x7F]/', $clean)) {
            return null;
        }

        // Must be valid URL
        if (!filter_var($clean, FILTER_VALIDATE_URL)) {
            return null;
        }

        $scheme = strtolower((string) parse_url($clean, PHP_URL_SCHEME));
        if ($scheme !== 'https') {
            return null;
        }

        $host = strtolower((string) parse_url($clean, PHP_URL_HOST));
        $allowedHosts = [
            'www.google.com',
            'maps.google.com',
            'google.com',
            'www.google.co.id',
            'maps.google.co.id',
        ];

        if (!in_array($host, $allowedHosts, true)) {
            return null;
        }

        $path = (string) parse_url($clean, PHP_URL_PATH);
        if (!str_starts_with($path, '/maps/embed')) {
            return null;
        }

        return $clean;
    }

    /**
     * SEC-05: Strict Reorder Payload Validator.
     * Validates associative [id => sort_order] or sequential [[id => x, sort_order => y]] payloads,
     * strictly rejecting mixed, empty, or malformed structures.
     *
     * @return array Validated payload array with 'orders' key
     */
    private function validateReorderPayload(Request $request, string $sortOrderField = 'sort_order'): array
    {
        $ordersInput = $request->input('orders');

        if (!is_array($ordersInput) || empty($ordersInput)) {
            return $request->validate([
                'orders' => 'required|array|min:1',
            ]);
        }

        $isAllArrays = count(array_filter($ordersInput, 'is_array')) === count($ordersInput);
        $isAllScalars = count(array_filter($ordersInput, 'is_array')) === 0;

        if (!$isAllArrays && !$isAllScalars) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'orders' => ['Format payload urutan tidak valid (tercampur antara associative dan sequential).'],
            ]);
        }

        if ($isAllArrays) {
            $allowedKeys = array_unique(['id', 'sort_order', 'order', $sortOrderField]);
            foreach ($ordersInput as $idx => $item) {
                if (!is_array($item)) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        "orders.{$idx}" => ['Elemen urutan harus berupa array / objek valid.'],
                    ]);
                }
                $extraKeys = array_diff(array_keys($item), $allowedKeys);
                if (!empty($extraKeys)) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        "orders.{$idx}" => ['Elemen urutan mengandung parameter tidak dikenal: ' . implode(', ', $extraKeys)],
                    ]);
                }
            }

            $rules = [
                'orders' => 'required|array|min:1',
                'orders.*.id' => 'required|integer|min:1',
                'orders.*.sort_order' => 'nullable|integer|min:0|max:1000000',
                'orders.*.order' => 'nullable|integer|min:0|max:1000000',
            ];
            if ($sortOrderField !== 'sort_order' && $sortOrderField !== 'order') {
                $rules['orders.*.' . $sortOrderField] = 'nullable|integer|min:0|max:1000000';
            }
            return $request->validate($rules);
        }

        return $request->validate([
            'orders' => [
                'required',
                'array',
                'min:1',
                function ($attribute, $value, $fail) {
                    if (!is_array($value)) return;
                    foreach ($value as $key => $val) {
                        if (!is_int($key) && (!is_string($key) || !ctype_digit((string) $key))) {
                            $fail("Kunci {$attribute} harus berupa ID integer positif.");
                            return;
                        }
                        if ((int) $key < 1) {
                            $fail("ID {$attribute} minimal 1.");
                            return;
                        }
                    }
                },
            ],
            'orders.*' => 'required|integer|min:0|max:1000000',
        ]);
    }

    /**
     * Display the Admin Sales & Traffic Analytics Dashboard.
     */
    public function dashboard(AnalyticsService $analyticsService)
    {
        $analytics = $analyticsService->getDashboardPayload();

        return view('admin.dashboard', compact('analytics'));
    }

    /**
     * Hero Slider Management Screen (Read & Mutation Integration).
     */
    public function hero()
    {
        $heroSetting = $this->siteSettingRepo->get('hero', [
            'badge' => 'Pusat Bahan Segar & Frozen Jogja',
            'title' => 'Belanja Daging Sapi, Ayam, Seafood & Sayuran Siap Olah Praktis',
            'headline_prefix' => 'Bahan Masak',
            'highlight' => 'Siap Olah',
            'headline_suffix' => ', Tinggal Masak.',
            'subtitle' => 'Pilihan tepat keluarga & pengusaha kuliner di Yogyakarta. Produk higienis dengan standar cold-chain terjamin, dipotong rapi, dan dikirim aman sampai ke dapur Anda.',
            'description' => 'Pilihan tepat keluarga & pengusaha kuliner di Yogyakarta. Produk higienis dengan standar cold-chain terjamin, dipotong rapi, dan dikirim aman sampai ke dapur Anda.',
            'whatsapp_button_text' => 'Konsultasi & Order Cepat via WhatsApp',
            'primary_cta_text' => 'Belanja Sekarang',
            'primary_cta_link' => '#produk',
            'catalog_button_text' => 'Lihat Katalog Lengkap',
            'secondary_cta_text' => 'Lihat Produk',
            'secondary_cta_link' => '#kategori',
            'images' => [
                'storage/media/hero-1.jpg',
                'storage/media/hero-2.jpg',
                'storage/media/hero-3.jpg',
            ],
        ]);

        $defaultTrust = [
            ['id' => 1, 'text' => '100% Halal & Higienis', 'is_active' => true, 'active' => true, 'sort_order' => 1],
            ['id' => 2, 'text' => 'Standar Rantai Dingin (Cold Chain)', 'is_active' => true, 'active' => true, 'sort_order' => 2],
            ['id' => 3, 'text' => 'Pengiriman Cepat Se-Jogja', 'is_active' => true, 'active' => true, 'sort_order' => 3],
        ];

        $rawTrustItems = $this->siteSettingRepo->get('hero_trust_items', $defaultTrust);
        $heroTrustItems = [];
        for ($i = 0; $i < 3; $i++) {
            $item = (is_array($rawTrustItems) && isset($rawTrustItems[$i])) ? $rawTrustItems[$i] : $defaultTrust[$i];
            $heroTrustItems[] = [
                'id' => $item['id'] ?? ($i + 1),
                'text' => $item['text'] ?? ($defaultTrust[$i]['text'] ?? ''),
                'is_active' => isset($item['is_active']) ? (bool) $item['is_active'] : (isset($item['active']) ? (bool) $item['active'] : true),
                'active' => isset($item['active']) ? (bool) $item['active'] : (isset($item['is_active']) ? (bool) $item['is_active'] : true),
                'sort_order' => $item['sort_order'] ?? ($i + 1),
            ];
        }

        $heroPartners = $this->siteSettingRepo->get('hero_partners', [
            'badge' => 'Kepercayaan Mitra',
            'title' => 'Telah Dipercaya Restoran, Cafe, Catering & Rumah Tangga di Jogja',
            'partners' => [
                ['id' => 1, 'name' => 'Restoran & Cafe Jogja', 'logo' => 'storage/partners/partner_1787924153_Ag8XLYM1.jpg', 'is_active' => true, 'sort_order' => 1],
                ['id' => 2, 'name' => 'Katering & Horeka', 'logo' => 'storage/partners/partner_1787925467_Hjp87zCe.png', 'is_active' => true, 'sort_order' => 2],
                ['id' => 3, 'name' => 'Rumah Tangga Jogja', 'logo' => 'storage/partners/partner_1787925555_vj3dgBxF.png', 'is_active' => true, 'sort_order' => 3],
            ]
        ]);

        $defaultPresets = [
            [
                'id' => 1,
                'name' => 'Hero Utama (Live DB)',
                'status' => 'Aktif',
                'badge' => $heroSetting['badge'] ?? 'Pusat Bahan Segar & Frozen Jogja',
                'title' => $heroSetting['title'] ?? 'Belanja Daging Sapi, Ayam, Seafood & Sayuran Siap Olah Praktis',
                'headline_prefix' => $heroSetting['headline_prefix'] ?? 'Bahan Masak',
                'highlight' => $heroSetting['highlight'] ?? 'Siap Olah',
                'headline_suffix' => $heroSetting['headline_suffix'] ?? ', Tinggal Masak.',
                'description' => $heroSetting['subtitle'] ?? ($heroSetting['description'] ?? 'Pilihan tepat keluarga & pengusaha kuliner di Yogyakarta.'),
                'primary_cta_text' => (isset($heroSetting['primary_cta_text']) && trim($heroSetting['primary_cta_text']) !== '')
                    ? $heroSetting['primary_cta_text']
                    : ($heroSetting['whatsapp_button_text'] ?? 'Belanja Sekarang'),
                'primary_cta_link' => $heroSetting['primary_cta_link'] ?? '#produk',
                'secondary_cta_text' => (isset($heroSetting['secondary_cta_text']) && trim($heroSetting['secondary_cta_text']) !== '')
                    ? $heroSetting['secondary_cta_text']
                    : ($heroSetting['catalog_button_text'] ?? 'Lihat Produk'),
                'secondary_cta_link' => $heroSetting['secondary_cta_link'] ?? '#kategori',
                'images' => $heroSetting['images'] ?? [
                    'storage/media/hero_meat_poultry_1786889302143.jpg',
                    'storage/media/hero_seafood_fish_1786889522926.jpg',
                    'storage/media/hero_ready_cook_1786889537358.jpg',
                    'storage/media/cat_daging_1786889601901.jpg',
                ],
                'trust_items' => $heroTrustItems,
                'updated_at' => 'Tersinkron Database',
            ],
            [
                'id' => 2,
                'name' => 'Hero Draft 02 (Preset)',
                'status' => 'Nonaktif',
                'badge' => 'Protein Segar & Siap Saji Higienis',
                'title' => 'Solusi Praktis Tinggal Masak untuk Keluarga',
                'headline_prefix' => 'Solusi Praktis',
                'highlight' => 'Tinggal Masak',
                'headline_suffix' => ' untuk Keluarga.',
                'description' => 'Pilihan seafood, ayam marinasi bumbu spesial, dan sayuran potong segar siap olah setiap hari.',
                'primary_cta_text' => 'Belanja Sekarang',
                'primary_cta_link' => '#produk',
                'secondary_cta_text' => 'Lihat Produk',
                'secondary_cta_link' => '#kategori',
                'images' => [
                    'storage/media/hero-2.jpg',
                    'storage/media/hero-3.jpg',
                    'storage/media/hero-1.jpg',
                ],
                'trust_items' => [
                    ['id' => 1, 'text' => 'Higienis & Segar', 'active' => true, 'is_active' => true, 'sort_order' => 1],
                    ['id' => 2, 'text' => 'Ready to Cook', 'active' => true, 'is_active' => true, 'sort_order' => 2],
                    ['id' => 3, 'text' => 'Free Delivery Sleman', 'active' => true, 'is_active' => true, 'sort_order' => 3],
                ],
                'updated_at' => 'Preset Layout',
            ],
            [
                'id' => 3,
                'name' => 'Hero Draft 03 (Preset)',
                'status' => 'Nonaktif',
                'badge' => 'Fresh & Frozen Food Kualitas Restoran',
                'title' => 'Daging Premium Harga Terjangkau Kirim Cepat',
                'headline_prefix' => 'Daging Premium',
                'highlight' => 'Harga Terjangkau',
                'headline_suffix' => ' Kirim Cepat.',
                'description' => 'Melayani kebutuhan rumah tangga, resto, catering, hingga pesanan partai besar ke seluruh wilayah Jogja.',
                'primary_cta_text' => 'Pesan via WhatsApp',
                'primary_cta_link' => 'https://wa.me/6281234567890',
                'secondary_cta_text' => 'Lihat Produk',
                'secondary_cta_link' => '#kategori',
                'images' => [
                    'storage/media/hero-3.jpg',
                    'storage/media/hero-1.jpg',
                ],
                'trust_items' => [
                    ['id' => 1, 'text' => 'Harga Grosir & Ecer', 'active' => true, 'is_active' => true, 'sort_order' => 1],
                    ['id' => 2, 'text' => 'Garansi Kualitas', 'active' => true, 'is_active' => true, 'sort_order' => 2],
                    ['id' => 3, 'text' => 'Sameday Delivery', 'active' => true, 'is_active' => true, 'sort_order' => 3],
                ],
                'updated_at' => 'Preset Layout',
            ],
        ];

        $savedDrafts = $this->siteSettingRepo->get('hero_drafts', null);
        $drafts = ($savedDrafts && is_array($savedDrafts) && count($savedDrafts) > 0)
            ? $savedDrafts
            : $defaultPresets;

        foreach ($drafts as &$d) {
            $dTrust = is_array($d['trust_items'] ?? null) ? $d['trust_items'] : [];
            $normTrust = [];
            for ($i = 0; $i < 3; $i++) {
                $t = $dTrust[$i] ?? $defaultTrust[$i];
                $normTrust[] = [
                    'id' => $t['id'] ?? ($i + 1),
                    'text' => $t['text'] ?? ($defaultTrust[$i]['text'] ?? ''),
                    'active' => isset($t['active']) ? (bool) $t['active'] : (isset($t['is_active']) ? (bool) $t['is_active'] : true),
                    'is_active' => isset($t['is_active']) ? (bool) $t['is_active'] : (isset($t['active']) ? (bool) $t['active'] : true),
                    'sort_order' => $t['sort_order'] ?? ($i + 1),
                ];
            }
            $d['trust_items'] = $normTrust;
        }
        unset($d);

        $mediaLibrary = $this->getMediaLibrary();
        $partnerMediaLibrary = $this->getPartnerMediaLibrary();

        $siteData = $this->siteSettingRepo->get('site', config('site', []));
        $contacts = $siteData['contacts'] ?? config('site.contacts', []);

        return view('admin.hero', compact('drafts', 'heroSetting', 'heroTrustItems', 'heroPartners', 'mediaLibrary', 'partnerMediaLibrary', 'contacts'));
    }

    /**
     * Update Hero, Trust Checklist & Partners Configuration (POST /admin/hero).
     */
    public function heroUpdate(Request $request)
    {
        $validated = $request->validate([
            'hero' => 'nullable|array',
            'hero.badge' => 'nullable|string|max:255',
            'hero.title' => 'nullable|string|max:255',
            'hero.headline_prefix' => 'nullable|string|max:255',
            'hero.highlight' => 'nullable|string|max:255',
            'hero.headline_suffix' => 'nullable|string|max:255',
            'hero.subtitle' => 'nullable|string|max:1000',
            'hero.description' => 'nullable|string|max:1000',
            'hero.whatsapp_button_text' => 'nullable|string|max:255',
            'hero.primary_cta_text' => 'nullable|string|max:255',
            'hero.primary_cta_link' => 'nullable|string|max:255',
            'hero.primary_cta_contact' => 'nullable|string|max:50',
            'hero.catalog_button_text' => 'nullable|string|max:255',
            'hero.secondary_cta_text' => 'nullable|string|max:255',
            'hero.secondary_cta_link' => 'nullable|string|max:255',
            'hero.secondary_cta_contact' => 'nullable|string|max:50',
            'hero.images' => 'nullable|array',
            'hero.images.*' => 'nullable|string|max:2048',

            'drafts' => 'nullable|array',
            'drafts.*.id' => 'nullable|integer|min:1',
            'drafts.*.name' => 'nullable|string|max:255',
            'drafts.*.badge' => 'nullable|string|max:255',
            'drafts.*.headline_prefix' => 'nullable|string|max:255',
            'drafts.*.highlight' => 'nullable|string|max:255',
            'drafts.*.headline_suffix' => 'nullable|string|max:255',
            'drafts.*.title' => 'nullable|string|max:255',
            'drafts.*.subtitle' => 'nullable|string|max:1000',
            'drafts.*.description' => 'nullable|string|max:1000',
            'drafts.*.primary_cta_text' => 'nullable|string|max:255',
            'drafts.*.primary_cta_link' => 'nullable|string|max:255',
            'drafts.*.primary_cta_contact' => 'nullable|string|max:50',
            'drafts.*.secondary_cta_text' => 'nullable|string|max:255',
            'drafts.*.secondary_cta_link' => 'nullable|string|max:255',
            'drafts.*.secondary_cta_contact' => 'nullable|string|max:50',
            'drafts.*.status' => 'nullable|string|max:50',
            'drafts.*.updated_at' => 'nullable|string|max:100',
            'drafts.*.images' => 'nullable|array',
            'drafts.*.images.*' => 'nullable|string|max:2048',
            'drafts.*.trust_items' => 'nullable|array',
            'drafts.*.trust_items.*.id' => 'nullable|integer|min:1',
            'drafts.*.trust_items.*.text' => 'nullable|string|max:255',
            'drafts.*.trust_items.*.active' => 'nullable|boolean',
            'drafts.*.trust_items.*.is_active' => 'nullable|boolean',
            'drafts.*.trust_items.*.sort_order' => 'nullable|integer|min:0|max:1000000',

            'trust_items' => 'nullable|array',
            'trust_items.*.id' => 'nullable|integer|min:1',
            'trust_items.*.text' => 'nullable|string|max:255',
            'trust_items.*.active' => 'nullable|boolean',
            'trust_items.*.is_active' => 'nullable|boolean',
            'trust_items.*.sort_order' => 'nullable|integer|min:0|max:1000000',

            'partners' => 'nullable|array',
            'partners.title' => 'nullable|string|max:255',
            'partners.badge' => 'nullable|string|max:255',
            'partners.partners' => 'nullable|array',
            'partners.partners.*.id' => 'nullable|integer|min:1',
            'partners.partners.*.name' => 'nullable|string|max:255',
            'partners.partners.*.logo' => 'nullable|string|max:2048',
            'partners.partners.*.active' => 'nullable|boolean',
            'partners.partners.*.is_active' => 'nullable|boolean',
            'partners.partners.*.sort_order' => 'nullable|integer|min:0|max:1000000',
        ]);

        $defaultTrustFallback = [
            ['id' => 1, 'text' => '100% Halal & Higienis', 'active' => true, 'is_active' => true, 'sort_order' => 1],
            ['id' => 2, 'text' => 'Standar Rantai Dingin (Cold Chain)', 'active' => true, 'is_active' => true, 'sort_order' => 2],
            ['id' => 3, 'text' => 'Pengiriman Cepat Se-Jogja', 'active' => true, 'is_active' => true, 'sort_order' => 3],
        ];

        if (isset($validated['drafts']) && is_array($validated['drafts'])) {
            $cleanDrafts = [];
            foreach ($validated['drafts'] as $idx => $draft) {
                if (!is_array($draft)) {
                    continue;
                }
                $rawTrustList = (isset($draft['trust_items']) && is_array($draft['trust_items'])) ? $draft['trust_items'] : [];
                $cleanTrustItems = [];
                for ($tIdx = 0; $tIdx < 3; $tIdx++) {
                    $t = $rawTrustList[$tIdx] ?? $defaultTrustFallback[$tIdx];
                    if (!is_array($t)) {
                        $t = $defaultTrustFallback[$tIdx];
                    }
                    $cleanTrustItems[] = [
                        'id' => isset($t['id']) ? (int) $t['id'] : ($tIdx + 1),
                        'text' => isset($t['text']) && trim((string) $t['text']) !== '' ? (string) $t['text'] : $defaultTrustFallback[$tIdx]['text'],
                        'active' => isset($t['active']) ? (bool) $t['active'] : (isset($t['is_active']) ? (bool) $t['is_active'] : true),
                        'is_active' => isset($t['is_active']) ? (bool) $t['is_active'] : (isset($t['active']) ? (bool) $t['active'] : true),
                        'sort_order' => isset($t['sort_order']) ? (int) $t['sort_order'] : ($tIdx + 1),
                    ];
                }
                $cleanDrafts[] = [
                    'id' => isset($draft['id']) ? (int) $draft['id'] : ($idx + 1),
                    'name' => isset($draft['name']) ? (string) $draft['name'] : null,
                    'badge' => isset($draft['badge']) ? (string) $draft['badge'] : null,
                    'headline_prefix' => isset($draft['headline_prefix']) ? (string) $draft['headline_prefix'] : null,
                    'highlight' => isset($draft['highlight']) ? (string) $draft['highlight'] : null,
                    'headline_suffix' => isset($draft['headline_suffix']) ? (string) $draft['headline_suffix'] : null,
                    'title' => isset($draft['title']) ? (string) $draft['title'] : null,
                    'subtitle' => isset($draft['subtitle']) ? (string) $draft['subtitle'] : (isset($draft['description']) ? (string) $draft['description'] : null),
                    'description' => isset($draft['description']) ? (string) $draft['description'] : (isset($draft['subtitle']) ? (string) $draft['subtitle'] : null),
                    'primary_cta_text' => isset($draft['primary_cta_text']) ? (string) $draft['primary_cta_text'] : null,
                    'primary_cta_link' => isset($draft['primary_cta_link']) ? $this->sanitizeNavigationUrl($draft['primary_cta_link'], '#produk') : null,
                    'primary_cta_contact' => isset($draft['primary_cta_contact']) ? (string) $draft['primary_cta_contact'] : null,
                    'secondary_cta_text' => isset($draft['secondary_cta_text']) ? (string) $draft['secondary_cta_text'] : null,
                    'secondary_cta_link' => isset($draft['secondary_cta_link']) ? $this->sanitizeNavigationUrl($draft['secondary_cta_link'], '#kategori') : null,
                    'secondary_cta_contact' => isset($draft['secondary_cta_contact']) ? (string) $draft['secondary_cta_contact'] : null,
                    'status' => isset($draft['status']) ? (string) $draft['status'] : null,
                    'updated_at' => isset($draft['updated_at']) ? (string) $draft['updated_at'] : null,
                    'images' => isset($draft['images']) && is_array($draft['images']) ? array_values(array_filter(array_map(fn($img) => $this->sanitizeImageUrl($img), $draft['images']))) : [],
                    'trust_items' => $cleanTrustItems,
                ];
            }
            $this->siteSettingRepo->set('hero_drafts', $cleanDrafts);
        }

        if (isset($validated['hero'])) {
            $currentHero = $this->siteSettingRepo->get('hero', []);
            $cleanHero = [
                'badge' => $validated['hero']['badge'] ?? ($currentHero['badge'] ?? null),
                'title' => $validated['hero']['title'] ?? ($currentHero['title'] ?? null),
                'headline_prefix' => $validated['hero']['headline_prefix'] ?? ($currentHero['headline_prefix'] ?? null),
                'highlight' => $validated['hero']['highlight'] ?? ($currentHero['highlight'] ?? null),
                'headline_suffix' => $validated['hero']['headline_suffix'] ?? ($currentHero['headline_suffix'] ?? null),
                'subtitle' => $validated['hero']['subtitle'] ?? ($validated['hero']['description'] ?? ($currentHero['subtitle'] ?? ($currentHero['description'] ?? null))),
                'description' => $validated['hero']['description'] ?? ($validated['hero']['subtitle'] ?? ($currentHero['description'] ?? ($currentHero['subtitle'] ?? null))),
                'whatsapp_button_text' => $validated['hero']['whatsapp_button_text'] ?? ($currentHero['whatsapp_button_text'] ?? null),
                'primary_cta_text' => $validated['hero']['primary_cta_text'] ?? ($currentHero['primary_cta_text'] ?? null),
                'primary_cta_link' => isset($validated['hero']['primary_cta_link']) ? $this->sanitizeNavigationUrl($validated['hero']['primary_cta_link'], '#produk') : ($currentHero['primary_cta_link'] ?? '#produk'),
                'primary_cta_contact' => $validated['hero']['primary_cta_contact'] ?? ($currentHero['primary_cta_contact'] ?? null),
                'catalog_button_text' => $validated['hero']['catalog_button_text'] ?? ($currentHero['catalog_button_text'] ?? null),
                'secondary_cta_text' => $validated['hero']['secondary_cta_text'] ?? ($currentHero['secondary_cta_text'] ?? null),
                'secondary_cta_link' => isset($validated['hero']['secondary_cta_link']) ? $this->sanitizeNavigationUrl($validated['hero']['secondary_cta_link'], '#kategori') : ($currentHero['secondary_cta_link'] ?? '#kategori'),
                'secondary_cta_contact' => $validated['hero']['secondary_cta_contact'] ?? ($currentHero['secondary_cta_contact'] ?? null),
                'images' => isset($validated['hero']['images']) && is_array($validated['hero']['images']) ? array_values(array_filter(array_map(fn($img) => $this->sanitizeImageUrl($img), $validated['hero']['images']))) : ($currentHero['images'] ?? []),
            ];
            $this->siteSettingRepo->set('hero', $cleanHero);
        }

        if (isset($validated['trust_items']) && is_array($validated['trust_items'])) {
            $rawHeroTrust = $validated['trust_items'];
            $cleanTrustItems = [];
            for ($idx = 0; $idx < 3; $idx++) {
                $t = $rawHeroTrust[$idx] ?? $defaultTrustFallback[$idx];
                if (!is_array($t)) {
                    $t = $defaultTrustFallback[$idx];
                }
                $cleanTrustItems[] = [
                    'id' => isset($t['id']) ? (int) $t['id'] : ($idx + 1),
                    'text' => isset($t['text']) && trim((string) $t['text']) !== '' ? (string) $t['text'] : $defaultTrustFallback[$idx]['text'],
                    'active' => isset($t['active']) ? (bool) $t['active'] : (isset($t['is_active']) ? (bool) $t['is_active'] : true),
                    'is_active' => isset($t['is_active']) ? (bool) $t['is_active'] : (isset($t['active']) ? (bool) $t['active'] : true),
                    'sort_order' => isset($t['sort_order']) ? (int) $t['sort_order'] : ($idx + 1),
                ];
            }
            $this->siteSettingRepo->set('hero_trust_items', $cleanTrustItems);
        }

        if (isset($validated['partners']) && is_array($validated['partners'])) {
            $currentPartners = $this->siteSettingRepo->get('hero_partners', []);
            $badge = $validated['partners']['badge'] ?? ($currentPartners['badge'] ?? 'Kepercayaan Mitra');
            $title = $validated['partners']['title'] ?? ($currentPartners['title'] ?? '');
            $partnersList = $validated['partners']['partners'] ?? ($currentPartners['partners'] ?? []);

            $cleanPartnersList = [];
            if (is_array($partnersList)) {
                foreach ($partnersList as $idx => $p) {
                    if (!is_array($p)) {
                        continue;
                    }
                    $raw = $p['is_active'] ?? ($p['active'] ?? true);
                    if (is_bool($raw)) {
                        $isActive = $raw;
                    } elseif (is_numeric($raw)) {
                        $isActive = (int) $raw === 1;
                    } elseif (is_string($raw)) {
                        $lower = strtolower(trim($raw));
                        if (in_array($lower, ['false', '0', 'nonaktif', 'nonaktif (sembunyi)', 'inactive', 'off', 'hide', 'hidden', ''])) {
                            $isActive = false;
                        } elseif (in_array($lower, ['true', '1', 'aktif', 'aktif (tampil)', 'active', 'on', 'show', 'visible'])) {
                            $isActive = true;
                        } else {
                            $isActive = false;
                        }
                    } else {
                        $isActive = (bool) $raw;
                    }

                    $cleanPartnersList[] = [
                        'id' => isset($p['id']) ? (int) $p['id'] : ($idx + 1),
                        'name' => isset($p['name']) ? (string) $p['name'] : '',
                        'logo' => isset($p['logo']) ? $this->sanitizeImageUrl($p['logo']) : null,
                        'is_active' => $isActive,
                        'sort_order' => isset($p['sort_order']) ? (int) $p['sort_order'] : ($idx + 1),
                    ];
                }
            }

            $cleanPartners = [
                'badge' => $badge,
                'title' => $title,
                'partners' => $cleanPartnersList,
            ];
            $this->siteSettingRepo->set('hero_partners', $cleanPartners);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Pengaturan Hero, Drafts, Trust Checklist, dan Mitra berhasil disimpan ke database!',
                'hero' => $this->siteSettingRepo->get('hero'),
                'hero_drafts' => $this->siteSettingRepo->get('hero_drafts'),
                'hero_trust_items' => $this->siteSettingRepo->get('hero_trust_items'),
                'hero_partners' => $this->siteSettingRepo->get('hero_partners'),
            ]);
        }

        return redirect()->route('admin.hero')->with('success', 'Pengaturan Hero berhasil disimpan.');
    }

    /**
     * Kategori Produk Management Screen.
     */
    public function kategori()
    {
        $categorySection = $this->getCategorySectionSettings();
        $dbCategories = $this->categoryRepo->getAll();
        $dbProducts = $this->productRepo->getAll();

        $defaultMetadata = [
            'daging-sapi' => [
                'subtitle' => 'Slice, Sengkel, Ribeye & Giling',
                'description' => 'Daging sapi segar & frozen potongan higienis tanpa pengawet.',
                'badge' => 'Sertifikasi Halal',
            ],
            'ayam-segar' => [
                'subtitle' => 'Karkas, Fillet Dada, Paha & Bumbu',
                'description' => 'Ayam segar potong harian & olahan marinasi siap masak.',
                'badge' => 'Potong Harian',
            ],
            'ikan-seafood' => [
                'subtitle' => 'Dory, Salmon, Udang & Cumi',
                'description' => 'Seafood kualitas resto, bersih tanpa bau lumpur.',
                'badge' => 'Higienis & Segar',
            ],
            'sayuran-siap-olah' => [
                'subtitle' => 'Sayur Sop, Sayur Asem, Capcay',
                'description' => 'Sayuran segar pilihan sudah dipotong dan dicuci bersih.',
                'badge' => 'Siap Cemplung',
            ],
            'frozen-food' => [
                'subtitle' => 'Nugget, Sosis, Bakso & Dimsum',
                'description' => 'Aneka frozen food higienis untuk stok praktis di rumah.',
                'badge' => 'Praktis Siap Saji',
            ],
        ];

        $categories = $dbCategories->map(function ($cat) use ($dbProducts, $defaultMetadata) {
            $activeCount = $dbProducts->filter(function ($p) use ($cat) {
                return in_array($cat->id, $p->category_ids, true) && $p->is_active;
            })->count();

            $meta = $defaultMetadata[$cat->slug] ?? [];
            $rawActive = (int) $cat->is_active;
            $status = ($rawActive === 1) ? 'active_landing' : (($rawActive === 2) ? 'active_catalog' : 'inactive');

            return [
                'id' => $cat->id,
                'name' => $cat->name,
                'slug' => $cat->slug,
                'subtitle' => $cat->subtitle ?? ($meta['subtitle'] ?? 'Pilihan Bahan Segar & Berkualitas'),
                'badge' => $cat->badge ?? ($meta['badge'] ?? 'Sertifikasi Halal'),
                'color' => $cat->color ?? 'orange',
                'image' => $cat->image ?? 'storage/media/cat_daging_1786889601901.jpg',
                'description' => $cat->description ?? ($meta['description'] ?? 'Bahan masak segar dan higienis pilihan keluarga.'),
                'order' => (int) $cat->sort_order,
                'sort_order' => (int) $cat->sort_order,
                'status' => $status,
                'is_active' => $rawActive,
                'is_system' => false,
                'products_count' => $activeCount,
                'count' => $activeCount . '+ Variasi',
            ];
        })->toArray();

        $products = $dbProducts->map(function ($p) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'category_id' => $p->category_id,
                'category_ids' => $p->category_ids,
                'status' => $p->is_active ? 'Aktif' : 'Nonaktif',
            ];
        })->toArray();

        $mediaLibrary = $this->getMediaLibrary();

        return view('admin.kategori', compact('categorySection', 'categories', 'products', 'mediaLibrary'));
    }

    /**
     * Create a new category (POST /admin/kategori).
     */
    public function categoryStore(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:categories,slug',
            'color' => 'nullable|string|max:50',
            'image' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable',
            'status' => 'nullable|string|in:active_landing,active_catalog,inactive',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $validated['color'] = $validated['color'] ?? 'orange';
        $validated['image'] = $validated['image'] ?? 'storage/media/cat_daging_1786889601901.jpg';
        $validated['sort_order'] = isset($validated['sort_order']) ? (int) $validated['sort_order'] : ($this->categoryRepo->getAll()->count() + 1);

        if (isset($validated['status'])) {
            $validated['is_active'] = ($validated['status'] === 'active_landing') ? 1 : (($validated['status'] === 'active_catalog') ? 2 : 0);
        } elseif (isset($validated['is_active'])) {
            if (is_numeric($validated['is_active'])) {
                $validated['is_active'] = (int) $validated['is_active'];
            } else {
                $validated['is_active'] = $validated['is_active'] ? 1 : 0;
            }
        } else {
            $validated['is_active'] = 1;
        }

        $category = $this->categoryRepo->create($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Kategori {$category->name} berhasil ditambahkan.",
                'category' => $category,
            ], 201);
        }

        return redirect()->route('admin.kategori')->with('success', "Kategori {$category->name} berhasil ditambahkan.");
    }

    /**
     * Update an existing category (PUT /admin/kategori/{id}).
     */
    public function categoryUpdate(Request $request, int $id)
    {
        $category = $this->categoryRepo->findById($id);
        if (!$category) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kategori tidak ditemukan.',
                ], 404);
            }
            return redirect()->route('admin.kategori')->with('error', 'Kategori tidak ditemukan.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:categories,slug,' . $id,
            'color' => 'nullable|string|max:50',
            'image' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable',
            'status' => 'nullable|string|in:active_landing,active_catalog,inactive',
        ]);

        if (isset($validated['status'])) {
            $validated['is_active'] = ($validated['status'] === 'active_landing') ? 1 : (($validated['status'] === 'active_catalog') ? 2 : 0);
        } elseif (isset($validated['is_active'])) {
            if (is_numeric($validated['is_active'])) {
                $validated['is_active'] = (int) $validated['is_active'];
            } else {
                $validated['is_active'] = $validated['is_active'] ? 1 : 0;
            }
        }

        $updated = $this->categoryRepo->update($id, $validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => $updated,
                'message' => "Kategori {$category->name} berhasil diperbarui.",
                'category' => $this->categoryRepo->findById($id),
            ]);
        }

        return redirect()->route('admin.kategori')->with('success', "Kategori {$category->name} berhasil diperbarui.");
    }

    /**
     * Delete a category with delete guard (DELETE /admin/kategori/{id}).
     */
    public function categoryDestroy(Request $request, int $id)
    {
        $category = $this->categoryRepo->findById($id);
        if (!$category) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kategori tidak ditemukan.',
                ], 404);
            }
            return redirect()->route('admin.kategori')->with('error', 'Kategori tidak ditemukan.');
        }

        // Critical Delete Guard: Block delete if category still has associated products
        if ($this->categoryRepo->hasProducts($id)) {
            $productCount = $this->categoryRepo->countProducts($id);
            $errorMsg = "Kategori \"{$category->name}\" tidak dapat dihapus karena masih memiliki {$productCount} produk terkait. Pindahkan atau hapus produk terlebih dahulu.";

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'blocked' => true,
                    'message' => $errorMsg,
                ], 422);
            }

            return redirect()->route('admin.kategori')->with('error', $errorMsg);
        }

        $this->categoryRepo->delete($id);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Kategori {$category->name} berhasil dihapus.",
            ]);
        }

        return redirect()->route('admin.kategori')->with('success', "Kategori {$category->name} berhasil dihapus.");
    }

    /**
     * Toggle active state of a category (PATCH /admin/kategori/{id}/toggle).
     */
    public function categoryToggle(Request $request, int $id)
    {
        $targetStatus = $request->input('status');
        $category = $this->categoryRepo->toggleActive($id, $targetStatus);
        if (!$category) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kategori tidak ditemukan.',
                ], 404);
            }
            return redirect()->route('admin.kategori')->with('error', 'Kategori tidak ditemukan.');
        }

        $rawActive = (int) $category->is_active;
        $status = ($rawActive === 1) ? 'active_landing' : (($rawActive === 2) ? 'active_catalog' : 'inactive');
        $statusLabels = [
            'active_landing' => 'Aktif (Landing Page & Katalog)',
            'active_catalog' => 'Aktif (Hanya Katalog)',
            'inactive' => 'Nonaktif (Disembunyikan)',
        ];
        $statusLabel = $statusLabels[$status] ?? 'Nonaktif';

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'is_active' => $rawActive,
                'status' => $status,
                'message' => "Status kategori {$category->name} diubah menjadi {$statusLabel}.",
                'category' => $category,
            ]);
        }

        return redirect()->route('admin.kategori')->with('success', "Status kategori {$category->name} diubah menjadi {$statusLabel}.");
    }

    /**
     * Reorder categories (POST /admin/kategori/reorder).
     */
    public function categoryReorder(Request $request)
    {
        $validated = $this->validateReorderPayload($request);

        $this->categoryRepo->reorder($validated['orders']);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Urutan kategori berhasil diperbarui.',
            ]);
        }

        return redirect()->route('admin.kategori')->with('success', 'Urutan kategori berhasil diperbarui.');
    }

    /**
     * Update Category Section Header Configuration (POST /admin/kategori/section-settings).
     */
    public function categorySectionUpdate(Request $request)
    {
        $validated = $request->validate([
            'label' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string',
        ]);

        $settings = [
            'label' => $validated['label'] ?? 'Kategori Utama',
            'title' => $validated['title'] ?? 'Mau Masak Apa Hari Ini?',
            'subtitle' => $validated['subtitle'] ?? 'Pilih bahan masak sesuai kebutuhanmu. Dari potongan daging segar, ayam bumbu, ikan laut, hingga sayuran siap cemplung.',
        ];

        $this->siteSettingRepo->set('category_section', $settings);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Pengaturan Header Section Kategori berhasil disimpan!',
                'categorySection' => $settings,
            ]);
        }

        return redirect()->route('admin.kategori')->with('success', 'Pengaturan Header Section Kategori berhasil disimpan!');
    }

    /**
     * Update Catalog Section Header Settings (POST /admin/produk/section-settings).
     */
    public function productSectionUpdate(Request $request)
    {
        $validated = $request->validate([
            'label' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string',
        ]);

        $settings = [
            'label' => $validated['label'] ?? 'Katalog Lengkap',
            'title' => $validated['title'] ?? 'Produk Pilihan',
            'subtitle' => $validated['subtitle'] ?? 'Pilih bahan masak sesuai kebutuhanmu. Tersedia skala retail rumah tangga maupun pembelian curah.',
        ];

        $this->siteSettingRepo->set('catalog_section', $settings);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Pengaturan Header Section Katalog Produk berhasil disimpan!',
                'catalogSection' => $settings,
            ]);
        }

        return redirect()->route('admin.produk')->with('success', 'Pengaturan Header Section Katalog Produk berhasil disimpan!');
    }

    /**
     * Katalog Produk Management Screen.
     */
    public function produk()
    {
        $dbCategories = $this->categoryRepo->getAll();
        $dbProducts = $this->productRepo->getAll();
        $contactSettings = $this->getContactSettings();
        $mediaLibrary = $this->getMediaLibrary();

        $catalogSection = $this->siteSettingRepo->get('catalog_section', [
            'label' => 'Katalog Lengkap',
            'title' => 'Produk Pilihan',
            'subtitle' => 'Pilih bahan masak sesuai kebutuhanmu. Tersedia skala retail rumah tangga maupun pembelian curah.'
        ]);

        $categories = $dbCategories->map(function ($c) {
            $rawActive = (int) $c->is_active;
            $status = ($rawActive === 1) ? 'active_landing' : (($rawActive === 2) ? 'active_catalog' : 'inactive');
            return [
                'id' => $c->id,
                'name' => $c->name,
                'slug' => $c->slug,
                'color' => $c->color ?? 'orange',
                'status' => $status,
                'is_active' => $rawActive,
            ];
        })->toArray();

        $products = $dbProducts->map(function ($p) {
            $catIds = $p->categories->pluck('id')->toArray();
            if (empty($catIds) && !empty($p->category_id)) {
                $catIds = [(int) $p->category_id];
            }
            $catNames = $p->categories->pluck('name')->toArray();
            if (empty($catNames) && $p->category) {
                $catNames = [$p->category->name];
            }

            return [
                'id' => $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'category_id' => $p->category_id,
                'category_ids' => $catIds,
                'category_names' => $catNames,
                'category' => !empty($catNames) ? implode(', ', $catNames) : ($p->category ? $p->category->name : 'Daging Sapi'),
                'types' => is_array($p->types) ? $p->types : [],
                'weight' => $p->weight_value . ($p->unit === 'gram' ? 'g' : ($p->unit === 'kg' ? 'kg' : ' ' . $p->unit)),
                'weight_value' => (int) $p->weight_value,
                'unit' => $p->unit ?? 'gram',
                'price' => (float) $p->normal_price,
                'normal_price' => (float) $p->normal_price,
                'discount_type' => $p->discount_type,
                'discount_value' => $p->discount_value ? (float) $p->discount_value : null,
                'status' => $p->is_active ? 'Aktif' : 'Nonaktif',
                'is_active' => (bool) $p->is_active,
                'image' => $p->image ?? 'storage/media/prod_beef_slice_1786890263309.jpg',
                'description' => $p->description ?? '',
                'stock_status' => $p->stock_status ?? 'READY_STOCK',
                'is_flash_sale' => (bool) $p->is_flash_sale,
                'flash_sale_discount_type' => $p->flash_sale_discount_type,
                'flash_sale_discount_value' => $p->flash_sale_discount_value ? (float) $p->flash_sale_discount_value : null,
                'flash_sale_sort_order' => (int) ($p->flash_sale_sort_order ?? 1),
                'sort_order' => (int) ($p->sort_order ?? 1),
            ];
        })->toArray();

        $flashSaleSetting = $this->siteSettingRepo->get('flash_sale', [
            'enabled' => false,
            'end_at' => null,
            'title' => 'Flash Sale Terbatas!',
            'subtitle' => 'Dapatkan potongan harga spesial untuk produk protein pilihan hari ini. Stok terbatas!',
        ]);

        return view('admin.produk', compact('categories', 'products', 'catalogSection', 'flashSaleSetting', 'contactSettings', 'mediaLibrary'));
    }

    /**
     * Create a new product (POST /admin/produk).
     */
    public function productStore(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products,slug',
            'category_ids' => 'required|array|min:1',
            'category_ids.*' => 'required|integer|exists:categories,id',
            'description' => 'nullable|string',
            'image' => 'nullable|string|max:255',
            'types' => 'nullable|array',
            'types.*' => 'string|max:100',
            'weight' => 'nullable|string|max:100',
            'weight_value' => 'nullable|numeric|min:0',
            'unit' => 'nullable|string|max:50',
            'normal_price' => 'required|numeric|min:0',
            'discount_type' => 'nullable|in:percentage,fixed',
            'discount_value' => 'nullable|numeric|min:0',
            'stock_status' => 'required|in:READY_STOCK,OUT_OF_STOCK,PRE_ORDER',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        // Regular Discount Boundary Validations
        if (!empty($validated['discount_type'])) {
            if ($validated['discount_type'] === 'percentage' && ($validated['discount_value'] ?? 0) > 100) {
                return response()->json([
                    'success' => false,
                    'message' => 'Diskon persentase tidak boleh melebihi 100%.',
                ], 422);
            }
            if ($validated['discount_type'] === 'fixed' && ($validated['discount_value'] ?? 0) > $validated['normal_price']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Diskon nominal (fixed) tidak boleh melebihi harga normal produk.',
                ], 422);
            }
        } else {
            $validated['discount_type'] = null;
            $validated['discount_value'] = null;
        }

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $validated['category_id'] = $validated['category_ids'][0];
        $validated['types'] = $validated['types'] ?? ['Fresh'];
        $validated['unit'] = $validated['unit'] ?? 'gram';
        $validated['weight_value'] = isset($validated['weight_value']) ? (float) $validated['weight_value'] : 500;
        $validated['weight'] = $validated['weight'] ?? ($validated['weight_value'] . ($validated['unit'] === 'gram' ? 'g' : ' ' . $validated['unit']));
        $validated['image'] = $validated['image'] ?? 'storage/media/prod_beef_slice_1786890263309.jpg';
        $validated['is_active'] = isset($validated['is_active']) ? (bool) $validated['is_active'] : true;

        // Flash Sale Isolation: New regular products start outside flash sale
        $validated['is_flash_sale'] = false;
        $validated['flash_sale_discount_type'] = null;
        $validated['flash_sale_discount_value'] = null;
        $validated['flash_sale_sort_order'] = 0;

        // New Product Rule: Auto-insert at #1 and shift existing orders down by 1
        $product = DB::transaction(function () use ($validated) {
            \App\Models\Product::query()->increment('sort_order', 1);
            $validated['sort_order'] = 1;
            return $this->productRepo->create($validated);
        });

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Produk {$product->name} berhasil ditambahkan.",
                'product' => $product->load(['categories', 'category']),
            ], 201);
        }

        return redirect()->route('admin.produk')->with('success', "Produk {$product->name} berhasil ditambahkan.");
    }

    /**
     * Update an existing product (PUT /admin/produk/{id}).
     */
    public function productUpdate(Request $request, int $id)
    {
        $product = $this->productRepo->findById($id);
        if (!$product) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produk tidak ditemukan.',
                ], 404);
            }
            return redirect()->route('admin.produk')->with('error', 'Produk tidak ditemukan.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'category_ids' => 'required|array|min:1',
            'category_ids.*' => 'required|integer|exists:categories,id',
            'description' => 'nullable|string',
            'image' => 'nullable|string|max:255',
            'types' => 'nullable|array',
            'types.*' => 'string|max:100',
            'weight' => 'nullable|string|max:100',
            'weight_value' => 'nullable|numeric|min:0',
            'unit' => 'nullable|string|max:50',
            'normal_price' => 'required|numeric|min:0',
            'discount_type' => 'nullable|in:percentage,fixed',
            'discount_value' => 'nullable|numeric|min:0',
            'stock_status' => 'required|in:READY_STOCK,OUT_OF_STOCK,PRE_ORDER',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        // Regular Discount Boundary Validations
        if (!empty($validated['discount_type'])) {
            if ($validated['discount_type'] === 'percentage' && ($validated['discount_value'] ?? 0) > 100) {
                return response()->json([
                    'success' => false,
                    'message' => 'Diskon persentase tidak boleh melebihi 100%.',
                ], 422);
            }
            if ($validated['discount_type'] === 'fixed' && ($validated['discount_value'] ?? 0) > $validated['normal_price']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Diskon nominal (fixed) tidak boleh melebihi harga normal produk.',
                ], 422);
            }
        } else {
            $validated['discount_type'] = null;
            $validated['discount_value'] = null;
        }

        if (isset($validated['is_active'])) {
            $validated['is_active'] = (bool) $validated['is_active'];
        }
        if (isset($validated['weight_value'])) {
            $validated['weight_value'] = (float) $validated['weight_value'];
            $unit = $validated['unit'] ?? ($product->unit ?? 'gram');
            $validated['weight'] = $validated['weight'] ?? ($validated['weight_value'] . ($unit === 'gram' ? 'g' : ' ' . $unit));
        }

        $validated['category_id'] = $validated['category_ids'][0];

        // Strict Flash Sale Isolation: Preserve existing flash sale fields
        unset($validated['is_flash_sale'], $validated['flash_sale_discount_type'], $validated['flash_sale_discount_value'], $validated['flash_sale_sort_order']);

        $updated = DB::transaction(function () use ($id, $validated) {
            return $this->productRepo->update($id, $validated);
        });

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => $updated,
                'message' => "Produk {$product->name} berhasil diperbarui.",
                'product' => $this->productRepo->findById($id),
            ]);
        }

        return redirect()->route('admin.produk')->with('success', "Produk {$product->name} berhasil diperbarui.");
    }

    /**
     * Delete a product with Flash Sale guard (DELETE /admin/produk/{id}).
     */
    public function productDestroy(Request $request, int $id)
    {
        $product = $this->productRepo->findById($id);
        if (!$product) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produk tidak ditemukan.',
                ], 404);
            }
            return redirect()->route('admin.produk')->with('error', 'Produk tidak ditemukan.');
        }

        // Flash Sale Delete Guard: Block delete if product is currently participating in Flash Sale
        if ($product->is_flash_sale) {
            $errorMsg = "Produk \"{$product->name}\" sedang aktif dalam program Flash Sale. Hapus produk dari daftar Flash Sale terlebih dahulu sebelum menghapus.";

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'blocked' => true,
                    'message' => $errorMsg,
                ], 422);
            }

            return redirect()->route('admin.produk')->with('error', $errorMsg);
        }

        $this->productRepo->delete($id);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Produk {$product->name} berhasil dihapus.",
            ]);
        }

        return redirect()->route('admin.produk')->with('success', "Produk {$product->name} berhasil dihapus.");
    }

    /**
     * Toggle active state of a product (PATCH /admin/produk/{id}/toggle).
     */
    public function productToggle(Request $request, int $id)
    {
        $product = $this->productRepo->toggleActive($id);
        if (!$product) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produk tidak ditemukan.',
                ], 404);
            }
            return redirect()->route('admin.produk')->with('error', 'Produk tidak ditemukan.');
        }

        $statusLabel = $product->is_active ? 'Aktif' : 'Nonaktif';

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'is_active' => $product->is_active,
                'message' => "Status produk {$product->name} diubah menjadi {$statusLabel}.",
                'product' => $product,
            ]);
        }

        return redirect()->route('admin.produk')->with('success', "Status produk {$product->name} diubah menjadi {$statusLabel}.");
    }

    /**
     * Reorder products (POST /admin/produk/reorder).
     */
    public function productReorder(Request $request)
    {
        $validated = $this->validateReorderPayload($request);
        $firstOrder = is_array($validated['orders']) && !empty($validated['orders']) ? array_values($validated['orders'])[0] : null;
        if (is_array($firstOrder)) {
            $mappedOrders = [];
            foreach ($validated['orders'] as $item) {
                $mappedOrders[$item['id']] = $item['sort_order'] ?? ($item['order'] ?? 0);
            }
            $validated['orders'] = $mappedOrders;
        }

        $this->productRepo->reorder($validated['orders']);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Urutan produk berhasil diperbarui.',
            ]);
        }

        return redirect()->route('admin.produk')->with('success', 'Urutan produk berhasil diperbarui.');
    }

    /**
     * Toggle Flash Sale ON/OFF with strict Enable Guard (POST /admin/flash-sale/toggle).
     */
    public function flashSaleToggle(Request $request)
    {
        $validated = $request->validate([
            'enabled' => 'required|boolean',
            'end_at' => 'nullable|string',
        ]);

        $currentSetting = $this->siteSettingRepo->get('flash_sale', [
            'enabled' => false,
            'end_at' => null,
            'title' => 'Flash Sale Terbatas!',
            'subtitle' => 'Dapatkan potongan harga spesial untuk produk protein pilihan hari ini. Stok terbatas!',
        ]);

        $enabled = (bool) $validated['enabled'];

        if ($enabled) {
            // Enable Guard: Validate active assigned Flash Sale products >= 1
            $activeFsCount = $this->productRepo->getFlashSaleProducts()->count();
            $endAt = $validated['end_at'] ?? ($currentSetting['end_at'] ?? null);

            $isValidFuture = false;
            if (!empty($endAt)) {
                try {
                    $parsedEndAt = \Carbon\Carbon::parse($endAt);
                    $isValidFuture = $parsedEndAt->isFuture();
                } catch (\Exception $e) {
                    $isValidFuture = false;
                }
            }

            if ($activeFsCount < 1 || !$isValidFuture) {
                return response()->json([
                    'success' => false,
                    'blocked' => true,
                    'message' => 'Tambahkan minimal 1 produk Flash Sale dan tentukan waktu berakhir di masa depan.',
                ], 422);
            }

            $currentSetting['enabled'] = true;
            $currentSetting['end_at'] = $endAt;
        } else {
            // Disable Flash Sale: Preserves product assignments and discounts intact
            $currentSetting['enabled'] = false;
        }

        $this->siteSettingRepo->set('flash_sale', $currentSetting);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $enabled ? 'Flash Sale berhasil diaktifkan!' : 'Flash Sale berhasil dinonaktifkan.',
                'flash_sale' => $currentSetting,
            ]);
        }

        return redirect()->route('admin.produk')->with('success', $enabled ? 'Flash Sale berhasil diaktifkan!' : 'Flash Sale berhasil dinonaktifkan.');
    }

    /**
     * Update Flash Sale general settings (POST /admin/flash-sale/settings).
     */
    public function flashSaleSettings(Request $request)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:500',
            'end_at' => 'nullable|string',
        ]);

        $currentSetting = $this->siteSettingRepo->get('flash_sale', [
            'enabled' => false,
            'end_at' => null,
            'title' => 'Flash Sale Terbatas!',
            'subtitle' => 'Dapatkan potongan harga spesial untuk produk protein pilihan hari ini. Stok terbatas!',
        ]);

        if (isset($validated['title'])) $currentSetting['title'] = $validated['title'];
        if (isset($validated['subtitle'])) $currentSetting['subtitle'] = $validated['subtitle'];
        if (isset($validated['end_at'])) $currentSetting['end_at'] = $validated['end_at'];

        $this->siteSettingRepo->set('flash_sale', $currentSetting);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Pengaturan Flash Sale berhasil diperbarui.',
                'flash_sale' => $currentSetting,
            ]);
        }

        return redirect()->route('admin.produk')->with('success', 'Pengaturan Flash Sale berhasil diperbarui.');
    }

    /**
     * Assign product to Flash Sale (POST /admin/flash-sale/assign).
     */
    public function flashSaleAssign(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $product = $this->productRepo->findById($validated['product_id']);
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan.',
            ], 404);
        }

        // Validate discount boundaries
        if ($validated['discount_type'] === 'percentage' && $validated['discount_value'] > 100) {
            return response()->json([
                'success' => false,
                'message' => 'Diskon Flash Sale persentase tidak boleh melebihi 100%.',
            ], 422);
        }

        if ($validated['discount_type'] === 'fixed' && $validated['discount_value'] > (float) $product->normal_price) {
            return response()->json([
                'success' => false,
                'message' => 'Diskon Flash Sale nominal tidak boleh melebihi harga normal produk.',
            ], 422);
        }

        $assigned = $this->productRepo->assignFlashSale($validated['product_id'], [
            'discount_type' => $validated['discount_type'],
            'discount_value' => $validated['discount_value'],
            'sort_order' => $validated['sort_order'] ?? 1,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => $assigned,
                'message' => "Produk \"{$product->name}\" berhasil ditambahkan ke Flash Sale.",
                'product' => $this->productRepo->findById($validated['product_id']),
            ]);
        }

        return redirect()->route('admin.produk')->with('success', "Produk \"{$product->name}\" berhasil ditambahkan ke Flash Sale.");
    }

    /**
     * Remove product from Flash Sale (DELETE /admin/flash-sale/remove/{id}).
     */
    public function flashSaleRemove(Request $request, int $id)
    {
        $product = $this->productRepo->findById($id);
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan.',
            ], 404);
        }

        $removed = $this->productRepo->removeFlashSale($id);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => $removed,
                'message' => "Produk \"{$product->name}\" berhasil dihapus dari Flash Sale.",
                'product' => $this->productRepo->findById($id),
            ]);
        }

        return redirect()->route('admin.produk')->with('success', "Produk \"{$product->name}\" berhasil dihapus dari Flash Sale.");
    }

    /**
     * Reorder Flash Sale products (POST /admin/flash-sale/reorder).
     */
    public function flashSaleReorder(Request $request)
    {
        $validated = $this->validateReorderPayload($request, 'flash_sale_sort_order');
        $firstOrder = is_array($validated['orders']) && !empty($validated['orders']) ? array_values($validated['orders'])[0] : null;
        if (is_array($firstOrder)) {
            $mappedOrders = [];
            foreach ($validated['orders'] as $item) {
                $mappedOrders[$item['id']] = $item['sort_order'] ?? ($item['flash_sale_sort_order'] ?? ($item['order'] ?? 0));
            }
            $validated['orders'] = $mappedOrders;
        }

        $this->productRepo->reorderFlashSale($validated['orders']);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Urutan Flash Sale berhasil diperbarui.',
            ]);
        }

        return redirect()->route('admin.produk')->with('success', 'Urutan Flash Sale berhasil diperbarui.');
    }

    /**
     * Keunggulan & Standar Mutu Management Screen.
     */
    public function keunggulan()
    {
        $benefitsData = $this->siteSettingRepo->get('benefits', [
            'section_badge' => 'Kenapa Memilih Kami',
            'section_title' => 'Lebih Praktis, Lebih Siap',
            'section_subtitle' => 'Komitmen kami menghadirkan bahan makanan segar dan frozen bermutu tinggi.',
            'items' => [],
        ]);

        $qualityStandardsData = $this->siteSettingRepo->get('quality_standards', [
            'section_badge' => 'Standar Mutu',
            'section_title' => 'Mengenal Standar Produk Kami',
            'section_subtitle' => 'Setiap produk yang keluar melewati proses seleksi ketat.',
            'items' => [],
        ]);

        $mediaLibrary = $this->getMediaLibrary();

        return view('admin.keunggulan', compact('benefitsData', 'qualityStandardsData', 'mediaLibrary'));
    }

    /**
     * Update Keunggulan & Standar Mutu settings (POST /admin/keunggulan).
     */
    public function keunggulanUpdate(Request $request)
    {
        $validated = $request->validate([
            'benefits' => 'nullable|array',
            'benefits.section_badge' => 'nullable|string|max:255',
            'benefits.section_title' => 'nullable|string|max:255',
            'benefits.section_subtitle' => 'nullable|string|max:1000',
            'benefits.items' => 'nullable|array',
            'benefits.items.*.id' => 'nullable|integer|min:1',
            'benefits.items.*.title' => 'nullable|string|max:255',
            'benefits.items.*.desc' => 'nullable|string|max:1000',
            'benefits.items.*.description' => 'nullable|string|max:1000',
            'benefits.items.*.subtitle' => 'nullable|string|max:1000',
            'benefits.items.*.icon' => ['nullable', 'string', Rule::in(['grid', 'shield', 'clock', 'truck'])],
            'benefits.items.*.badge' => 'nullable|string|max:255',

            'quality' => 'nullable|array',
            'quality.section_badge' => 'nullable|string|max:255',
            'quality.section_title' => 'nullable|string|max:255',
            'quality.section_subtitle' => 'nullable|string|max:1000',
            'quality.items' => 'nullable|array',
            'quality.items.*.id' => 'nullable|integer|min:1',
            'quality.items.*.name' => 'nullable|string|max:255',
            'quality.items.*.title' => 'nullable|string|max:255',
            'quality.items.*.tag' => 'nullable|string|max:255',
            'quality.items.*.badge' => 'nullable|string|max:255',
            'quality.items.*.desc' => 'nullable|string|max:1000',
            'quality.items.*.description' => 'nullable|string|max:1000',
            'quality.items.*.icon' => ['nullable', 'string', Rule::in(['grid', 'shield', 'clock', 'truck'])],
            'quality.items.*.features' => 'nullable|array',
            'quality.items.*.features.*' => 'nullable|string|max:255',
        ]);

        if (array_key_exists('benefits', $validated) && is_array($validated['benefits'])) {
            $rawBenefits = $validated['benefits'];
            $cleanBenefits = [
                'section_badge' => isset($rawBenefits['section_badge']) && $rawBenefits['section_badge'] !== null ? trim((string) $rawBenefits['section_badge']) : null,
                'section_title' => isset($rawBenefits['section_title']) && $rawBenefits['section_title'] !== null ? trim((string) $rawBenefits['section_title']) : null,
                'section_subtitle' => isset($rawBenefits['section_subtitle']) && $rawBenefits['section_subtitle'] !== null ? trim((string) $rawBenefits['section_subtitle']) : null,
                'items' => [],
            ];

            if (isset($rawBenefits['items']) && is_array($rawBenefits['items'])) {
                foreach ($rawBenefits['items'] as $idx => $item) {
                    if (!is_array($item)) {
                        continue;
                    }
                    $cleanBenefits['items'][] = [
                        'id' => isset($item['id']) ? (int) $item['id'] : ($idx + 1),
                        'title' => isset($item['title']) ? trim((string) $item['title']) : '',
                        'desc' => isset($item['desc']) ? trim((string) $item['desc']) : (isset($item['description']) ? trim((string) $item['description']) : (isset($item['subtitle']) ? trim((string) $item['subtitle']) : '')),
                        'icon' => in_array($item['icon'] ?? '', ['grid', 'shield', 'clock', 'truck'], true) ? $item['icon'] : 'grid',
                        'badge' => isset($item['badge']) && $item['badge'] !== null ? trim((string) $item['badge']) : null,
                    ];
                }
            }

            $this->siteSettingRepo->set('benefits', $cleanBenefits);
        }

        if (array_key_exists('quality', $validated) && is_array($validated['quality'])) {
            $rawQuality = $validated['quality'];
            $cleanQuality = [
                'section_badge' => isset($rawQuality['section_badge']) && $rawQuality['section_badge'] !== null ? trim((string) $rawQuality['section_badge']) : null,
                'section_title' => isset($rawQuality['section_title']) && $rawQuality['section_title'] !== null ? trim((string) $rawQuality['section_title']) : null,
                'section_subtitle' => isset($rawQuality['section_subtitle']) && $rawQuality['section_subtitle'] !== null ? trim((string) $rawQuality['section_subtitle']) : null,
                'items' => [],
            ];

            if (isset($rawQuality['items']) && is_array($rawQuality['items'])) {
                foreach ($rawQuality['items'] as $idx => $item) {
                    if (!is_array($item)) {
                        continue;
                    }
                    $features = [];
                    if (isset($item['features']) && is_array($item['features'])) {
                        foreach ($item['features'] as $f) {
                            if (is_string($f) && trim($f) !== '') {
                                $features[] = trim($f);
                            }
                        }
                    }

                    $cleanQuality['items'][] = [
                        'id' => isset($item['id']) ? (int) $item['id'] : ($idx + 1),
                        'name' => isset($item['name']) ? trim((string) $item['name']) : (isset($item['title']) ? trim((string) $item['title']) : ''),
                        'tag' => isset($item['tag']) ? trim((string) $item['tag']) : (isset($item['badge']) ? trim((string) $item['badge']) : ''),
                        'desc' => isset($item['desc']) ? trim((string) $item['desc']) : (isset($item['description']) ? trim((string) $item['description']) : ''),
                        'features' => $features,
                        'icon' => in_array($item['icon'] ?? '', ['grid', 'shield', 'clock', 'truck'], true) ? $item['icon'] : null,
                    ];
                }
            }

            $this->siteSettingRepo->set('quality_standards', $cleanQuality);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Pengaturan Keunggulan & Standar Mutu berhasil disimpan.',
                'benefits' => $this->siteSettingRepo->get('benefits'),
                'quality' => $this->siteSettingRepo->get('quality_standards'),
            ]);
        }

        return redirect()->route('admin.keunggulan')->with('success', 'Pengaturan Keunggulan & Standar Mutu berhasil disimpan.');
    }

    /**
     * Knowledge & Edukasi Management Screen.
     */
    public function knowledge()
    {
        $knowledgeSection = $this->siteSettingRepo->get('knowledge_section', [
            'label' => 'Edukasi & Inspirasi Dapur',
            'title' => 'Dapur & Knowledge',
            'subtitle' => 'Panduan praktis seputar penanganan daging, thawing, penyimpanan frozen food, hingga tips memasak harian keluarga di Yogyakarta.'
        ]);

        $dbCategories = $this->knowledgeRepo->getAllCategories();
        $dbArticles = $this->knowledgeRepo->getAllArticles();

        $defaultColors = ['green', 'blue', 'purple', 'orange', 'yellow', 'red', 'teal'];
        $knowledgeCategories = $dbCategories->map(function ($kc, $idx) use ($defaultColors) {
            return [
                'id' => $kc->id,
                'name' => $kc->name,
                'slug' => $kc->slug,
                'sort_order' => (int) $kc->sort_order,
                'is_active' => (bool) $kc->is_active,
                'status' => $kc->is_active ? 'Aktif' : 'Nonaktif',
                'articles_count' => (int) ($kc->articles_count ?? 0),
                'color' => $defaultColors[$idx % count($defaultColors)],
            ];
        })->toArray();

        $articles = $dbArticles->map(function ($a) {
            $htmlContent = KnowledgeArticleParser::renderBlocksToHtml($a->content ?? []);

            return [
                'id' => $a->id,
                'title' => $a->title,
                'slug' => $a->slug,
                'category_id' => $a->category_id,
                'category' => $a->category ? $a->category->name : 'Tips Penyimpanan',
                'status' => ucfirst($a->status),
                'published_at' => $a->created_at ? $a->created_at->translatedFormat('d F Y') : 'Draft',
                'image' => $a->image ?? 'storage/media/know_thawing_1786890543832.jpg',
                'excerpt' => $a->excerpt ?? '',
                'content' => $htmlContent ?: ($a->excerpt ?? ''),
                'sort_order' => (int) ($a->sort_order ?? 1),
            ];
        })->toArray();

        $mediaLibrary = $this->getMediaLibrary();

        return view('admin.knowledge', compact('knowledgeSection', 'articles', 'knowledgeCategories', 'mediaLibrary'));
    }

    /**
     * Update Knowledge Section Header Configuration (POST /admin/knowledge/section-settings).
     */
    public function knowledgeSectionUpdate(Request $request)
    {
        $validated = $request->validate([
            'label' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string',
        ]);

        $settings = [
            'label' => $validated['label'] ?? 'Edukasi & Inspirasi Dapur',
            'title' => $validated['title'] ?? 'Dapur & Knowledge',
            'subtitle' => $validated['subtitle'] ?? 'Panduan praktis seputar penanganan daging, thawing, penyimpanan frozen food, hingga tips memasak harian keluarga di Yogyakarta.',
        ];

        $this->siteSettingRepo->set('knowledge_section', $settings);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Pengaturan Header Section Knowledge & Tips berhasil disimpan!',
                'knowledge_section' => $settings,
            ]);
        }

        return redirect()->route('admin.knowledge')->with('success', 'Pengaturan Header Section Knowledge & Tips berhasil disimpan!');
    }

    /**
     * Store a new knowledge category (POST /admin/knowledge-categories).
     */
    public function knowledgeCategoryStore(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:knowledge_categories,slug',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        // Ensure slug uniqueness
        $baseSlug = $validated['slug'];
        $count = 1;
        while (KnowledgeCategory::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = "{$baseSlug}-{$count}";
            $count++;
        }

        $category = $this->knowledgeRepo->createCategory([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'sort_order' => $validated['sort_order'] ?? (KnowledgeCategory::count() + 1),
            'is_active' => $request->has('is_active') ? (bool) $validated['is_active'] : true,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Kategori pengetahuan {$category->name} berhasil dibuat.",
                'category' => $category,
            ], 201);
        }

        return redirect()->route('admin.knowledge')->with('success', "Kategori pengetahuan {$category->name} berhasil dibuat.");
    }

    /**
     * Update a knowledge category (PUT /admin/knowledge-categories/{id}).
     */
    public function knowledgeCategoryUpdate(Request $request, int $id)
    {
        $category = $this->knowledgeRepo->findCategoryById($id);
        if (!$category) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Kategori tidak ditemukan.'], 404);
            }
            return redirect()->route('admin.knowledge')->with('error', 'Kategori tidak ditemukan.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:knowledge_categories,slug,' . $id,
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $this->knowledgeRepo->updateCategory($id, [
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'sort_order' => $validated['sort_order'] ?? $category->sort_order,
            'is_active' => $request->has('is_active') ? (bool) $validated['is_active'] : $category->is_active,
        ]);

        $updated = $this->knowledgeRepo->findCategoryById($id);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Kategori {$updated->name} berhasil diperbarui.",
                'category' => $updated,
            ]);
        }

        return redirect()->route('admin.knowledge')->with('success', "Kategori {$updated->name} berhasil diperbarui.");
    }

    /**
     * Delete a knowledge category with Delete Guard (DELETE /admin/knowledge-categories/{id}).
     */
    public function knowledgeCategoryDestroy(Request $request, int $id)
    {
        $category = $this->knowledgeRepo->findCategoryById($id);
        if (!$category) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Kategori tidak ditemukan.'], 404);
            }
            return redirect()->route('admin.knowledge')->with('error', 'Kategori tidak ditemukan.');
        }

        // Critical Delete Guard: Block delete if category has associated articles
        $articleCount = $this->knowledgeRepo->countCategoryArticles($id);
        if ($articleCount > 0) {
            $errorMsg = "Kategori \"{$category->name}\" tidak dapat dihapus karena masih memiliki {$articleCount} artikel terkait. Pindahkan atau hapus artikel terlebih dahulu.";
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'blocked' => true,
                    'message' => $errorMsg,
                ], 422);
            }
            return redirect()->route('admin.knowledge')->with('error', $errorMsg);
        }

        $this->knowledgeRepo->deleteCategory($id);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Kategori {$category->name} berhasil dihapus.",
            ]);
        }

        return redirect()->route('admin.knowledge')->with('success', "Kategori {$category->name} berhasil dihapus.");
    }

    /**
     * Toggle active status of a knowledge category (PATCH /admin/knowledge-categories/{id}/toggle).
     */
    public function knowledgeCategoryToggle(Request $request, int $id)
    {
        $category = $this->knowledgeRepo->toggleCategoryActive($id);
        if (!$category) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Kategori tidak ditemukan.'], 404);
            }
            return redirect()->route('admin.knowledge')->with('error', 'Kategori tidak ditemukan.');
        }

        $statusLabel = $category->is_active ? 'Aktif' : 'Nonaktif';

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'is_active' => (bool) $category->is_active,
                'status' => $statusLabel,
                'message' => "Status kategori {$category->name} diubah menjadi {$statusLabel}.",
                'category' => $category,
            ]);
        }

        return redirect()->route('admin.knowledge')->with('success', "Status kategori {$category->name} diubah menjadi {$statusLabel}.");
    }

    /**
     * Reorder knowledge categories (POST /admin/knowledge-categories/reorder).
     */
    public function knowledgeCategoryReorder(Request $request)
    {
        $validated = $this->validateReorderPayload($request);
        $firstOrder = is_array($validated['orders']) && !empty($validated['orders']) ? array_values($validated['orders'])[0] : null;
        if (!is_array($firstOrder)) {
            $mappedOrders = [];
            foreach ($validated['orders'] as $id => $order) {
                $mappedOrders[] = ['id' => (int) $id, 'sort_order' => (int) $order];
            }
            $validated['orders'] = $mappedOrders;
        }

        $this->knowledgeRepo->reorderCategories($validated['orders']);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Urutan kategori pengetahuan berhasil diperbarui.',
            ]);
        }

        return redirect()->route('admin.knowledge')->with('success', 'Urutan kategori pengetahuan berhasil diperbarui.');
    }

    /**
     * Store a new knowledge article (POST /admin/knowledge-articles).
     */
    public function knowledgeArticleStore(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:knowledge_categories,id',
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:knowledge_articles,slug',
            'excerpt' => 'nullable|string',
            'content' => 'required',
            'image' => 'nullable|string|max:255',
            'status' => 'required|in:published,draft',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        // Ensure slug uniqueness
        $baseSlug = $validated['slug'];
        $count = 1;
        while (KnowledgeArticle::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = "{$baseSlug}-{$count}";
            $count++;
        }

        $parsedContent = KnowledgeArticleParser::parse($validated['content']);

        $article = $this->knowledgeRepo->createArticle([
            'category_id' => (int) $validated['category_id'],
            'title' => $validated['title'],
            'slug' => $validated['slug'],
            'excerpt' => $validated['excerpt'] ?? '',
            'content' => $parsedContent,
            'image' => $validated['image'] ?? 'storage/media/know_thawing_1786890543832.jpg',
            'status' => strtolower($validated['status']),
            'sort_order' => $validated['sort_order'] ?? (KnowledgeArticle::count() + 1),
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Artikel \"{$article->title}\" berhasil dibuat.",
                'article' => $this->knowledgeRepo->findArticleById($article->id),
            ], 201);
        }

        return redirect()->route('admin.knowledge')->with('success', "Artikel \"{$article->title}\" berhasil dibuat.");
    }

    /**
     * Update a knowledge article (PUT /admin/knowledge-articles/{id}).
     */
    public function knowledgeArticleUpdate(Request $request, int $id)
    {
        $article = $this->knowledgeRepo->findArticleById($id);
        if (!$article) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Artikel tidak ditemukan.'], 404);
            }
            return redirect()->route('admin.knowledge')->with('error', 'Artikel tidak ditemukan.');
        }

        $validated = $request->validate([
            'category_id' => 'required|exists:knowledge_categories,id',
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:knowledge_articles,slug,' . $id,
            'excerpt' => 'nullable|string',
            'content' => 'required',
            'image' => 'nullable|string|max:255',
            'status' => 'required|in:published,draft',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        $parsedContent = KnowledgeArticleParser::parse($validated['content']);

        $this->knowledgeRepo->updateArticle($id, [
            'category_id' => (int) $validated['category_id'],
            'title' => $validated['title'],
            'slug' => $validated['slug'],
            'excerpt' => $validated['excerpt'] ?? '',
            'content' => $parsedContent,
            'image' => $validated['image'] ?? $article->image,
            'status' => strtolower($validated['status']),
            'sort_order' => $validated['sort_order'] ?? $article->sort_order,
        ]);

        $updated = $this->knowledgeRepo->findArticleById($id);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Artikel \"{$updated->title}\" berhasil diperbarui.",
                'article' => $updated,
            ]);
        }

        return redirect()->route('admin.knowledge')->with('success', "Artikel \"{$updated->title}\" berhasil diperbarui.");
    }

    /**
     * Delete a knowledge article (DELETE /admin/knowledge-articles/{id}).
     */
    public function knowledgeArticleDestroy(Request $request, int $id)
    {
        $article = $this->knowledgeRepo->findArticleById($id);
        if (!$article) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Artikel tidak ditemukan.'], 404);
            }
            return redirect()->route('admin.knowledge')->with('error', 'Artikel tidak ditemukan.');
        }

        $this->knowledgeRepo->deleteArticle($id);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Artikel \"{$article->title}\" berhasil dihapus.",
            ]);
        }

        return redirect()->route('admin.knowledge')->with('success', "Artikel \"{$article->title}\" berhasil dihapus.");
    }

    /**
     * Toggle status of a knowledge article (PATCH /admin/knowledge-articles/{id}/toggle).
     */
    public function knowledgeArticleToggle(Request $request, int $id)
    {
        $article = $this->knowledgeRepo->findArticleById($id);
        if (!$article) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Artikel tidak ditemukan.'], 404);
            }
            return redirect()->route('admin.knowledge')->with('error', 'Artikel tidak ditemukan.');
        }

        $newStatus = ($article->status === 'published') ? 'draft' : 'published';
        $this->knowledgeRepo->updateArticle($id, ['status' => $newStatus]);
        $updated = $this->knowledgeRepo->findArticleById($id);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'status' => $newStatus,
                'message' => "Status artikel diubah menjadi {$newStatus}.",
                'article' => $updated,
            ]);
        }

        return redirect()->route('admin.knowledge')->with('success', "Status artikel diubah menjadi {$newStatus}.");
    }

    /**
     * Reorder knowledge articles (POST /admin/knowledge-articles/reorder).
     */
    public function knowledgeArticleReorder(Request $request)
    {
        $validated = $this->validateReorderPayload($request);
        $firstOrder = is_array($validated['orders']) && !empty($validated['orders']) ? array_values($validated['orders'])[0] : null;
        if (!is_array($firstOrder)) {
            $mappedOrders = [];
            foreach ($validated['orders'] as $id => $order) {
                $mappedOrders[] = ['id' => (int) $id, 'sort_order' => (int) $order];
            }
            $validated['orders'] = $mappedOrders;
        }

        $this->knowledgeRepo->reorderArticles($validated['orders']);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Urutan artikel pengetahuan berhasil diperbarui.',
            ]);
        }

        return redirect()->route('admin.knowledge')->with('success', 'Urutan artikel pengetahuan berhasil diperbarui.');
    }

    /**
     * Footer (Ulasan Pelanggan, Lokasi, Footer) Management Screen.
     */
    public function footer()
    {
        $dbReviews = $this->reviewRepo->getAll();
        $reviewSettings = $this->siteSettingRepo->get('review_settings', [
            'review_mode' => 'manual',
            'google_place_id' => null,
            'google_rating' => null,
            'google_total_reviews' => null,
            'last_synced_at' => null,
        ]);

        $reviewItems = $dbReviews->map(function ($r) {
            return [
                'id' => $r->id,
                'name' => $r->reviewer_name,
                'rating' => (int) $r->rating,
                'comment' => $r->review_text ?? ($r->comment ?? ''),
                'review_text' => $r->review_text ?? ($r->comment ?? ''),
                'role' => $r->reviewer_title ?: 'Pelanggan',
                'location' => $r->reviewer_location ?: 'Yogyakarta',
                'time' => $r->reviewed_at ? $r->reviewed_at->diffForHumans() : 'Baru saja',
                'source' => $r->source === 'google' ? 'Google Review' : 'Manual Review',
                'is_active' => (bool) $r->is_active,
                'sort_order' => (int) ($r->sort_order ?? 1),
            ];
        })->toArray();

        $locationSetting = $this->siteSettingRepo->get('location', config('location', []));
        $footerSetting = $this->siteSettingRepo->get('footer', [
            'brand_title' => 'Sumber Protein Jogja',
            'brand_desc' => 'Penyedia bahan makanan mentah, frozen food, dan olahan ready-to-cook berkualitas di Yogyakarta. Melayani kebutuhan konsumsi harian keluarga dan suplai horeka/curah.',
            'social_links' => [
                ['id' => 1, 'url' => 'https://instagram.com/sumberproteinjogja'],
                ['id' => 2, 'url' => 'https://tiktok.com/@sumberproteinjogja'],
                ['id' => 3, 'url' => 'https://wa.me/6281234567890'],
            ],
            'nav_title' => 'Navigasi Cepat',
            'nav_links' => [
                ['title' => 'Beranda', 'url' => '#hero'],
                ['title' => 'Kategori Produk', 'url' => '#kategori'],
                ['title' => 'Katalog Pilihan', 'url' => '#produk'],
                ['title' => 'Keunggulan Kami', 'url' => '#keunggulan'],
                ['title' => 'Dapur & Knowledge', 'url' => '#knowledge'],
                ['title' => 'Ulasan Pelanggan', 'url' => '#testimoni'],
            ],
            'category_title' => 'Kategori Pangan',
            'category_links' => [
                ['title' => 'Daging Sapi Slice & Sengkel', 'url' => '#produk'],
                ['title' => 'Ayam Ungkep Bumbu Kuning', 'url' => '#produk'],
                ['title' => 'Dada Ayam Fillet Boneless', 'url' => '#produk'],
                ['title' => 'Fillet Gurame & Salmon', 'url' => '#produk'],
                ['title' => 'Paket Sayur Siap Masak', 'url' => '#produk'],
                ['title' => 'Ayam & Daging Curah (Bulk)', 'url' => '#produk'],
            ],
            'outlet_title' => 'Outlet Yogyakarta',
            'outlet_address' => 'Jl. Kaliurang Km. 8.5 No. 42, Sleman, D.I. Yogyakarta 55581',
            'outlet_hours_label' => 'Jam Operasional:',
            'outlet_hours' => 'Senin – Minggu (07.00 – 19.00 WIB)',
            'outlet_phone_label' => 'Hotline Pemesanan:',
            'outlet_phone' => '+62 812-3456-7890',
            'copyright' => 'Sumber Protein Jogja. Hak Cipta Dilindungi.',
            'legal_links' => [
                ['title' => 'Syarat & Ketentuan', 'url' => '#'],
                ['title' => 'Kebijakan Privasi', 'url' => '#'],
                ['title' => 'Sertifikasi Halal', 'url' => '#'],
            ],
        ]);

        $siteData = $this->siteSettingRepo->get('site', config('site', []));
        $contacts = $siteData['contacts'] ?? config('site.contacts', []);

        $footerData = [
            'reviews' => [
                'status' => 'Live DB Data',
                'source_name' => ($reviewSettings['review_mode'] ?? 'manual') === 'google' ? 'Google Maps' : 'Manual Database',
                'last_updated' => $reviewSettings['last_synced_at'] ?? 'Belum ada sync Google',
                'place_name' => 'Sumber Protein Jogja',
                'section_badge' => 'Ulasan Pelanggan',
                'section_title' => 'Apa Kata Mereka?',
                'section_subtitle' => 'Pengalaman nyata dari ibu rumah tangga, chef rumahan, hingga pemilik kedai kuliner di Yogyakarta.',
                'rating' => (float) ($reviewSettings['google_rating'] ?? 5.0),
                'total_reviews' => (string) ($reviewSettings['google_total_reviews'] ?? count($reviewItems)),
                'displayed_count' => count(array_filter($reviewItems, fn($item) => $item['is_active'])),
                'google_place_id' => $reviewSettings['google_place_id'] ?? null,
                'google_place_url' => !empty($reviewSettings['google_place_id']) ? 'https://search.google.com/local/writereview?placeid=' . $reviewSettings['google_place_id'] : null,
                'review_mode' => $reviewSettings['review_mode'] ?? 'manual',
                'items' => $reviewItems,
            ],
            'location' => $locationSetting,
            'actual_footer' => $footerSetting,
            'contacts' => $contacts,
        ];

        return view('admin.footer', compact('footerData', 'contacts'));
    }

    /**
     * Update Footer & Location Settings (POST /admin/footer).
     */
    public function footerUpdate(Request $request)
    {
        $validated = $request->validate([
            'location' => 'nullable|array',
            'location.section' => 'nullable|array',
            'location.section.badge' => 'nullable|string|max:255',
            'location.section.title' => 'nullable|string|max:255',
            'location.section.subtitle' => 'nullable|string|max:1000',
            'location.outlet' => 'nullable|array',
            'location.outlet.name' => 'nullable|string|max:255',
            'location.outlet.tagline' => 'nullable|string|max:255',
            'location.outlet.status_badge' => 'nullable|string|max:255',
            'location.address' => 'nullable|array',
            'location.address.street' => 'nullable|string|max:255',
            'location.address.district' => 'nullable|string|max:255',
            'location.address.city' => 'nullable|string|max:255',
            'location.address.province' => 'nullable|string|max:255',
            'location.address.postal_code' => 'nullable|string|max:20',
            'location.address.country_code' => 'nullable|string|max:10',
            'location.address.full' => 'nullable|string|max:1000',
            'location.coordinates' => 'nullable|array',
            'location.coordinates.latitude' => 'nullable|numeric|between:-90,90',
            'location.coordinates.longitude' => 'nullable|numeric|between:-180,180',
            'location.operational_hours' => 'nullable|array',
            'location.operational_hours.timezone' => 'nullable|string|max:50',
            'location.operational_hours.display' => 'nullable|string|max:255',
            'location.operational_hours.days' => 'nullable|array',
            'location.operational_hours.days.*.day' => 'nullable|string|max:50',
            'location.operational_hours.days.*.open' => 'nullable|string|max:20',
            'location.operational_hours.days.*.close' => 'nullable|string|max:20',
            'location.operational_hours.days.*.closed' => 'nullable|boolean',
            'location.delivery_note' => 'nullable|string|max:500',
            'location.contact_key' => 'nullable|string|max:50',
            'location.contact_id' => 'nullable|string|max:50',
            'location.phone' => 'nullable|string|max:50',
            'location.maps' => 'nullable|array',
            'location.maps.link' => 'nullable|string|max:2048',
            'location.maps.embed' => 'nullable|string|max:2048',
            'location.maps.button_text' => 'nullable|string|max:255',
            'location.maps.map_title' => 'nullable|string|max:255',
            'location.maps.map_location_tag' => 'nullable|string|max:255',

            'actual_footer' => 'nullable|array',
            'actual_footer.brand_name' => 'nullable|string|max:255',
            'actual_footer.brand_title' => 'nullable|string|max:255',
            'actual_footer.tagline' => 'nullable|string|max:500',
            'actual_footer.description' => 'nullable|string|max:1000',
            'actual_footer.brand_desc' => 'nullable|string|max:1000',
            'actual_footer.outlet_title' => 'nullable|string|max:255',
            'actual_footer.outlet_address' => 'nullable|string|max:1000',
            'actual_footer.outlet_hours_label' => 'nullable|string|max:255',
            'actual_footer.outlet_hours' => 'nullable|string|max:255',
            'actual_footer.outlet_phone_label' => 'nullable|string|max:255',
            'actual_footer.outlet_phone' => 'nullable|string|max:50',
            'actual_footer.outlet_email' => 'nullable|string|max:255',
            'actual_footer.nav_title' => 'nullable|string|max:255',
            'actual_footer.category_title' => 'nullable|string|max:255',
            'actual_footer.copyright' => 'nullable|string|max:255',
            'actual_footer.social_links' => 'nullable|array',
            'actual_footer.social_links.*.id' => 'nullable|integer|min:1',
            'actual_footer.social_links.*.platform' => 'nullable|string|max:50',
            'actual_footer.social_links.*.url' => 'nullable|string|max:2048',
            'actual_footer.social_links.*.icon' => 'nullable|string|max:50',
            'actual_footer.nav_links' => 'nullable|array',
            'actual_footer.nav_links.*.id' => 'nullable|integer|min:1',
            'actual_footer.nav_links.*.label' => 'nullable|string|max:255',
            'actual_footer.nav_links.*.title' => 'nullable|string|max:255',
            'actual_footer.nav_links.*.url' => 'nullable|string|max:2048',
            'actual_footer.category_links' => 'nullable|array',
            'actual_footer.category_links.*.id' => 'nullable|integer|min:1',
            'actual_footer.category_links.*.label' => 'nullable|string|max:255',
            'actual_footer.category_links.*.name' => 'nullable|string|max:255',
            'actual_footer.category_links.*.url' => 'nullable|string|max:2048',
            'actual_footer.legal_links' => 'nullable|array',
            'actual_footer.legal_links.*.id' => 'nullable|integer|min:1',
            'actual_footer.legal_links.*.label' => 'nullable|string|max:255',
            'actual_footer.legal_links.*.title' => 'nullable|string|max:255',
            'actual_footer.legal_links.*.url' => 'nullable|string|max:2048',

            'footer' => 'nullable|array',
            'footer.brand_name' => 'nullable|string|max:255',
            'footer.brand_title' => 'nullable|string|max:255',
            'footer.tagline' => 'nullable|string|max:500',
            'footer.description' => 'nullable|string|max:1000',
            'footer.brand_desc' => 'nullable|string|max:1000',
            'footer.outlet_title' => 'nullable|string|max:255',
            'footer.outlet_address' => 'nullable|string|max:1000',
            'footer.outlet_hours_label' => 'nullable|string|max:255',
            'footer.outlet_hours' => 'nullable|string|max:255',
            'footer.outlet_phone_label' => 'nullable|string|max:255',
            'footer.outlet_phone' => 'nullable|string|max:50',
            'footer.outlet_email' => 'nullable|string|max:255',
            'footer.nav_title' => 'nullable|string|max:255',
            'footer.category_title' => 'nullable|string|max:255',
            'footer.copyright' => 'nullable|string|max:255',
            'footer.social_links' => 'nullable|array',
            'footer.social_links.*.id' => 'nullable|integer|min:1',
            'footer.social_links.*.platform' => 'nullable|string|max:50',
            'footer.social_links.*.url' => 'nullable|string|max:2048',
            'footer.social_links.*.icon' => 'nullable|string|max:50',
            'footer.nav_links' => 'nullable|array',
            'footer.nav_links.*.id' => 'nullable|integer|min:1',
            'footer.nav_links.*.label' => 'nullable|string|max:255',
            'footer.nav_links.*.title' => 'nullable|string|max:255',
            'footer.nav_links.*.url' => 'nullable|string|max:2048',
            'footer.category_links' => 'nullable|array',
            'footer.category_links.*.id' => 'nullable|integer|min:1',
            'footer.category_links.*.label' => 'nullable|string|max:255',
            'footer.category_links.*.name' => 'nullable|string|max:255',
            'footer.category_links.*.url' => 'nullable|string|max:2048',
            'footer.legal_links' => 'nullable|array',
            'footer.legal_links.*.id' => 'nullable|integer|min:1',
            'footer.legal_links.*.label' => 'nullable|string|max:255',
            'footer.legal_links.*.title' => 'nullable|string|max:255',
            'footer.legal_links.*.url' => 'nullable|string|max:2048',
        ]);

        if (isset($validated['location']) && is_array($validated['location'])) {
            $rawLoc = $validated['location'];
            $cleanDays = [];
            if (isset($rawLoc['operational_hours']['days']) && is_array($rawLoc['operational_hours']['days'])) {
                foreach ($rawLoc['operational_hours']['days'] as $d) {
                    if (!is_array($d)) {
                        continue;
                    }
                    $cleanDays[] = [
                        'day' => $d['day'] ?? null,
                        'open' => $d['open'] ?? null,
                        'close' => $d['close'] ?? null,
                        'closed' => isset($d['closed']) ? (bool) $d['closed'] : null,
                    ];
                }
            }

            $cleanLocation = [
                'section' => [
                    'badge' => $rawLoc['section']['badge'] ?? null,
                    'title' => $rawLoc['section']['title'] ?? null,
                    'subtitle' => $rawLoc['section']['subtitle'] ?? null,
                ],
                'outlet' => [
                    'name' => $rawLoc['outlet']['name'] ?? null,
                    'tagline' => $rawLoc['outlet']['tagline'] ?? null,
                    'status_badge' => $rawLoc['outlet']['status_badge'] ?? null,
                ],
                'address' => [
                    'street' => $rawLoc['address']['street'] ?? null,
                    'district' => $rawLoc['address']['district'] ?? null,
                    'city' => $rawLoc['address']['city'] ?? null,
                    'province' => $rawLoc['address']['province'] ?? null,
                    'postal_code' => $rawLoc['address']['postal_code'] ?? null,
                    'country_code' => $rawLoc['address']['country_code'] ?? null,
                    'full' => $rawLoc['address']['full'] ?? null,
                ],
                'coordinates' => [
                    'latitude' => isset($rawLoc['coordinates']['latitude']) ? (float) $rawLoc['coordinates']['latitude'] : null,
                    'longitude' => isset($rawLoc['coordinates']['longitude']) ? (float) $rawLoc['coordinates']['longitude'] : null,
                ],
                'operational_hours' => [
                    'timezone' => $rawLoc['operational_hours']['timezone'] ?? null,
                    'display' => $rawLoc['operational_hours']['display'] ?? null,
                    'days' => $cleanDays,
                ],
                'delivery_note' => $rawLoc['delivery_note'] ?? null,
                'contact_key' => $rawLoc['contact_key'] ?? ($rawLoc['contact_id'] ?? null),
                'phone' => $rawLoc['phone'] ?? null,
                'maps' => [
                    'link' => isset($rawLoc['maps']['link']) ? $this->sanitizeExternalUrl($rawLoc['maps']['link']) : null,
                    'embed' => isset($rawLoc['maps']['embed']) ? $this->sanitizeMapsEmbedUrl($rawLoc['maps']['embed']) : null,
                    'button_text' => $rawLoc['maps']['button_text'] ?? null,
                    'map_title' => $rawLoc['maps']['map_title'] ?? null,
                    'map_location_tag' => $rawLoc['maps']['map_location_tag'] ?? null,
                ],
            ];

            $this->siteSettingRepo->set('location', $cleanLocation);
        }

        $footerPayload = $validated['actual_footer'] ?? ($validated['footer'] ?? null);
        if ($footerPayload !== null && is_array($footerPayload)) {
            $cleanSocial = [];
            if (isset($footerPayload['social_links']) && is_array($footerPayload['social_links'])) {
                foreach ($footerPayload['social_links'] as $idx => $soc) {
                    if (!is_array($soc)) {
                        continue;
                    }
                    $cleanSocial[] = [
                        'id' => isset($soc['id']) ? (int) $soc['id'] : ($idx + 1),
                        'platform' => isset($soc['platform']) ? (string) $soc['platform'] : null,
                        'url' => isset($soc['url']) ? ($this->sanitizeExternalUrl($soc['url']) ?? '') : '',
                        'icon' => isset($soc['icon']) ? (string) $soc['icon'] : null,
                    ];
                }
            }

            $cleanNav = [];
            if (isset($footerPayload['nav_links']) && is_array($footerPayload['nav_links'])) {
                foreach ($footerPayload['nav_links'] as $idx => $nav) {
                    if (!is_array($nav)) {
                        continue;
                    }
                    $navTitle = isset($nav['title']) ? (string) $nav['title'] : (isset($nav['label']) ? (string) $nav['label'] : '');
                    $cleanNav[] = [
                        'id' => isset($nav['id']) ? (int) $nav['id'] : ($idx + 1),
                        'title' => $navTitle,
                        'label' => $navTitle,
                        'url' => isset($nav['url']) ? $this->sanitizeNavigationUrl($nav['url'], '#') : '#',
                    ];
                }
            }

            $cleanCategory = [];
            if (isset($footerPayload['category_links']) && is_array($footerPayload['category_links'])) {
                foreach ($footerPayload['category_links'] as $idx => $cat) {
                    if (!is_array($cat)) {
                        continue;
                    }
                    $catTitle = isset($cat['title']) ? (string) $cat['title'] : (isset($cat['label']) ? (string) $cat['label'] : (isset($cat['name']) ? (string) $cat['name'] : ''));
                    $cleanCategory[] = [
                        'id' => isset($cat['id']) ? (int) $cat['id'] : ($idx + 1),
                        'title' => $catTitle,
                        'label' => $catTitle,
                        'name' => $catTitle,
                        'url' => isset($cat['url']) ? $this->sanitizeNavigationUrl($cat['url'], '#produk') : '#produk',
                    ];
                }
            }

            $cleanLegal = [];
            if (isset($footerPayload['legal_links']) && is_array($footerPayload['legal_links'])) {
                foreach ($footerPayload['legal_links'] as $idx => $legal) {
                    if (!is_array($legal)) {
                        continue;
                    }
                    $legalTitle = isset($legal['title']) ? (string) $legal['title'] : (isset($legal['label']) ? (string) $legal['label'] : '');
                    $cleanLegal[] = [
                        'id' => isset($legal['id']) ? (int) $legal['id'] : ($idx + 1),
                        'title' => $legalTitle,
                        'label' => $legalTitle,
                        'url' => isset($legal['url']) ? $this->sanitizeNavigationUrl($legal['url'], '#') : '#',
                    ];
                }
            }

            $cleanFooter = [
                'brand_name' => $footerPayload['brand_name'] ?? ($footerPayload['brand_title'] ?? null),
                'brand_title' => $footerPayload['brand_title'] ?? ($footerPayload['brand_name'] ?? null),
                'tagline' => $footerPayload['tagline'] ?? null,
                'description' => $footerPayload['description'] ?? ($footerPayload['brand_desc'] ?? null),
                'brand_desc' => $footerPayload['brand_desc'] ?? ($footerPayload['description'] ?? null),
                'outlet_title' => $footerPayload['outlet_title'] ?? null,
                'outlet_address' => $footerPayload['outlet_address'] ?? null,
                'outlet_hours_label' => $footerPayload['outlet_hours_label'] ?? 'Jam Operasional:',
                'outlet_hours' => $footerPayload['outlet_hours'] ?? null,
                'outlet_phone_label' => $footerPayload['outlet_phone_label'] ?? 'Hotline Pemesanan:',
                'outlet_phone' => $footerPayload['outlet_phone'] ?? null,
                'outlet_email' => $footerPayload['outlet_email'] ?? null,
                'nav_title' => $footerPayload['nav_title'] ?? null,
                'category_title' => $footerPayload['category_title'] ?? null,
                'copyright' => $footerPayload['copyright'] ?? null,
                'social_links' => $cleanSocial,
                'nav_links' => $cleanNav,
                'category_links' => $cleanCategory,
                'legal_links' => $cleanLegal,
            ];

            $this->siteSettingRepo->set('footer', $cleanFooter);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Pengaturan Lokasi & Footer berhasil disimpan ke database.',
                'location' => $this->siteSettingRepo->get('location'),
                'footer' => $this->siteSettingRepo->get('footer'),
            ]);
        }

        return redirect()->route('admin.footer')->with('success', 'Pengaturan Lokasi & Footer berhasil disimpan ke database.');
    }

    /**
     * Create a new review (POST /admin/reviews).
     */
    public function reviewStore(Request $request)
    {
        $validated = $request->validate([
            'reviewer_name' => 'required|string|max:255',
            'reviewer_title' => 'nullable|string|max:255',
            'reviewer_location' => 'nullable|string|max:255',
            'review_text' => 'required|string',
            'rating' => 'required|integer|min:1|max:5',
            'reviewed_at' => 'nullable|date',
            'avatar' => 'nullable|string|max:255',
            'source' => 'nullable|string|in:manual,google',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['source'] = $validated['source'] ?? 'manual';
        $validated['google_review_id'] = null;
        $validated['is_active'] = isset($validated['is_active']) ? (bool) $validated['is_active'] : true;
        $validated['sort_order'] = isset($validated['sort_order']) ? (int) $validated['sort_order'] : ($this->reviewRepo->getAll()->count() + 1);
        $validated['reviewed_at'] = !empty($validated['reviewed_at']) ? $validated['reviewed_at'] : now();

        $review = $this->reviewRepo->create($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Ulasan dari {$review->reviewer_name} berhasil ditambahkan.",
                'review' => $review,
            ], 201);
        }

        return redirect()->route('admin.footer')->with('success', "Ulasan dari {$review->reviewer_name} berhasil ditambahkan.");
    }

    /**
     * Update an existing review (PUT /admin/reviews/{id}).
     */
    public function reviewUpdate(Request $request, int $id)
    {
        $review = $this->reviewRepo->findById($id);
        if (!$review) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ulasan tidak ditemukan.',
                ], 404);
            }
            return redirect()->route('admin.footer')->with('error', 'Ulasan tidak ditemukan.');
        }

        $validated = $request->validate([
            'reviewer_name' => 'required|string|max:255',
            'reviewer_title' => 'nullable|string|max:255',
            'reviewer_location' => 'nullable|string|max:255',
            'review_text' => 'required|string',
            'rating' => 'required|integer|min:1|max:5',
            'reviewed_at' => 'nullable|date',
            'avatar' => 'nullable|string|max:255',
            'source' => 'nullable|string|in:manual,google',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if (isset($validated['is_active'])) {
            $validated['is_active'] = (bool) $validated['is_active'];
        }

        $updated = $this->reviewRepo->update($id, $validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => $updated,
                'message' => "Ulasan dari {$review->reviewer_name} berhasil diperbarui.",
                'review' => $this->reviewRepo->findById($id),
            ]);
        }

        return redirect()->route('admin.footer')->with('success', "Ulasan dari {$review->reviewer_name} berhasil diperbarui.");
    }

    /**
     * Delete a review (DELETE /admin/reviews/{id}).
     */
    public function reviewDestroy(Request $request, int $id)
    {
        $review = $this->reviewRepo->findById($id);
        if (!$review) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ulasan tidak ditemukan.',
                ], 404);
            }
            return redirect()->route('admin.footer')->with('error', 'Ulasan tidak ditemukan.');
        }

        $this->reviewRepo->delete($id);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Ulasan dari {$review->reviewer_name} berhasil dihapus.",
            ]);
        }

        return redirect()->route('admin.footer')->with('success', "Ulasan dari {$review->reviewer_name} berhasil dihapus.");
    }

    /**
     * Toggle active state of a review (PATCH /admin/reviews/{id}/toggle).
     */
    public function reviewToggle(Request $request, int $id)
    {
        $review = $this->reviewRepo->toggleActive($id);
        if (!$review) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ulasan tidak ditemukan.',
                ], 404);
            }
            return redirect()->route('admin.footer')->with('error', 'Ulasan tidak ditemukan.');
        }

        $statusLabel = $review->is_active ? 'Aktif' : 'Nonaktif';

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'is_active' => $review->is_active,
                'message' => "Status ulasan {$review->reviewer_name} diubah menjadi {$statusLabel}.",
                'review' => $review,
            ]);
        }

        return redirect()->route('admin.footer')->with('success', "Status ulasan {$review->reviewer_name} diubah menjadi {$statusLabel}.");
    }

    /**
     * Reorder reviews (POST /admin/reviews/reorder).
     */
    public function reviewReorder(Request $request)
    {
        $validated = $this->validateReorderPayload($request);
        $firstOrder = is_array($validated['orders']) && !empty($validated['orders']) ? array_values($validated['orders'])[0] : null;
        if (is_array($firstOrder)) {
            $mappedOrders = [];
            foreach ($validated['orders'] as $item) {
                $mappedOrders[$item['id']] = $item['sort_order'] ?? ($item['order'] ?? 0);
            }
            $validated['orders'] = $mappedOrders;
        }

        $this->reviewRepo->reorder($validated['orders']);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Urutan ulasan berhasil diperbarui.',
            ]);
        }

        return redirect()->route('admin.footer')->with('success', 'Urutan ulasan berhasil diperbarui.');
    }

    /**
     * Switch Review Source Mode with Google Guard (POST /admin/reviews/mode).
     */
    public function reviewMode(Request $request)
    {
        $modeInput = $request->input('mode') ?? $request->input('review_mode');
        if (!in_array($modeInput, ['manual', 'google'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Parameter mode ulasan tidak valid.',
            ], 422);
        }

        $settings = $this->siteSettingRepo->get('review_settings', [
            'review_mode' => 'manual',
            'google_place_id' => null,
            'google_rating' => null,
            'google_total_reviews' => null,
            'last_synced_at' => null,
        ]);

        $targetMode = $modeInput;

        // Google Mode Guard: Block switch to google if google_place_id is empty
        if ($targetMode === 'google' && empty($settings['google_place_id'])) {
            return response()->json([
                'success' => false,
                'blocked' => true,
                'message' => 'Konfigurasi Google Review belum lengkap. Harap isi Google Place ID terlebih dahulu.',
            ], 422);
        }

        $settings['review_mode'] = $targetMode;
        $this->siteSettingRepo->set('review_settings', $settings);

        $modeLabel = $targetMode === 'google' ? 'Google Review' : 'Manual Review';

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Sumber ulasan berhasil dialihkan ke {$modeLabel}.",
                'review_settings' => $settings,
            ]);
        }

        return redirect()->route('admin.footer')->with('success', "Sumber ulasan berhasil dialihkan ke {$modeLabel}.");
    }

    /**
     * Update Google Review Configuration (POST /admin/reviews/google-config).
     */
    public function reviewGoogleConfig(Request $request)
    {
        $validated = $request->validate([
            'google_place_id' => 'nullable|string|max:255',
            'google_rating' => 'nullable|numeric|min:1|max:5',
            'google_total_reviews' => 'nullable|integer|min:0',
        ]);

        $settings = $this->siteSettingRepo->get('review_settings', [
            'review_mode' => 'manual',
            'google_place_id' => null,
            'google_rating' => null,
            'google_total_reviews' => null,
            'last_synced_at' => null,
        ]);

        $settings['google_place_id'] = $validated['google_place_id'] ?? null;
        if (isset($validated['google_rating'])) {
            $settings['google_rating'] = (float) $validated['google_rating'];
        }
        if (isset($validated['google_total_reviews'])) {
            $settings['google_total_reviews'] = (int) $validated['google_total_reviews'];
        }

        $this->siteSettingRepo->set('review_settings', $settings);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Konfigurasi Google Review berhasil diperbarui.',
                'review_settings' => $settings,
            ]);
        }

        return redirect()->route('admin.footer')->with('success', 'Konfigurasi Google Review berhasil diperbarui.');
    }

    /**
     * Sync Google Reviews from Google Places API (POST /admin/reviews/sync-google).
     */
    public function reviewSyncGoogle(Request $request)
    {
        $settings = $this->siteSettingRepo->get('review_settings', [
            'review_mode' => 'manual',
            'google_place_id' => null,
            'google_rating' => null,
            'google_total_reviews' => null,
            'last_synced_at' => null,
        ]);

        $placeId = $request->input('google_place_id') ?? $settings['google_place_id'];
        if (empty($placeId)) {
            return response()->json([
                'success' => false,
                'message' => 'Google Place ID belum diisi. Harap masukkan Google Place ID terlebih dahulu.',
            ], 422);
        }

        $apiKey = env('GOOGLE_PLACES_API_KEY') ?? env('GOOGLE_MAPS_API_KEY');

        if (empty($apiKey)) {
            return response()->json([
                'success' => false,
                'api_key_missing' => true,
                'message' => 'Google Places API Key belum disetting di file .env (GOOGLE_PLACES_API_KEY). Tanpa API key dari Google Cloud Console, Google tidak mengizinkan penarikan ulasan otomatis. Anda dapat memasukkan ulasan pelanggan Google secara manual.',
                'place_id' => $placeId,
            ], 200);
        }

        try {
            $url = "https://maps.googleapis.com/maps/api/place/details/json?place_id={$placeId}&fields=name,rating,user_ratings_total,reviews&key={$apiKey}&language=id";
            $response = \Illuminate\Support\Facades\Http::timeout(10)->get($url);
            $data = $response->json();

            if ($response->successful() && ($data['status'] ?? '') === 'OK') {
                $result = $data['result'] ?? [];

                if (isset($result['rating'])) {
                    $settings['google_rating'] = (float) $result['rating'];
                }
                if (isset($result['user_ratings_total'])) {
                    $settings['google_total_reviews'] = (int) $result['user_ratings_total'];
                }
                $settings['last_synced_at'] = \Carbon\Carbon::now()->format('d M Y, H:i') . ' WIB';
                $this->siteSettingRepo->set('review_settings', $settings);

                $syncedCount = 0;
                if (!empty($result['reviews'])) {
                    foreach ($result['reviews'] as $gRev) {
                        \App\Models\Review::updateOrCreate(
                            [
                                'reviewer_name' => $gRev['author_name'] ?? 'Google User',
                                'source' => 'google',
                            ],
                            [
                                'rating' => $gRev['rating'] ?? 5,
                                'review_text' => $gRev['text'] ?? '',
                                'reviewer_title' => 'Google Reviewer',
                                'reviewer_location' => 'Google Maps',
                                'is_active' => true,
                                'reviewed_at' => isset($gRev['time']) ? \Carbon\Carbon::createFromTimestamp($gRev['time']) : now(),
                            ]
                        );
                        $syncedCount++;
                    }
                }

                $allReviews = $this->reviewRepo->getAll()->map(function ($r) {
                    return [
                        'id' => $r->id,
                        'name' => $r->reviewer_name,
                        'rating' => (int) $r->rating,
                        'comment' => $r->review_text ?? ($r->comment ?? ''),
                        'review_text' => $r->review_text ?? ($r->comment ?? ''),
                        'role' => $r->reviewer_title ?: 'Pelanggan',
                        'location' => $r->reviewer_location ?: 'Yogyakarta',
                        'time' => $r->reviewed_at ? $r->reviewed_at->diffForHumans() : 'Baru saja',
                        'source' => $r->source === 'google' ? 'Google Review' : 'Manual Review',
                        'is_active' => (bool) $r->is_active,
                        'sort_order' => (int) ($r->sort_order ?? 1),
                    ];
                })->toArray();

                return response()->json([
                    'success' => true,
                    'message' => "Berhasil menarik {$syncedCount} ulasan dan rating terbaru dari Google Maps.",
                    'settings' => $settings,
                    'reviews' => $allReviews,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data dari Google API: ' . ($data['error_message'] ?? ($data['status'] ?? 'Unknown Error')),
            ], 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghubungi Google API: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * SEO & Meta Settings Management Screen.
     */
    public function seo()
    {
        $seoData = $this->siteSettingRepo->get('seo', config('seo', []));
        $mediaLibrary = $this->getMediaLibrary();

        return view('admin.seo', compact('seoData', 'mediaLibrary'));
    }

    /**
     * Update SEO & Meta Settings (POST /admin/seo).
     */
    public function seoUpdate(Request $request)
    {
        $validated = $request->validate([
            'seo' => 'required|array',
            'seo.meta_title' => 'nullable|string|max:255',
            'seo.meta_description' => 'nullable|string|max:1000',
            'seo.canonical_url' => 'nullable|string|max:500',
            'seo.robots' => ['nullable', 'string', \Illuminate\Validation\Rule::in(['index, follow', 'noindex, nofollow', 'index,follow', 'noindex,nofollow'])],
            'seo.meta_keywords' => 'nullable|string|max:500',
            'seo.og_title' => 'nullable|string|max:255',
            'seo.og_description' => 'nullable|string|max:1000',
            'seo.og_image' => 'nullable|string|max:500',
        ]);

        $existingSeo = $this->siteSettingRepo->get('seo', config('seo', []));
        $seoInput = $validated['seo'];
        if (array_key_exists('canonical_url', $seoInput)) {
            $seoInput['canonical_url'] = $this->sanitizeExternalUrl($seoInput['canonical_url']);
        }
        if (array_key_exists('og_image', $seoInput)) {
            $seoInput['og_image'] = $this->sanitizeImageUrl($seoInput['og_image']);
        }

        $mergedSeo = array_merge($existingSeo, $seoInput);

        $this->siteSettingRepo->set('seo', $mergedSeo);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Pengaturan SEO & Meta berhasil disimpan ke database.',
                'seo' => $mergedSeo,
            ]);
        }

        return redirect()->route('admin.seo')->with('success', 'Pengaturan SEO & Meta berhasil disimpan ke database.');
    }

    /**
     * Site & Contact Settings Management Screen.
     */
    public function settings()
    {
        $settingsData = $this->siteSettingRepo->get('site', config('site', []));
        if (empty($settingsData['contacts']) || !is_array($settingsData['contacts'])) {
            $settingsData['contacts'] = config('site.contacts', []);
        }

        $authUser = auth()->user();
        if ($authUser) {
            if (!isset($settingsData['admin_user']) || !is_array($settingsData['admin_user'])) {
                $settingsData['admin_user'] = [];
            }
            $settingsData['admin_user']['name'] = $authUser->name;
            if (!empty($authUser->avatar)) {
                $settingsData['admin_user']['avatar_image'] = $authUser->avatar;
            }
            if (!empty($authUser->email)) {
                $settingsData['admin_user']['email'] = $authUser->email;
            }
        }

        $mediaLibrary = $this->getMediaLibrary();

        return view('admin.settings', compact('settingsData', 'mediaLibrary'));
    }

    /**
     * Update Site & Contact Settings (POST /admin/settings).
     */
    public function settingsUpdate(Request $request)
    {
        $validated = $request->validate([
            'site' => 'required|array',
            // Brand
            'site.brand' => 'nullable|array',
            'site.brand.site_name' => 'nullable|string|max:255',
            'site.brand.name' => 'nullable|string|max:255',
            'site.brand.short_name' => 'nullable|string|max:255',
            'site.brand.tagline' => 'nullable|string|max:500',
            'site.brand.description' => 'nullable|string|max:1000',
            'site.brand.tab_title_pattern' => 'nullable|string|max:255',
            'site.brand.logo_url' => 'nullable|string|max:500',
            'site.brand.favicon_url' => 'nullable|string|max:500',
            // Contact
            'site.contact' => 'nullable|array',
            'site.contact.whatsapp' => 'nullable|string|max:50',
            'site.contact.order_whatsapp' => 'nullable|string|max:50',
            'site.contact.admin_whatsapp' => 'nullable|string|max:50',
            'site.contact.cs_whatsapp' => 'nullable|string|max:50',
            'site.contact.phone' => 'nullable|string|max:50',
            'site.contact.office_phone' => 'nullable|string|max:50',
            'site.contact.email' => 'nullable|string|max:255',
            'site.contact.status' => 'nullable|string|max:50',
            // Contact Registry
            'site.contacts' => 'nullable|array',
            'site.contacts.*.id' => 'nullable|string|max:50',
            'site.contacts.*.key' => 'nullable|string|max:50',
            'site.contacts.*.name' => 'nullable|string|max:255',
            'site.contacts.*.division' => 'nullable|string|max:255',
            'site.contacts.*.type' => 'nullable|string|in:whatsapp,phone,email',
            'site.contacts.*.value' => 'nullable|string|max:255',
            'site.contacts.*.description' => 'nullable|string|max:500',
            'site.contacts.*.active' => 'nullable|boolean',
            'site.contacts.*.is_system' => 'nullable|boolean',
            // Social
            'site.social' => 'nullable|array',
            'site.social.instagram' => 'nullable|string|max:500',
            'site.social.tiktok' => 'nullable|string|max:500',
            'site.social.facebook' => 'nullable|string|max:500',
            // Website
            'site.website' => 'nullable|array',
            'site.website.url' => 'nullable|string|max:500',
            'site.website.copyright' => 'nullable|string|max:500',
            // Admin Panel
            'site.admin_panel' => 'nullable|array',
            'site.admin_panel.panel_name' => 'nullable|string|max:255',
            'site.admin_panel.badge_tag' => 'nullable|string|max:255',
            'site.admin_panel.footer_note' => 'nullable|string|max:500',
            // Admin User
            'site.admin_user' => 'nullable|array',
            'site.admin_user.avatar_image' => 'nullable|string|max:500',
            'site.admin_user.name' => 'nullable|string|max:255',
            'site.admin_user.role' => 'nullable|string|max:255',
            'site.admin_user.email' => 'nullable|string|max:255',
            'site.admin_user.phone' => 'nullable|string|max:50',
        ]);

        $existingSettings = $this->siteSettingRepo->get('site', config('site', []));
        $siteInput = $validated['site'];

        if (isset($siteInput['brand']['logo_url'])) {
            $siteInput['brand']['logo_url'] = $this->sanitizeImageUrl($siteInput['brand']['logo_url']);
        }
        if (isset($siteInput['brand']['favicon_url'])) {
            $siteInput['brand']['favicon_url'] = $this->sanitizeImageUrl($siteInput['brand']['favicon_url']);
        }
        if (isset($siteInput['website']['url'])) {
            $siteInput['website']['url'] = $this->sanitizeExternalUrl($siteInput['website']['url']);
        }
        if (isset($siteInput['social']['instagram'])) {
            $siteInput['social']['instagram'] = $this->sanitizeExternalUrl($siteInput['social']['instagram']);
        }
        if (isset($siteInput['social']['tiktok'])) {
            $siteInput['social']['tiktok'] = $this->sanitizeExternalUrl($siteInput['social']['tiktok']);
        }
        if (isset($siteInput['social']['facebook'])) {
            $siteInput['social']['facebook'] = $this->sanitizeExternalUrl($siteInput['social']['facebook']);
        }
        if (isset($siteInput['admin_user']['avatar_image'])) {
            $siteInput['admin_user']['avatar_image'] = $this->sanitizeImageUrl($siteInput['admin_user']['avatar_image']);
        }

        $mergedSettings = array_replace_recursive($existingSettings, $siteInput);

        if (isset($validated['site']['contacts']) && is_array($validated['site']['contacts'])) {
            $mergedSettings['contacts'] = $validated['site']['contacts'];
            foreach ($validated['site']['contacts'] as $c) {
                $cKey = $c['key'] ?? ($c['id'] ?? '');
                $cVal = $c['value'] ?? '';
                $cType = $c['type'] ?? '';
                if ($cKey === 'order_wa' || ($cType === 'whatsapp' && str_contains(strtolower($cKey), 'order'))) {
                    $mergedSettings['contact']['order_whatsapp'] = $cVal;
                } elseif ($cKey === 'admin_wa' || ($cType === 'whatsapp' && str_contains(strtolower($cKey), 'admin'))) {
                    $mergedSettings['contact']['admin_whatsapp'] = $cVal;
                    $mergedSettings['contact']['whatsapp'] = $cVal;
                } elseif ($cKey === 'cs_care') {
                    $mergedSettings['contact']['cs_whatsapp'] = $cVal;
                } elseif ($cKey === 'main_phone') {
                    $mergedSettings['contact']['phone'] = $cVal;
                } elseif ($cKey === 'office_phone') {
                    $mergedSettings['contact']['office_phone'] = $cVal;
                } elseif ($cKey === 'official_email') {
                    $mergedSettings['contact']['email'] = $cVal;
                }
            }
        }

        $this->siteSettingRepo->set('site', $mergedSettings);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Pengaturan Site & Contact Registry berhasil disimpan ke database.',
                'settings' => $mergedSettings,
            ]);
        }

        return redirect()->route('admin.settings')->with('success', 'Pengaturan Site & Contact Registry berhasil disimpan ke database.');
    }

    /**
     * Update Administrator Profile Avatar (POST /admin/profile/avatar).
     */
    public function avatarUpdate(Request $request)
    {
        $request->validate([
            'avatar' => 'nullable|string|max:500',
            'avatar_file' => 'nullable|file|image|mimes:jpeg,jpg,png,webp|max:2048',
        ]);

        $user = $request->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi login tidak valid / unauthorized.',
            ], 401);
        }

        $avatarPath = $request->input('avatar');

        if ($request->hasFile('avatar_file')) {
            $file = $request->file('avatar_file');
            $extension = strtolower($file->getClientOriginalExtension() ?: 'png');
            $filename = 'avatar_' . $user->id . '_' . time() . '_' . Str::random(8) . '.' . $extension;
            $stored = $file->storeAs('media', $filename, 'public');
            $avatarPath = 'storage/' . $stored;
        }

        // Gracefully persist to users table if column exists
        if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'avatar')) {
            $user->avatar = $avatarPath;
            $user->save();
        }

        // Also sync to site_settings for consistent presentation
        $existingSettings = $this->siteSettingRepo->get('site', config('site', []));
        if (isset($existingSettings['admin_user'])) {
            $existingSettings['admin_user']['avatar_image'] = $avatarPath;
            $this->siteSettingRepo->set('site', $existingSettings);
        }

        $avatarUrl = $avatarPath
            ? (str_starts_with($avatarPath, 'http') ? $avatarPath : asset(ltrim($avatarPath, '/')))
            : null;

        return response()->json([
            'success' => true,
            'message' => 'Avatar profil administrator berhasil diperbarui!',
            'avatar' => $avatarPath,
            'avatar_url' => $avatarUrl,
        ]);
    }

    /**
     * Update Administrator Profile Name (POST /admin/profile/name).
     */
    public function nameUpdate(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|min:2|max:255',
        ]);

        $user = $request->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi login tidak valid / unauthorized.',
            ], 401);
        }

        $user->name = trim($validated['name']);
        $user->save();

        // Legacy compatibility sync to site_settings
        try {
            $existingSettings = $this->siteSettingRepo->get('site', config('site', []));
            if (isset($existingSettings['admin_user'])) {
                $existingSettings['admin_user']['name'] = $user->name;
                $this->siteSettingRepo->set('site', $existingSettings);
            }
        } catch (\Throwable $e) {
            // Gracefully ignore site_settings sync failure to avoid rolling back canonical users.name
        }

        return response()->json([
            'success' => true,
            'message' => 'Nama administrator berhasil diperbarui!',
            'name' => $user->name,
        ]);
    }

    /**
     * Update Administrator Profile Email (POST /admin/profile/email).
     */
    public function emailUpdate(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi login tidak valid / unauthorized.',
            ], 401);
        }

        // Normalize email (lowercase and trimmed)
        if ($request->has('email')) {
            $request->merge([
                'email' => strtolower(trim((string) $request->input('email')))
            ]);
        }

        $validated = $request->validate([
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
        ]);

        $email = $validated['email'];
        $user->email = $email;
        $user->save();

        // Legacy compatibility sync to site_settings
        try {
            $existingSettings = $this->siteSettingRepo->get('site', config('site', []));
            if (isset($existingSettings['admin_user'])) {
                $existingSettings['admin_user']['email'] = $user->email;
                $this->siteSettingRepo->set('site', $existingSettings);
            }
        } catch (\Throwable $e) {
            // Gracefully ignore site_settings sync failure to avoid rolling back canonical users.email
        }

        return response()->json([
            'success' => true,
            'message' => 'Email login administrator berhasil diperbarui!',
            'email' => $user->email,
        ]);
    }

    /**
     * Update Administrator Profile Password (POST /admin/profile/password).
     */
    public function passwordUpdate(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi login tidak valid / unauthorized.',
            ], 401);
        }

        $validated = $request->validate([
            'current_password' => ['required', 'string', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.current_password' => 'Password lama tidak sesuai.',
            'current_password.required' => 'Password lama wajib diisi.',
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password baru minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak sesuai.',
        ]);

        $user->password = Hash::make($validated['password']);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Sandi berhasil diperbarui.',
        ]);
    }
}
