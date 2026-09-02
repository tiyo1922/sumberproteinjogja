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
     * Centralized Media Library list for Global Media Picker.
     */
    private function getMediaLibrary(): array
    {
        $items = [
            [
                'id' => 1,
                'filename' => 'hero-1.jpg',
                'path' => 'images/hero-1.jpg',
                'title' => 'Daging Sapi & Ayam Segar (Hero)',
                'resolution' => '1920 × 1080',
                'ratio' => '16:9',
                'size' => '342 KB',
                'is_recommended' => true,
            ],
            [
                'id' => 2,
                'filename' => 'hero-2.jpg',
                'path' => 'images/hero-2.jpg',
                'title' => 'Seafood & Ikan Fillet Pilihan (Hero)',
                'resolution' => '1920 × 1080',
                'ratio' => '16:9',
                'size' => '415 KB',
                'is_recommended' => true,
            ],
            [
                'id' => 3,
                'filename' => 'hero-3.jpg',
                'path' => 'images/hero-3.jpg',
                'title' => 'Ready to Cook & Sayur Siap Masak (Hero)',
                'resolution' => '1920 × 1080',
                'ratio' => '16:9',
                'size' => '388 KB',
                'is_recommended' => true,
            ],
            [
                'id' => 4,
                'filename' => 'cat-daging.jpg',
                'path' => 'images/cat-daging.jpg',
                'title' => 'Daging Sapi Slice & Sengkel Rawon',
                'resolution' => '800 × 600',
                'ratio' => '4:3',
                'size' => '295 KB',
                'is_recommended' => true,
            ],
            [
                'id' => 5,
                'filename' => 'cat-ayam.jpg',
                'path' => 'images/cat-ayam.jpg',
                'title' => 'Ayam Broiler & Dada Fillet Boneless',
                'resolution' => '800 × 600',
                'ratio' => '4:3',
                'size' => '310 KB',
                'is_recommended' => true,
            ],
            [
                'id' => 6,
                'filename' => 'cat-ikan.jpg',
                'path' => 'images/cat-ikan.jpg',
                'title' => 'Ikan Gurame & Dori Fillet Segar',
                'resolution' => '800 × 600',
                'ratio' => '4:3',
                'size' => '360 KB',
                'is_recommended' => true,
            ],
            [
                'id' => 7,
                'filename' => 'cat-sayur.jpg',
                'path' => 'images/cat-sayur.jpg',
                'title' => 'Sayuran Segar Organik & Siap Olah',
                'resolution' => '800 × 600',
                'ratio' => '4:3',
                'size' => '280 KB',
                'is_recommended' => true,
            ],
            [
                'id' => 8,
                'filename' => 'know-thawing.jpg',
                'path' => 'images/know-thawing.jpg',
                'title' => 'Dapur & Edukasi Thawing Higienis',
                'resolution' => '1200 × 800',
                'ratio' => '3:2',
                'size' => '420 KB',
                'is_recommended' => true,
            ],
            [
                'id' => 9,
                'filename' => 'prod-beef-slice.jpg',
                'path' => 'images/prod-beef-slice.jpg',
                'title' => 'Daging Sapi Shortplate Slice 500g',
                'resolution' => '1200 × 900',
                'ratio' => '4:3',
                'size' => '325 KB',
                'is_recommended' => true,
            ],
            [
                'id' => 10,
                'filename' => 'prod-ayam-bumbu.jpg',
                'path' => 'images/prod-ayam-bumbu.jpg',
                'title' => 'Ayam Ungkep Bumbu Kuning Lengkuas',
                'resolution' => '1200 × 900',
                'ratio' => '4:3',
                'size' => '315 KB',
                'is_recommended' => true,
            ],
            [
                'id' => 11,
                'filename' => 'prod-ikan-gurame.jpg',
                'path' => 'images/prod-ikan-gurame.jpg',
                'title' => 'Fillet Ikan Gurame Segar Bersih',
                'resolution' => '1200 × 900',
                'ratio' => '4:3',
                'size' => '340 KB',
                'is_recommended' => true,
            ],
            [
                'id' => 12,
                'filename' => 'prod-sayur-mix.jpg',
                'path' => 'images/prod-sayur-mix.jpg',
                'title' => 'Paket Sayur Sop Komplit Higienis',
                'resolution' => '1200 × 900',
                'ratio' => '4:3',
                'size' => '290 KB',
                'is_recommended' => true,
            ],
        ];

        // Dynamically scan uploaded files from storage/app/public/media/
        $disk = Storage::disk('public');
        if ($disk->exists('media')) {
            $files = $disk->files('media');
            rsort($files);
            foreach ($files as $idx => $file) {
                $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                if (in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'svg', 'ico'])) {
                    $filename = basename($file);
                    $sizeKb = round($disk->size($file) / 1024);
                    $items[] = [
                        'id' => 'upload_' . ($idx + 1) . '_' . md5($file),
                        'filename' => $filename,
                        'path' => 'storage/' . $file,
                        'url' => asset('storage/' . $file),
                        'title' => $filename,
                        'resolution' => 'Custom Upload',
                        'ratio' => 'Auto',
                        'size' => $sizeKb . ' KB',
                        'is_recommended' => false,
                        'is_deletable' => true,
                    ];
                }
            }
        }

        // Ensure all preset items have is_deletable = false
        foreach ($items as &$it) {
            if (!isset($it['is_deletable'])) {
                $it['is_deletable'] = false;
            }
        }
        unset($it);

        return $items;
    }

    /**
     * Upload Media file to centralized Public Media Storage (storage/app/public/media/).
     */
    public function mediaUpload(Request $request)
    {
        $request->validate([
            'image' => 'required|file|mimes:jpeg,jpg,png,webp,svg,ico|max:5120',
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
            'message' => 'File media bawaan sistem tidak dapat dihapus atau file tidak ditemukan di server.',
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
                'images/hero-1.jpg',
                'images/hero-2.jpg',
                'images/hero-3.jpg',
            ],
        ]);

        $heroTrustItems = $this->siteSettingRepo->get('hero_trust_items', [
            ['id' => 1, 'text' => '100% Halal & Higienis', 'is_active' => true, 'sort_order' => 1],
            ['id' => 2, 'text' => 'Standar Rantai Dingin (Cold Chain)', 'is_active' => true, 'sort_order' => 2],
            ['id' => 3, 'text' => 'Pengiriman Cepat Se-Jogja', 'is_active' => true, 'sort_order' => 3],
        ]);

        $heroPartners = $this->siteSettingRepo->get('hero_partners', [
            'badge' => 'Kepercayaan Mitra',
            'title' => 'Telah Dipercaya Restoran, Cafe, Catering & Rumah Tangga di Jogja',
            'partners' => [
                ['id' => 1, 'name' => 'Restoran & Cafe Jogja', 'logo' => 'images/mitra-placeholder.png', 'is_active' => true, 'sort_order' => 1],
                ['id' => 2, 'name' => 'Katering & Horeka', 'logo' => 'images/mitra-placeholder.png', 'is_active' => true, 'sort_order' => 2],
                ['id' => 3, 'name' => 'Rumah Tangga Jogja', 'logo' => 'images/mitra-placeholder.png', 'is_active' => true, 'sort_order' => 3],
            ]
        ]);

        $drafts = [
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
                    'images/hero-1.jpg',
                    'images/hero-2.jpg',
                    'images/hero-3.jpg',
                    'images/cat-daging.jpg',
                ],
                'trust_items' => is_array($heroTrustItems) ? array_map(function ($item) {
                    return [
                        'id' => $item['id'] ?? 1,
                        'text' => $item['text'] ?? '',
                        'active' => $item['is_active'] ?? ($item['active'] ?? true),
                        'sort_order' => $item['sort_order'] ?? 1,
                    ];
                }, $heroTrustItems) : [],
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
                    'images/hero-2.jpg',
                    'images/hero-3.jpg',
                    'images/hero-1.jpg',
                ],
                'trust_items' => [
                    ['id' => 1, 'text' => 'Higienis & Segar', 'active' => true, 'sort_order' => 1],
                    ['id' => 2, 'text' => 'Ready to Cook', 'active' => true, 'sort_order' => 2],
                    ['id' => 3, 'text' => 'Free Delivery Sleman', 'active' => true, 'sort_order' => 3],
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
                    'images/hero-3.jpg',
                    'images/hero-1.jpg',
                ],
                'trust_items' => [
                    ['id' => 1, 'text' => 'Harga Grosir & Ecer', 'active' => true, 'sort_order' => 1],
                    ['id' => 2, 'text' => 'Garansi Kualitas', 'active' => true, 'sort_order' => 2],
                    ['id' => 3, 'text' => 'Sameday Delivery', 'active' => true, 'sort_order' => 3],
                ],
                'updated_at' => 'Preset Layout',
            ],
        ];

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
            'hero.subtitle' => 'nullable|string',
            'hero.description' => 'nullable|string',
            'hero.whatsapp_button_text' => 'nullable|string|max:255',
            'hero.primary_cta_text' => 'nullable|string|max:255',
            'hero.primary_cta_link' => 'nullable|string|max:255',
            'hero.primary_cta_contact' => 'nullable|string|max:50',
            'hero.catalog_button_text' => 'nullable|string|max:255',
            'hero.secondary_cta_text' => 'nullable|string|max:255',
            'hero.secondary_cta_link' => 'nullable|string|max:255',
            'hero.secondary_cta_contact' => 'nullable|string|max:50',
            'hero.images' => 'nullable|array',
            'trust_items' => 'nullable|array',
            'partners' => 'nullable|array',
            'partners.title' => 'nullable|string|max:255',
            'partners.badge' => 'nullable|string|max:255',
            'partners.partners' => 'nullable|array',
        ]);

        if (isset($validated['hero'])) {
            $currentHero = $this->siteSettingRepo->get('hero', []);
            $mergedHero = array_merge($currentHero, $validated['hero']);
            if (isset($validated['hero']['title'])) {
                $mergedHero['title'] = $validated['hero']['title'];
            }
            if (isset($validated['hero']['subtitle'])) {
                $mergedHero['subtitle'] = $validated['hero']['subtitle'];
                $mergedHero['description'] = $validated['hero']['subtitle'];
            }
            $this->siteSettingRepo->set('hero', $mergedHero);
        }

        if (isset($validated['trust_items'])) {
            $this->siteSettingRepo->set('hero_trust_items', $validated['trust_items']);
        }

        if (isset($validated['partners'])) {
            $currentPartners = $this->siteSettingRepo->get('hero_partners', []);
            $mergedPartners = array_merge($currentPartners, $validated['partners']);
            $this->siteSettingRepo->set('hero_partners', $mergedPartners);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Pengaturan Hero, Trust Checklist, dan Mitra berhasil disimpan ke database!',
                'hero' => $this->siteSettingRepo->get('hero'),
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
                return $p->category_id == $cat->id && $p->is_active;
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
                'image' => $cat->image ?? 'images/cat-daging.jpg',
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
        $validated['image'] = $validated['image'] ?? 'images/cat-daging.jpg';
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
        $validated = $request->validate([
            'orders' => 'required|array',
        ]);

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
                'image' => $p->image ?? 'images/prod-beef-slice.jpg',
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
        $validated['image'] = $validated['image'] ?? 'images/prod-beef-slice.jpg';
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
        $validated = $request->validate([
            'orders' => 'required|array',
        ]);

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
        $validated = $request->validate([
            'orders' => 'required|array',
        ]);

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
        $benefitsInput = $request->input('benefits');
        $qualityInput = $request->input('quality');

        if (is_array($benefitsInput)) {
            $this->siteSettingRepo->set('benefits', $benefitsInput);
        }

        if (is_array($qualityInput)) {
            $this->siteSettingRepo->set('quality_standards', $qualityInput);
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
                'image' => $a->image ?? 'images/know-thawing.jpg',
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
        $validated = $request->validate([
            'orders' => 'required|array',
        ]);

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
            'image' => $validated['image'] ?? 'images/know-thawing.jpg',
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
        $validated = $request->validate([
            'orders' => 'required|array',
        ]);

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
            'actual_footer' => 'nullable|array',
            'footer' => 'nullable|array',
        ]);

        if (isset($validated['location'])) {
            $this->siteSettingRepo->set('location', $validated['location']);
        }

        $footerPayload = $validated['actual_footer'] ?? ($validated['footer'] ?? null);
        if ($footerPayload !== null) {
            $this->siteSettingRepo->set('footer', $footerPayload);
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
        $validated = $request->validate([
            'orders' => 'required|array',
        ]);

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
        $mergedSeo = array_merge($existingSeo, $validated['seo']);

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
        $mergedSettings = array_replace_recursive($existingSettings, $validated['site']);

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
