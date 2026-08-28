<?php

namespace App\Http\Controllers;

use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Repositories\Contracts\KnowledgeRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\ReviewRepositoryInterface;
use App\Repositories\Contracts\SiteSettingRepositoryInterface;
use App\Services\KnowledgeArticleParser;

class LandingController
{
    protected CategoryRepositoryInterface $categoryRepo;
    protected ProductRepositoryInterface $productRepo;
    protected KnowledgeRepositoryInterface $knowledgeRepo;
    protected ReviewRepositoryInterface $reviewRepo;
    protected SiteSettingRepositoryInterface $siteSettingRepo;

    /**
     * Inject canonical repository contracts.
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
     * Display the canonical public Customer Landing Page.
     */
    public function index()
    {
        // 1. Database-Driven Site, Location & Footer Settings
        $location = $this->siteSettingRepo->get('location', config('location'));
        $footer = $this->siteSettingRepo->get('footer', []);

        $site = config('site');
        if (!empty($footer['brand_title'])) {
            $site['brand']['name'] = $footer['brand_title'];
            $site['brand']['short_name'] = $footer['brand_title'];
        }
        if (!empty($footer['brand_desc'])) {
            $site['brand']['description'] = $footer['brand_desc'];
        }
        if (!empty($footer['copyright'])) {
            $site['website']['copyright'] = $footer['copyright'];
        }
        if (!empty($footer['outlet_phone'])) {
            $site['contact']['phone'] = $footer['outlet_phone'];
        }
        if (!empty($footer['social_links']) && is_array($footer['social_links'])) {
            foreach ($footer['social_links'] as $soc) {
                $u = $soc['url'] ?? '';
                if (str_contains($u, 'instagram.com')) $site['social']['instagram'] = $u;
                if (str_contains($u, 'tiktok.com')) $site['social']['tiktok'] = $u;
                if (str_contains($u, 'facebook.com')) $site['social']['facebook'] = $u;
                if (str_contains($u, 'wa.me') || str_contains($u, 'whatsapp.com')) {
                    $cleanWa = preg_replace('/[^0-9]/', '', $u);
                    if ($cleanWa) {
                        $site['contact']['admin_whatsapp'] = $cleanWa;
                        $site['contact']['whatsapp'] = $cleanWa;
                    }
                }
            }
        }

        $seo = config('seo');

        $storeInfo = [
            'name' => $footer['brand_title'] ?? ($site['brand']['name'] ?? 'Sumber Protein Jogja'),
            'tagline' => $site['brand']['tagline'] ?? 'Bahan Masak Siap Olah, Tinggal Masak.',
            'address' => $location['address']['full'] ?? ($footer['outlet_address'] ?? ''),
            'hours' => $location['operational_hours']['display'] ?? ($footer['outlet_hours'] ?? ''),
            'phone' => $footer['outlet_phone'] ?? ($site['contact']['phone'] ?? ''),
            'whatsapp' => $site['contact']['admin_whatsapp'] ?? '',
            'email' => $site['contact']['email'] ?? '',
            'instagram' => $site['social']['instagram'] ?? '',
            'maps_url' => $location['maps']['link'] ?? '',
        ];

        // 2. Database-Driven Hero & Trust Configuration
        $heroSetting = $this->siteSettingRepo->get('hero', []);
        $heroTrustItems = $this->siteSettingRepo->get('hero_trust_items', []);
        $heroPartners = $this->siteSettingRepo->get('hero_partners', []);

        $hero = [
            'id' => 1,
            'name' => 'Hero Utama',
            'status' => 'Aktif',
            'badge' => $heroSetting['badge'] ?? 'Pusat Bahan Segar & Frozen Jogja',
            'title' => $heroSetting['title'] ?? 'Belanja Daging Sapi, Ayam, Seafood & Sayuran Siap Olah Praktis',
            'headline_prefix' => $heroSetting['headline_prefix'] ?? 'Bahan Masak',
            'highlight' => $heroSetting['highlight'] ?? 'Siap Olah',
            'headline_suffix' => $heroSetting['headline_suffix'] ?? ', Tinggal Masak.',
            'subtitle' => $heroSetting['subtitle'] ?? ($heroSetting['description'] ?? 'Pilihan tepat keluarga & pengusaha kuliner di Yogyakarta. Produk higienis dengan standar cold-chain terjamin, dipotong rapi, dan dikirim aman sampai ke dapur Anda.'),
            'description' => $heroSetting['subtitle'] ?? ($heroSetting['description'] ?? 'Pilihan tepat keluarga & pengusaha kuliner di Yogyakarta. Produk higienis dengan standar cold-chain terjamin, dipotong rapi, dan dikirim aman sampai ke dapur Anda.'),
            'primary_cta_text' => $heroSetting['whatsapp_button_text'] ?? ($heroSetting['primary_cta_text'] ?? 'Belanja Sekarang'),
            'primary_cta_link' => $heroSetting['primary_cta_link'] ?? '#produk',
            'secondary_cta_text' => $heroSetting['catalog_button_text'] ?? ($heroSetting['secondary_cta_text'] ?? 'Lihat Produk'),
            'secondary_cta_link' => $heroSetting['secondary_cta_link'] ?? '#kategori',
            'images' => $heroSetting['images'] ?? [
                'images/hero-1.jpg',
                'images/hero-2.jpg',
                'images/hero-3.jpg',
            ],
            'trust_items' => is_array($heroTrustItems) ? array_map(function ($item) {
                return [
                    'id' => $item['id'] ?? 1,
                    'text' => $item['text'] ?? '',
                    'active' => $item['is_active'] ?? ($item['active'] ?? true),
                ];
            }, $heroTrustItems) : [],
            'partners' => $heroPartners,
        ];

        // 3. Database-Driven Categories
        $categorySection = [
            'label' => 'Kategori Utama',
            'title' => 'Mau Masak Apa Hari Ini?',
            'subtitle' => 'Pilih bahan masak sesuai kebutuhanmu. Dari potongan daging segar, ayam bumbu, ikan laut, hingga sayuran siap cemplung.'
        ];
        $categories = $this->categoryRepo->getActiveWithProductCount();

        // 4. Database-Driven Products & Flash Sale
        $products = $this->productRepo->getActiveCatalog();
        $flashSaleSetting = $this->siteSettingRepo->get('flash_sale', [
            'enabled' => false,
            'end_at' => null,
            'title' => 'Flash Sale Terbatas!',
            'subtitle' => 'Dapatkan potongan harga spesial untuk produk protein pilihan hari ini. Stok terbatas!',
        ]);

        $isFlashSaleEligible = false;
        $flashSaleProducts = collect([]);

        if (!empty($flashSaleSetting['enabled']) && !empty($flashSaleSetting['end_at'])) {
            try {
                $endAt = \Carbon\Carbon::parse($flashSaleSetting['end_at']);
                if ($endAt->isFuture()) {
                    $eligibleProds = $this->productRepo->getFlashSaleProducts();
                    if ($eligibleProds->count() > 0) {
                        $isFlashSaleEligible = true;
                        $flashSaleProducts = $eligibleProds;
                    }
                }
            } catch (\Exception $e) {
                $isFlashSaleEligible = false;
            }
        }

        $flashSaleSetting['is_active'] = $isFlashSaleEligible;

        // 5. Database-Driven Value Propositions & Quality Standards
        $benefitsConfig = $this->siteSettingRepo->get('benefits', []);
        $benefitsSection = [
            'badge' => $benefitsConfig['section_badge'] ?? 'Kenapa Memilih Kami',
            'title' => $benefitsConfig['section_title'] ?? 'Lebih Praktis, Lebih Siap',
            'subtitle' => $benefitsConfig['section_subtitle'] ?? 'Komitmen kami menghadirkan bahan makanan segar dan frozen bermutu tinggi untuk memudahkan dapur rumah tangga dan operasional usaha Anda di Yogyakarta.',
        ];
        $benefits = $benefitsConfig['items'] ?? [];

        $qualityConfig = $this->siteSettingRepo->get('quality_standards', []);
        $qualitySection = [
            'badge' => $qualityConfig['section_badge'] ?? 'Standar Mutu',
            'title' => $qualityConfig['section_title'] ?? 'Mengenal Standar Produk Kami',
            'subtitle' => $qualityConfig['section_subtitle'] ?? 'Setiap produk yang keluar dari fasilitas penyimpanan Sumber Protein Jogja melewati proses seleksi ketat untuk menjamin keamanan pangan keluarga Anda.',
        ];
        $productKnowledge = $qualityConfig['items'] ?? [];

        // 6. Database-Driven Educational Knowledge Articles (Max 6 for Landing Page)
        $knowledgeSection = [
            'badge' => 'Dapur & Knowledge',
            'title' => 'Tips & Panduan Olah Bahan Masak',
            'subtitle' => 'Pelajari cara menyimpan daging agar tahan lama, teknik marinasi bumbu meresap, dan panduan memilih seafood segar dari ahlinya.'
        ];

        $rawArticles = $this->knowledgeRepo->getPublishedArticles(6);
        $badgeClassMap = [
            1 => 'badge-frozen',
            2 => 'badge-ready',
            3 => 'badge-primary',
            4 => 'badge-accent',
            5 => 'badge-bulk',
            6 => 'badge-fresh',
        ];

        $knowledgeArticles = $rawArticles->map(function ($article) use ($badgeClassMap) {
            $catId = $article->category_id ?? 1;
            $htmlContent = KnowledgeArticleParser::renderBlocksToHtml($article->content ?? []);

            return [
                'id' => $article->id,
                'title' => $article->title,
                'slug' => $article->slug,
                'category_id' => $catId,
                'category' => $article->category->name ?? 'Edukasi Dapur',
                'badge_class' => $badgeClassMap[$catId] ?? 'badge-primary',
                'read_time' => '3 menit baca',
                'image' => $article->image ?? 'images/know-thawing.jpg',
                'excerpt' => $article->excerpt ?? '',
                'content' => $htmlContent ?: ($article->excerpt ?? ''),
                'date' => $article->created_at ? $article->created_at->format('d M Y') : '',
            ];
        })->toArray();

        // 7. Database-Driven Customer Testimonials (Dual-Mode Source Switch)
        $reviewSettings = $this->siteSettingRepo->get('review_settings', [
            'review_mode' => 'manual',
            'google_place_id' => null,
            'google_rating' => null,
            'google_total_reviews' => null,
            'last_synced_at' => null,
        ]);

        $mode = $reviewSettings['review_mode'] ?? 'manual';
        if ($mode === 'google' && !empty($reviewSettings['google_place_id'])) {
            $dbReviews = $this->reviewRepo->getBySource('google', true);
        } else {
            $dbReviews = $this->reviewRepo->getBySource('manual', true);
        }

        $testimonials = $dbReviews->map(function ($review) {
            $roleLocation = $review->reviewer_title ?: 'Pelanggan';
            if (!empty($review->reviewer_location)) {
                $roleLocation .= ', ' . $review->reviewer_location;
            }

            return [
                'id' => $review->id,
                'name' => $review->reviewer_name,
                'role' => $roleLocation,
                'rating' => (int) $review->rating,
                'date' => $review->reviewed_at ? $review->reviewed_at->diffForHumans() : 'Baru saja',
                'review' => $review->review_text ?? ($review->comment ?? ''),
                'avatar' => $review->initials,
                'source' => $review->source === 'google' ? 'Google Review' : 'Ulasan Pelanggan',
            ];
        })->toArray();

        return view('landing', compact(
            'site',
            'seo',
            'location',
            'hero',
            'categorySection',
            'categories',
            'products',
            'flashSaleSetting',
            'flashSaleProducts',
            'benefitsSection',
            'benefits',
            'knowledgeSection',
            'knowledgeArticles',
            'qualitySection',
            'productKnowledge',
            'testimonials',
            'storeInfo',
            'footer'
        ));
    }
}
