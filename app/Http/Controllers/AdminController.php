<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Centralized Media Library list for Global Media Picker.
     */
    private function getMediaLibrary(): array
    {
        return [
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
    }

    /**
     * Contact settings mock data.
     */
    private function getContactSettings(): array
    {
        return [
            'order_whatsapp' => '6281234567891',
            'admin_whatsapp' => '6281234567890',
            'cs_whatsapp' => '6281234567892',
            'phone' => '(0274) 889977',
            'status' => 'Aktif',
        ];
    }

    /**
     * Display the Admin CMS Dashboard.
     */
    public function dashboard()
    {
        $stats = [
            'hero_active_count' => 1,
            'hero_drafts_count' => 2,
            'categories_count' => 6,
            'products_active_count' => 24,
            'knowledge_count' => 18,
            'knowledge_published_count' => 15,
            'knowledge_draft_count' => 3,
        ];

        $heroOverview = [
            'name' => 'Hero Draft 01',
            'status' => 'Aktif',
            'headline' => 'Bahan Masak Siap Olah, Tinggal Masak.',
            'images_count' => 4,
            'updated_at' => '17 Agustus 2026, 01:15 WIB',
        ];

        $landingStatus = [
            [
                'section' => 'Hero Slider',
                'detail' => 'Hero Draft 01 aktif (4 slideshow background, 3 trust checklist)',
                'status' => 'Aktif',
                'route' => 'admin.hero',
                'badge' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            ],
            [
                'section' => 'Kategori Produk',
                'detail' => '6 Kategori (1 Sistem + 5 Kategori Aktif)',
                'status' => 'Aktif',
                'route' => 'admin.kategori',
                'badge' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            ],
            [
                'section' => 'Katalog Produk',
                'detail' => '24 Produk aktif (10 featured di homepage customer)',
                'status' => 'Aktif',
                'route' => 'admin.produk',
                'badge' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            ],
            [
                'section' => 'Keunggulan & Mutu',
                'detail' => '4 Poin Keunggulan + 4 Standar Mutu Produk',
                'status' => 'Aktif',
                'route' => 'admin.keunggulan',
                'badge' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            ],
            [
                'section' => 'Knowledge & Tips',
                'detail' => '18 Artikel (15 Published, 3 Draft, Inline expand aktif)',
                'status' => 'Aktif',
                'route' => 'admin.knowledge',
                'badge' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            ],
            [
                'section' => 'Footer (Reviews & Lokasi)',
                'detail' => 'Google 4.9★ (180+ Review) + Outlet Sleman Maps',
                'status' => 'Aktif',
                'route' => 'admin.footer',
                'badge' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            ],
            [
                'section' => 'SEO & Meta',
                'detail' => 'Meta Title & OpenGraph terkonfigurasi',
                'status' => 'Aktif',
                'route' => 'admin.seo',
                'badge' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            ],
            [
                'section' => 'Site & Contact',
                'detail' => 'Brand & WhatsApp Settings terhubung',
                'status' => 'Aktif',
                'route' => 'admin.settings',
                'badge' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            ],
        ];

        $recentUpdates = [
            [
                'title' => 'Hero Draft 01 (Bahan Masak Siap Olah)',
                'type' => 'Hero diperbarui',
                'badge_type' => 'bg-amber-50 text-amber-700 border-amber-200',
                'time' => '17 Agustus 2026, 01:15 WIB',
                'author' => 'Admin'
            ],
            [
                'title' => 'Dada Ayam Fillet Boneless Clean',
                'type' => 'Produk diperbarui',
                'badge_type' => 'bg-blue-50 text-blue-700 border-blue-200',
                'time' => '17 Agustus 2026, 00:42 WIB',
                'author' => 'Admin'
            ],
            [
                'title' => '5 Tips Menyimpan Daging Beku Agar Tetap Segar & Higienis',
                'type' => 'Knowledge diperbarui',
                'badge_type' => 'bg-purple-50 text-purple-700 border-purple-200',
                'time' => '16 Agustus 2026, 21:15 WIB',
                'author' => 'Admin'
            ],
            [
                'title' => 'Daging Sapi Shortplate Slice Premium 500g',
                'type' => 'Produk diperbarui',
                'badge_type' => 'bg-blue-50 text-blue-700 border-blue-200',
                'time' => '16 Agustus 2026, 17:00 WIB',
                'author' => 'Admin'
            ],
            [
                'title' => 'Footer — Outlet Sleman & Jam Operasional',
                'type' => 'Footer diperbarui',
                'badge_type' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                'time' => '16 Agustus 2026, 14:10 WIB',
                'author' => 'Admin'
            ],
        ];

        return view('admin.dashboard', compact('stats', 'heroOverview', 'landingStatus', 'recentUpdates'));
    }

    /**
     * Hero Slider Management Screen.
     */
    public function hero()
    {
        $drafts = [
            [
                'id' => 1,
                'name' => 'Hero Draft 01',
                'status' => 'Aktif',
                'badge' => 'Penyedia Bahan Segar & Frozen Food Terpercaya di Jogja',
                'headline_prefix' => 'Bahan Masak',
                'highlight' => 'Siap Olah',
                'headline_suffix' => ', Tinggal Masak.',
                'description' => 'Daging, ayam, ikan, dan sayuran pilihan dalam bentuk frozen dan ready to cook untuk kebutuhan rumah tangga maupun pembelian curah.',
                'primary_cta_text' => 'Belanja Sekarang',
                'primary_cta_link' => '#produk',
                'secondary_cta_text' => 'Lihat Produk',
                'secondary_cta_link' => '#kategori',
                'images' => [
                    'images/hero-1.jpg',
                    'images/hero-2.jpg',
                    'images/hero-3.jpg',
                    'images/cat-daging.jpg',
                ],
                'trust_items' => [
                    ['id' => 1, 'text' => '100% Halal', 'active' => true],
                    ['id' => 2, 'text' => 'Cold Chain', 'active' => true],
                    ['id' => 3, 'text' => 'Kirim Se-Jogja', 'active' => true],
                ],
                'updated_at' => '17 Agustus 2026, 01:15 WIB',
            ],
            [
                'id' => 2,
                'name' => 'Hero Draft 02',
                'status' => 'Nonaktif',
                'badge' => 'Protein Segar & Siap Saji Higienis',
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
                    ['id' => 1, 'text' => 'Higienis & Segar', 'active' => true],
                    ['id' => 2, 'text' => 'Ready to Cook', 'active' => true],
                    ['id' => 3, 'text' => 'Free Delivery Sleman', 'active' => true],
                ],
                'updated_at' => '16 Agustus 2026, 20:30 WIB',
            ],
            [
                'id' => 3,
                'name' => 'Hero Draft 03',
                'status' => 'Nonaktif',
                'badge' => 'Fresh & Frozen Food Kualitas Restoran',
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
                    ['id' => 1, 'text' => 'Harga Grosir & Ecer', 'active' => true],
                    ['id' => 2, 'text' => 'Garansi Kualitas', 'active' => true],
                    ['id' => 3, 'text' => 'Sameday Delivery', 'active' => true],
                ],
                'updated_at' => '15 Agustus 2026, 14:10 WIB',
            ],
        ];

        $mediaLibrary = $this->getMediaLibrary();

        return view('admin.hero', compact('drafts', 'mediaLibrary'));
    }

    /**
     * Master Category Section Settings (Header).
     */
    public function getCategorySectionSettings()
    {
        return [
            'label' => 'Kategori Utama',
            'title' => 'Mau Masak Apa Hari Ini?',
            'subtitle' => 'Pilih bahan masak sesuai kebutuhanmu. Dari potongan daging segar, ayam bumbu, ikan laut, hingga sayuran siap cemplung.'
        ];
    }

    /**
     * Master Categories List (Source of Truth).
     */
    public function getCategories()
    {
        return [
            [
                'id' => 1,
                'name' => 'Daging Sapi',
                'slug' => 'daging-sapi',
                'subtitle' => 'Slice, Sengkel, Ribeye & Giling',
                'badge' => 'Sertifikasi Halal',
                'color' => 'orange',
                'image' => 'images/cat-daging.jpg',
                'description' => 'Daging sapi segar & frozen potongan higienis tanpa pengawet.',
                'order' => 1,
                'status' => 'active_landing',
                'is_system' => false,
            ],
            [
                'id' => 2,
                'name' => 'Ayam Segar & Olahan',
                'slug' => 'ayam-segar',
                'subtitle' => 'Fillet, Parting, Utuh & Ungkep',
                'badge' => 'Potong Segar Tiap Subuh',
                'color' => 'yellow',
                'image' => 'images/cat-ayam.jpg',
                'description' => 'Ayam potong higienis standar cold-chain, plain maupun berbumbu.',
                'order' => 2,
                'status' => 'active_landing',
                'is_system' => false,
            ],
            [
                'id' => 3,
                'name' => 'Ikan & Seafood',
                'slug' => 'ikan-seafood',
                'subtitle' => 'Salmon, Gurame, Dori & Udang',
                'badge' => 'Segar Beku Kapal',
                'color' => 'blue',
                'image' => 'images/cat-ikan.jpg',
                'description' => 'Fillet tanpa duri dan ikan utuh segar beku kaya nutrisi omega-3.',
                'order' => 3,
                'status' => 'active_landing',
                'is_system' => false,
            ],
            [
                'id' => 4,
                'name' => 'Sayuran Siap Olah',
                'slug' => 'sayuran-siap-olah',
                'subtitle' => 'Sayur Sup, Capcay & Sayur Segar',
                'badge' => 'Bersih Tinggal Cemplung',
                'color' => 'green',
                'image' => 'images/cat-sayur.jpg',
                'description' => 'Sayuran organik & hidroponik cuci bersih praktis untuk masakan harian.',
                'order' => 4,
                'status' => 'active_landing',
                'is_system' => false,
            ],
            [
                'id' => 5,
                'name' => 'Frozen Food & Olahan',
                'slug' => 'frozen-food',
                'subtitle' => 'Nugget, Sosis, Bakso & Olahan',
                'badge' => 'Higienis Siap Masak',
                'color' => 'purple',
                'image' => 'images/prod-ayam-bumbu.jpg',
                'description' => 'Olahan daging dan ayam siap saji praktis untuk bekal keluarga.',
                'order' => 5,
                'status' => 'active_catalog',
                'is_system' => false,
            ],
        ];
    }

    /**
     * Kategori Produk Management Screen.
     */
    public function kategori()
    {
        $categorySection = $this->getCategorySectionSettings();
        $categories = $this->getCategories();
        $products = $this->getProducts();
        $mediaLibrary = $this->getMediaLibrary();

        // Calculate dynamic product count from active products
        foreach ($categories as &$cat) {
            $activeCount = count(array_filter($products, function($p) use ($cat) {
                return ($p['category_id'] ?? null) == $cat['id'] && ($p['status'] ?? 'Aktif') === 'Aktif';
            }));
            $cat['products_count'] = $activeCount;
            $cat['count'] = $activeCount . '+ Variasi';
        }
        unset($cat);

        return view('admin.kategori', compact('categorySection', 'categories', 'products', 'mediaLibrary'));
    }

    /**
     * Master Products List with Relational category_id.
     */
    public function getProducts()
    {
        return [
            [
                'id' => 1,
                'name' => 'Daging Sapi Shortplate Slice Premium',
                'category_id' => 1,
                'category' => 'Daging Sapi',
                'types' => ['Frozen', 'Plain', 'Curah'],
                'weight' => '500g',
                'weight_value' => 500,
                'unit' => 'gram',
                'price' => 58000,
                'status' => 'Aktif',
                'image' => 'images/prod-beef-slice.jpg',
                'description' => 'Irisan tipis 1.5mm daging sapi impor berlemak gurih, sangat cocok untuk sukiyaki, shabu-shabu, dan grill BBQ rumahan.',
                'whatsapp_destination' => 'admin',
            ],
            [
                'id' => 2,
                'name' => 'Dada Ayam Fillet Boneless Clean',
                'category_id' => 2,
                'category' => 'Ayam Segar & Olahan',
                'types' => ['Fresh', 'Plain'],
                'weight' => '1000g',
                'weight_value' => 1000,
                'unit' => 'gram',
                'price' => 46000,
                'status' => 'Aktif',
                'image' => 'images/cat-ayam.jpg',
                'description' => 'Dada ayam tanpa tulang dan tanpa kulit, tinggi protein dan rendah lemak. Cocok untuk program diet, steak ayam, atau olahan tumis.',
                'whatsapp_destination' => 'admin',
            ],
            [
                'id' => 3,
                'name' => 'Ayam Ungkep Bumbu Kuning Lengkuas',
                'category_id' => 2,
                'category' => 'Ayam Segar & Olahan',
                'types' => ['Ready to Cook', 'Berbumbu'],
                'weight' => '800g',
                'weight_value' => 800,
                'unit' => 'gram',
                'price' => 42000,
                'status' => 'Aktif',
                'image' => 'images/prod-ayam-bumbu.jpg',
                'description' => 'Ayam pejantan utuh potong 4 yang telah diungkep dengan rempah tradisional lengkap. Tinggal goreng atau bakar praktis.',
                'whatsapp_destination' => 'order',
            ],
            [
                'id' => 4,
                'name' => 'Fillet Ikan Gurame Segar Bersih',
                'category_id' => 3,
                'category' => 'Ikan & Seafood',
                'types' => ['Fresh', 'Plain'],
                'weight' => '500g',
                'weight_value' => 500,
                'unit' => 'gram',
                'price' => 38000,
                'status' => 'Aktif',
                'image' => 'images/prod-ikan-gurame.jpg',
                'description' => 'Daging gurame fillet tanpa duri, higienis dan tidak berbau tanah. Siap ditepungi, dibuat sup asam manis, atau asam pedas.',
                'whatsapp_destination' => 'admin',
            ],
            [
                'id' => 5,
                'name' => 'Paket Sayur Sop Komplit Higienis',
                'category_id' => 4,
                'category' => 'Sayuran Siap Olah',
                'types' => ['Ready to Cook', 'Fresh'],
                'weight' => '350g',
                'weight_value' => 350,
                'unit' => 'gram',
                'price' => 12000,
                'status' => 'Aktif',
                'image' => 'images/prod-sayur-mix.jpg',
                'description' => 'Kombinasi wortel impor, buncis, kentang, kol, dan seledri yang sudah dicuci bersih dan dipotong rapi. Termasuk bumbu racik.',
                'whatsapp_destination' => 'admin',
            ],
            [
                'id' => 6,
                'name' => 'Daging Sengkel Sapi Potong Rawon / Semur',
                'category_id' => 1,
                'category' => 'Daging Sapi',
                'types' => ['Fresh', 'Curah'],
                'weight' => '1000g',
                'weight_value' => 1000,
                'unit' => 'gram',
                'price' => 125000,
                'status' => 'Aktif',
                'image' => 'images/cat-daging.jpg',
                'description' => 'Daging bagian betis sapi dengan urat kenyal lembut, sangat gurih saat direbus lama untuk masakan rawon, soto, atau semur.',
                'whatsapp_destination' => 'order',
            ],
            [
                'id' => 7,
                'name' => 'Ikan Dori Fillet Premium Glazing Rendah',
                'category_id' => 3,
                'category' => 'Ikan & Seafood',
                'types' => ['Frozen', 'Plain', 'Curah'],
                'weight' => '1000g',
                'weight_value' => 1000,
                'unit' => 'gram',
                'price' => 48000,
                'status' => 'Aktif',
                'image' => 'images/cat-ikan.jpg',
                'description' => 'Daging ikan pangasius putih bersih dengan kadar glazing minimal, tekstur lembut tanpa aroma amis menyengat.',
                'whatsapp_destination' => 'admin',
            ],
            [
                'id' => 8,
                'name' => 'Paket Sayur Lodeh Racik Bumbu',
                'category_id' => 4,
                'category' => 'Sayuran Siap Olah',
                'types' => ['Ready to Cook'],
                'weight' => '400g',
                'weight_value' => 400,
                'unit' => 'gram',
                'price' => 14000,
                'status' => 'Aktif',
                'image' => 'images/cat-sayur.jpg',
                'description' => 'Paket komplit labu siam, terong ungu, kacang panjang, melinjo, daun melinjo, dan jagung manis siap masak bersama santan.',
                'whatsapp_destination' => 'admin',
            ],
            [
                'id' => 9,
                'name' => 'Daging Giling Sapi Spesial Low Fat',
                'category_id' => 1,
                'category' => 'Daging Sapi',
                'types' => ['Frozen', 'Plain'],
                'weight' => '500g',
                'weight_value' => 500,
                'unit' => 'gram',
                'price' => 62000,
                'status' => 'Aktif',
                'image' => 'images/prod-beef-slice.jpg',
                'description' => 'Daging sapi murni giling dengan rasio lemak kurang dari 10%, cocok untuk pasta bolognese, patty burger, dan bakso rumahan.',
                'whatsapp_destination' => 'admin',
            ],
            [
                'id' => 10,
                'name' => 'Paha Ayam Utuh Marinasi BBQ Smokey',
                'category_id' => 2,
                'category' => 'Ayam Segar & Olahan',
                'types' => ['Ready to Cook', 'Berbumbu'],
                'weight' => '500g',
                'weight_value' => 500,
                'unit' => 'gram',
                'price' => 36000,
                'status' => 'Aktif',
                'image' => 'images/prod-ayam-bumbu.jpg',
                'description' => 'Paha ayam bagian atas dan bawah yang dimarinasi saus barbeque gurih manis dengan aroma asap khas restoran panggang.',
                'whatsapp_destination' => 'order',
            ],
            [
                'id' => 11,
                'name' => 'Udang Vaname Kupas Bersih Ekor (PDTO)',
                'category_id' => 3,
                'category' => 'Ikan & Seafood',
                'types' => ['Frozen', 'Plain', 'Fresh'],
                'weight' => '500g',
                'weight_value' => 500,
                'unit' => 'gram',
                'price' => 55000,
                'status' => 'Aktif',
                'image' => 'images/hero-2.jpg',
                'description' => 'Udang laut vaname segar ukuran sedang yang sudah dibuang kepala, kulit, dan ususnya, menyisakan ekor rapi.',
                'whatsapp_destination' => 'admin',
            ],
            [
                'id' => 12,
                'name' => 'Bakso Sapi Urat Premium 25 Butir',
                'category_id' => 5,
                'category' => 'Frozen Food & Olahan',
                'types' => ['Frozen', 'Curah'],
                'weight' => '500g',
                'weight_value' => 500,
                'unit' => 'gram',
                'price' => 45000,
                'status' => 'Aktif',
                'image' => 'images/hero-1.jpg',
                'description' => 'Bakso sapi dengan tekstur urat renyah kenyal dan aroma daging sapi asli tanpa bahan pengawet berbahaya.',
                'whatsapp_destination' => 'admin',
            ],
            [
                'id' => 13,
                'name' => 'Daging Sirloin Steak Cut 200g',
                'category_id' => 1,
                'category' => 'Daging Sapi',
                'types' => ['Frozen', 'Plain'],
                'weight' => '200g',
                'weight_value' => 200,
                'unit' => 'gram',
                'price' => 45000,
                'status' => 'Aktif',
                'image' => 'images/cat-daging.jpg',
                'description' => 'Potongan steak sirloin dengan strip lemak samping yang juicy, cocok untuk pan-seared steak ala cafe.',
                'whatsapp_destination' => 'admin',
            ],
            [
                'id' => 14,
                'name' => 'Sayap Ayam Broiler Segar (Chicken Wings)',
                'category_id' => 2,
                'category' => 'Ayam Segar & Olahan',
                'types' => ['Fresh', 'Plain', 'Curah'],
                'weight' => '1000g',
                'weight_value' => 1000,
                'unit' => 'gram',
                'price' => 38000,
                'status' => 'Aktif',
                'image' => 'images/cat-ayam.jpg',
                'description' => 'Sayap ayam utuh isi 10-12 pcs, bersih tanpa bulu, favorit untuk olahan spicy wings atau kaldu sup gurih.',
                'whatsapp_destination' => 'admin',
            ],
            [
                'id' => 15,
                'name' => 'Cumi Tubuh Ring Calamari Cut',
                'category_id' => 3,
                'category' => 'Ikan & Seafood',
                'types' => ['Frozen', 'Plain'],
                'weight' => '500g',
                'weight_value' => 500,
                'unit' => 'gram',
                'price' => 49000,
                'status' => 'Aktif',
                'image' => 'images/hero-2.jpg',
                'description' => 'Cumi potong cincin tanpa kulit dan tanpa tinta, siap dimasak tepung goreng krispi atau saus tiram.',
                'whatsapp_destination' => 'admin',
            ],
            [
                'id' => 16,
                'name' => 'Paket Sayur Asem Komplit Tradisional',
                'category_id' => 4,
                'category' => 'Sayuran Siap Olah',
                'types' => ['Ready to Cook'],
                'weight' => '400g',
                'weight_value' => 400,
                'unit' => 'gram',
                'price' => 12000,
                'status' => 'Aktif',
                'image' => 'images/cat-sayur.jpg',
                'description' => 'Paket racik sayur asem siap masak lengkap dengan melinjo, jagung, kacang panjang, labu siam, dan asem jawa.',
                'whatsapp_destination' => 'admin',
            ],
            [
                'id' => 17,
                'name' => 'Daging Iga Sapi Potong Sop / Konro',
                'category_id' => 1,
                'category' => 'Daging Sapi',
                'types' => ['Fresh', 'Plain', 'Curah'],
                'weight' => '1000g',
                'weight_value' => 1000,
                'unit' => 'gram',
                'price' => 110000,
                'status' => 'Aktif',
                'image' => 'images/cat-daging.jpg',
                'description' => 'Potongan iga sapi tebal berdaging gurih, sangat nikmat untuk sop iga bening, iga bakar kecap, atau konro bakar.',
                'whatsapp_destination' => 'order',
            ],
            [
                'id' => 18,
                'name' => 'Hati Ampela Ayam Bersih 10 Pasang',
                'category_id' => 2,
                'category' => 'Ayam Segar & Olahan',
                'types' => ['Fresh', 'Plain'],
                'weight' => '500g',
                'weight_value' => 500,
                'unit' => 'gram',
                'price' => 18000,
                'status' => 'Aktif',
                'image' => 'images/cat-ayam.jpg',
                'description' => 'Jeroan ati ampela ayam segar yang sudah dicuci bersih dari lemak dan kotoran, siap diungkep atau disambal goreng.',
                'whatsapp_destination' => 'admin',
            ],
            [
                'id' => 19,
                'name' => 'Fillet Ikan Kakap Merah Segar Beku',
                'category_id' => 3,
                'category' => 'Ikan & Seafood',
                'types' => ['Frozen', 'Plain'],
                'weight' => '500g',
                'weight_value' => 500,
                'unit' => 'gram',
                'price' => 56000,
                'status' => 'Aktif',
                'image' => 'images/hero-2.jpg',
                'description' => 'Daging kakap merah laut kualitas ekspor tanpa duri, sangat lezat untuk menu asam manis, gulai kepala kakap, atau bakar kecap.',
                'whatsapp_destination' => 'admin',
            ],
            [
                'id' => 20,
                'name' => 'Paket Sayur Capcay Kuah / Goreng',
                'category_id' => 4,
                'category' => 'Sayuran Siap Olah',
                'types' => ['Ready to Cook'],
                'weight' => '350g',
                'weight_value' => 350,
                'unit' => 'gram',
                'price' => 15000,
                'status' => 'Aktif',
                'image' => 'images/cat-sayur.jpg',
                'description' => 'Wortel, kembang kol, brokoli, sawi putih, sawi hijau, dan jamur kuping bersih siap tumis.',
                'whatsapp_destination' => 'admin',
            ],
            [
                'id' => 21,
                'name' => 'Sosis Sapi Frankfurter Premium 10 Pcs',
                'category_id' => 5,
                'category' => 'Frozen Food & Olahan',
                'types' => ['Ready to Cook', 'Frozen'],
                'weight' => '500g',
                'weight_value' => 500,
                'unit' => 'gram',
                'price' => 48000,
                'status' => 'Aktif',
                'image' => 'images/hero-1.jpg',
                'description' => 'Sosis daging sapi dengan selongsong alami collagen yang renyah saat digigit, aroma smoked beef nikmat.',
                'whatsapp_destination' => 'admin',
            ],
            [
                'id' => 22,
                'name' => 'Daging Sukiyaki Beef Roll 250g',
                'category_id' => 1,
                'category' => 'Daging Sapi',
                'types' => ['Frozen', 'Plain'],
                'weight' => '250g',
                'weight_value' => 250,
                'unit' => 'gram',
                'price' => 34000,
                'status' => 'Aktif',
                'image' => 'images/cat-daging.jpg',
                'description' => 'Gulungan daging sapi slice super tipis untuk menu enoki beef roll, sukiyaki, atau hotpot keluarga.',
                'whatsapp_destination' => 'admin',
            ],
            [
                'id' => 23,
                'name' => 'Ayam Katsu Fillet Breaded Siap Goreng',
                'category_id' => 2,
                'category' => 'Ayam Segar & Olahan',
                'types' => ['Ready to Cook', 'Frozen'],
                'weight' => '500g (4 Pcs)',
                'weight_value' => 500,
                'unit' => 'gram',
                'price' => 39000,
                'status' => 'Aktif',
                'image' => 'images/prod-ayam-bumbu.jpg',
                'description' => 'Dada ayam fillet berbalut tepung roti panko renyah, tinggal digoreng 5 menit untuk bekal anak sekolah.',
                'whatsapp_destination' => 'order',
            ],
            [
                'id' => 24,
                'name' => 'Baby Buncis Super Organik Petik Segar',
                'category_id' => 4,
                'category' => 'Sayuran Siap Olah',
                'types' => ['Fresh', 'Plain'],
                'weight' => '250g',
                'weight_value' => 250,
                'unit' => 'gram',
                'price' => 8000,
                'status' => 'Aktif',
                'image' => 'images/cat-sayur.jpg',
                'description' => 'Baby buncis muda renyah manis bebas ulat, sudah dipetik ujungnya, siap untuk tumis daging sapi atau telur asin.',
                'whatsapp_destination' => 'admin',
            ],
        ];
    }

    /**
     * Katalog Produk Management Screen.
     */
    public function produk()
    {
        $categories = $this->getCategories();
        $contactSettings = $this->getContactSettings();
        $products = $this->getProducts();
        $mediaLibrary = $this->getMediaLibrary();

        return view('admin.produk', compact('categories', 'products', 'contactSettings', 'mediaLibrary'));
    }

    /**
     * Keunggulan & Standar Mutu Management Screen.
     */
    public function keunggulan()
    {
        $benefitsData = [
            'section_badge' => 'Kenapa Memilih Kami',
            'section_title' => 'Lebih Praktis, Lebih Siap',
            'section_subtitle' => 'Komitmen kami menghadirkan bahan makanan segar dan frozen bermutu tinggi untuk memudahkan dapur rumah tangga dan operasional usaha Anda di Yogyakarta.',
            'items' => [
                [
                    'id' => 1,
                    'title' => 'Pilihan Produk Lengkap',
                    'icon' => 'grid',
                    'desc' => 'Daging sapi kualitas premium, ayam potong segar, ikan laut/tawar tanpa duri, hingga sayuran harian dalam satu tempat terpadu.',
                ],
                [
                    'id' => 2,
                    'title' => 'Frozen & Terjaga Higienis',
                    'icon' => 'shield',
                    'desc' => 'Dibekukan dengan standar cold-chain ketat serta kemasan kedap udara untuk mengunci kelembapan, rasa, dan nutrisi asli bahan pangan.',
                ],
                [
                    'id' => 3,
                    'title' => 'Ready to Cook Praktis',
                    'icon' => 'clock',
                    'desc' => 'Bahan sudah dipotong presisi, dicuci bersih, dan tersedia opsi bumbu racikan tradisional khas Jogja yang tinggal dimasak tanpa repot.',
                ],
                [
                    'id' => 4,
                    'title' => 'Rumah Tangga & Curah',
                    'icon' => 'truck',
                    'desc' => 'Fleksibilitas belanja: mulai dari pack eceran 200g untuk menu keluarga harian hingga kemasan 10kg-50kg harga grosir bagi pengusaha kuliner.',
                ],
            ]
        ];

        $qualityStandardsData = [
            'section_badge' => 'Standar Mutu',
            'section_title' => 'Mengenal Standar Produk Kami',
            'section_subtitle' => 'Setiap produk yang keluar dari fasilitas penyimpanan Sumber Protein Jogja melewati proses seleksi ketat untuk menjamin keamanan pangan keluarga Anda.',
            'items' => [
                [
                    'id' => 1,
                    'name' => 'Daging Sapi',
                    'tag' => 'Grade Pilihan',
                    'desc' => 'Daging sapi segar lokal dan impor pilihan. Diproses dengan higienitas tinggi, dipotong presisi menggunakan mesin modern, dan dikemas vacuum untuk menjaga kelembapan alami.',
                    'features' => [
                        'Halal Certified',
                        'Bebas Pengawet',
                        'Kemasan Vacuum Food-grade',
                        'Tersedia Potongan Custom'
                    ]
                ],
                [
                    'id' => 2,
                    'name' => 'Ayam Pilihan',
                    'tag' => 'Segar & Bersih',
                    'desc' => 'Ayam broiler dan kampung hasil pemotongan subuh bersertifikat Halal MUI. Tersedia dalam kondisi fresh maupun frozen dengan teknologi blast freezer untuk mencegah pertumbuhan bakteri.',
                    'features' => [
                        '100% Halal MUI',
                        'Bebas Bau & Lendir',
                        'Rantai Dingin Terjamin',
                        'Varian Bumbu Tradisional'
                    ]
                ],
                [
                    'id' => 3,
                    'name' => 'Ikan & Seafood',
                    'tag' => 'Segar Beku Kapal',
                    'desc' => 'Ikan air laut dan air tawar dibekukan seketika di atas kapal nelayan untuk mengunci kesegaran alami laut. Fillet bersih tanpa duri siap olah untuk anak-anak dan keluarga.',
                    'features' => [
                        'Kaya Omega 3 & Protein',
                        'Tanpa Duri (Boneless)',
                        'Bebas Formalin/Kimia',
                        'Higienis Siap Masak'
                    ]
                ],
                [
                    'id' => 4,
                    'name' => 'Sayuran Segar',
                    'tag' => 'Bebas Pestisida Berlebih',
                    'desc' => 'Sayuran segar dipetik dari petani lokal Yogyakarta dan lereng Merapi. Dicuci menggunakan air ozon steril, dipotong higienis, dan dikemas kedap udara.',
                    'features' => [
                        'Petani Lokal Jogja',
                        'Cuci Bersih Ozon',
                        'Tahan Lebih Lama',
                        'Paket Resep Komplit'
                    ]
                ]
            ]
        ];

        $mediaLibrary = $this->getMediaLibrary();

        return view('admin.keunggulan', compact('benefitsData', 'qualityStandardsData', 'mediaLibrary'));
    }

    /**
     * Knowledge & Tips Management Screen.
     */
    public function knowledge()
    {
        $knowledgeCategories = [
            ['id' => 1, 'name' => 'Tips Penyimpanan', 'color' => 'blue', 'status' => 'Aktif', 'articles_count' => 5],
            ['id' => 2, 'name' => 'Edukasi Dapur', 'color' => 'green', 'status' => 'Aktif', 'articles_count' => 5],
            ['id' => 3, 'name' => 'Informasi Produk', 'color' => 'purple', 'status' => 'Aktif', 'articles_count' => 4],
            ['id' => 4, 'name' => 'Resep Masakan', 'color' => 'orange', 'status' => 'Aktif', 'articles_count' => 4],
            ['id' => 5, 'name' => 'Tips Belanja', 'color' => 'yellow', 'status' => 'Aktif', 'articles_count' => 0],
            ['id' => 6, 'name' => 'Edukasi Protein', 'color' => 'red', 'status' => 'Aktif', 'articles_count' => 0],
        ];

        $articles = [
            [
                'id' => 1,
                'title' => '5 Tips Menyimpan Daging Beku Agar Tetap Segar & Higienis',
                'slug' => '5-tips-menyimpan-daging-beku-agar-tetap-segar-higienis',
                'category' => 'Tips Penyimpanan',
                'status' => 'Published',
                'published_at' => '17 Agustus 2026',
                'image' => 'images/know-thawing.jpg',
                'excerpt' => 'Menyimpan daging sapi dan ayam beku memerlukan teknik pengemasan kedap udara dan kestabilan suhu freezer di bawah -18°C agar nutrisi dan keempukan serat tetap terjaga sempurna.',
                'content' => "Daging beku merupakan solusi praktis bagi keluarga modern untuk menjaga ketersediaan bahan pangan berprotein tinggi di rumah. Namun, proses penyimpanan yang keliru dapat merusak kualitas rasa, tekstur, hingga memicu pertumbuhan bakteri berbahaya.\n\nBerikut adalah 5 langkah krusial untuk menjaga daging beku Anda tetap dalam kondisi prima:\n\n1. Gunakan Kemasan Kedap Udara (Vacuum Sealed)\nUdara adalah musuh utama daging beku karena memicu freezer burn—kondisi di mana permukaan daging mengering dan berubah warna keabu-abuan. Bagi daging menjadi porsi sekali masak sebelum dibekukan.\n\n2. Pertahankan Suhu Freezer Stabil di Bawah -18°C\nSuhu yang berfluktuasi akibat sering membuka tutup pintu freezer akan menyebabkan kristal es membesar dan merusak serat otot daging saat dimasak.\n\n3. Jangan Pernah Membekukan Kembali Daging yang Sudah Cair (Thawed)\nDaging yang telah dicairkan memiliki kandungan air bebas yang tinggi. Jika dibekukan ulang, struktur sel daging akan rusak dan bakteri dapat berkembang biak dengan cepat.\n\n4. Beri Label Tanggal Penyimpanan\nSelalu catat tanggal pembelian dan tanggal mulai disimpan di freezer. Terapkan prinsip FIFO (First In, First Out) agar konsumsi selalu optimal.\n\n5. Pisahkan Daging Mentah dari Makanan Siap Santap\nGunakan wadah terpisah untuk mencegah kontaminasi silang cairan daging mentah ke bahan makanan lain di dalam freezer.",
            ],
            [
                'id' => 2,
                'title' => 'Panduan Thawing Daging yang Benar Tanpa Menghilangkan Nutrisi',
                'slug' => 'panduan-thawing-daging-yang-benar-tanpa-menghilangkan-nutrisi',
                'category' => 'Edukasi Dapur',
                'status' => 'Published',
                'published_at' => '16 Agustus 2026',
                'image' => 'images/know-thawing.jpg',
                'excerpt' => 'Mencairkan daging beku di suhu ruang atau merendamnya dalam air panas berisiko merusak rasa dan membiakkan bakteri. Simak teknik thawing chiller yang higienis.',
                'content' => "Thawing atau proses pencairan daging beku sering kali dianggap sepele, padahal metode yang salah dapat merusak tekstur daging dan membiarkan bakteri berkembang biak dengan sangat cepat di zona bahaya suhu (5°C - 60°C).\n\nTiga Metode Thawing Terbaik yang Direkomendasikan:\n\n1. Metode Kulkas / Chiller (Metode Paling Aman)\nPindahkan daging dari freezer ke rak kulkas bawah selama 8 hingga 12 jam sebelum diolah. Proses pencairan yang lambat ini menjaga kelembapan alami daging dan mencegah kebocoran sari daging (drip loss).\n\n2. Metode Air Dingin Mengalir (Metode Cepat)\nJika waktu Anda terbatas, masukkan daging dalam plastik klip kedap air, lalu rendam di dalam mangkuk berisi air dingin atau di bawah kucuran air mengalir pelan. Ganti air setiap 30 menit.\n\n3. Metode Microwave Defrost (Metode Instan)\nGunakan fitur defrost dengan daya rendah. Pastikan daging langsung dimasak setelah proses defrost selesai.",
            ],
            [
                'id' => 3,
                'title' => 'Mengenal Perbedaan Daging Sapi Shortplate dan Ribeye untuk BBQ',
                'slug' => 'mengenal-perbedaan-daging-sapi-shortplate-dan-ribeye-untuk-bbq',
                'category' => 'Informasi Produk',
                'status' => 'Published',
                'published_at' => '15 Agustus 2026',
                'image' => 'images/cat-daging.jpg',
                'excerpt' => 'Bagi penggemar grill dan shabu-shabu, pahami karakteristik marbling, ketebalan lemak, dan tingkat keempukan antara potongan Shortplate slice dan Ribeye steak cut.',
                'content' => "Memilih potongan daging sapi yang tepat adalah kunci utama keberhasilan sesi memanggang BBQ bersama keluarga.\n\n1. Karakteristik Daging Shortplate Slice:\nShortplate berasal dari bagian perut bawah sapi. Potongan ini memiliki rasio lemak dan daging yang seimbang (sekitar 30-40% lemak), sehingga saat dipanggang di atas grill pan akan mengeluarkan aroma gurih yang intens tanpa perlu tambahan mentega berlebih.\n\n2. Karakteristik Daging Ribeye:\nRibeye berasal dari bagian rusuk sapi. Potongan ini terkenal dengan marbling intrakulit yang lembut dan mata lemak di bagian tengah. Teksturnya sangat empuk dan juicy saat dimasak dengan tingkat kematangan medium-well.",
            ],
            [
                'id' => 4,
                'title' => 'Cara Memasak Ikan Gurame Agar Gurih dan Tidak Berbau Tanah',
                'slug' => 'cara-memasak-ikan-gurame-agar-gurih-dan-tidak-berbau-tanah',
                'category' => 'Resep Masakan',
                'status' => 'Published',
                'published_at' => '14 Agustus 2026',
                'image' => 'images/cat-ikan.jpg',
                'excerpt' => 'Ikan air tawar seperti gurame membutuhkan perlakuan khusus pada pembersihan insang, baluran jeruk nipis, dan bumbu marinasi rempah agar cita rasanya segar dan gurih.',
                'content' => "Ikan gurame adalah salah satu lauk favorit keluarga Indonesia, baik digoreng terbang, dibakar bumbu rujak, maupun dimasak asam manis.\n\nLangkah Mengatasi Bau Lumpur pada Ikan Air Tawar:\n1. Bersihkan selaput hitam di dalam rongga perut ikan hingga benar-benar bersih dan buang insangnya.\n2. Lumuri ikan dengan air perasan jeruk nipis dan garam kasar selama 15 menit, lalu bilas air bersih.\n3. Rendam dalam larutan air asam jawa atau parutan jahe dan ketumbar sebelum digoreng dalam minyak panas melimpah.",
            ],
            [
                'id' => 5,
                'title' => 'Tips Menyimpan Sayuran Hijau di Chiller Supaya Tetap Renyah 7 Hari',
                'slug' => 'tips-menyimpan-sayuran-hijau-di-chiller-supaya-tetap-renyah-7-hari',
                'category' => 'Tips Penyimpanan',
                'status' => 'Published',
                'published_at' => '13 Agustus 2026',
                'image' => 'images/cat-sayur.jpg',
                'excerpt' => 'Sayuran daun seperti bayam, kangkung, dan sawi rentan layu dan membusuk jika terkena kelembapan berlebih. Terapkan metode bungkus kertas tisu dan kontainer kedap.',
                'content' => "Sayuran daun hijau membutuhkan sirkulasi udara yang terkontrol dan perlindungan dari tetesan embun kondensasi kulkas.\n\nLangkah-langkah Penyimpanan:\n1. Jangan mencuci sayuran jika belum akan dimasak hari itu.\n2. Potong bagian akar yang kotor dan buang daun yang sudah menguning.\n3. Bungkus sayuran dengan kertas koran polos atau kitchen towel kering.\n4. Masukkan ke dalam food container atau kantong plastik berlubang dan letakkan di laci khusus sayuran (crisper drawer).",
            ],
            [
                'id' => 6,
                'title' => 'Rahasia Marinasi Ayam Ungkep Bumbu Kuning Meresap Sampai ke Tulang',
                'slug' => 'rahasia-marinasi-ayam-ungkep-bumbu-kuning-meresap-sampai-ke-tulang',
                'category' => 'Resep Masakan',
                'status' => 'Published',
                'published_at' => '12 Agustus 2026',
                'image' => 'images/prod-ayam-bumbu.jpg',
                'excerpt' => 'Kombinasi takaran lengkuas, kunyit bakar, ketumbar sangrai, dan daun salam yang tepat saat proses slow cooking menghasilkan ayam ungkep dengan aroma memikat.',
                'content' => "Ayam ungkep bumbu kuning adalah stok lauk wajib di setiap freezer rumah tangga. Kuncinya terletak pada proses perebusan api kecil (simmering) yang memungkinkan bumbu meresap perlahan ke dalam pori-pori daging tanpa membuat tekstur kulit ayam hancur.",
            ],
            [
                'id' => 7,
                'title' => 'Manfaat Mengonsumsi Protein Berkualitas untuk Imunitas Keluarga',
                'slug' => 'manfaat-mengonsumsi-protein-berkualitas-untuk-imunitas-keluarga',
                'category' => 'Edukasi Dapur',
                'status' => 'Published',
                'published_at' => '10 Agustus 2026',
                'image' => 'images/hero-1.jpg',
                'excerpt' => 'Asam amino esensial dari protein hewani dan nabati berperan penting dalam pembentukan sel antibodi dan regenerasi jaringan tubuh sehari-hari.',
                'content' => "Protein adalah zat pembangun utama dalam tubuh manusia. Mengonsumsi variasi protein hewani seperti daging merah, unggas, telur, dan ikan secara berimbang akan memenuhi kebutuhan zat besi dan vitamin B12 harian.",
            ],
            [
                'id' => 8,
                'title' => 'Perbedaan Ayam Broiler, Ayam Pejantan, dan Ayam Kampung',
                'slug' => 'perbedaan-ayam-broiler-ayam-pejantan-dan-ayam-kampung',
                'category' => 'Informasi Produk',
                'status' => 'Published',
                'published_at' => '08 Agustus 2026',
                'image' => 'images/cat-ayam.jpg',
                'excerpt' => 'Karakteristik serat daging, kadar lemak, dan kecocokan jenis masakan antara ayam pedaging broiler, ayam pejantan gurih, dan ayam kampung asli.',
                'content' => "Setiap jenis ayam memiliki karakteristik unik. Ayam broiler bertekstur empuk dan cepat matang, ayam pejantan memiliki kekenyalan mirip ayam kampung dengan harga lebih terjangkau, sedangkan ayam kampung kaya akan kaldu gurih untuk sajian sup obat tradisional.",
            ],
            [
                'id' => 9,
                'title' => 'Panduan Memilih Ikan Laut Segar: Ciri Mata, Insang, dan Sisik',
                'slug' => 'panduan-memilih-ikan-laut-segar-ciri-mata-insang-sisik',
                'category' => 'Edukasi Dapur',
                'status' => 'Published',
                'published_at' => '05 Agustus 2026',
                'image' => 'images/cat-ikan.jpg',
                'excerpt' => 'Kenali tanda kesegaran ikan laut dengan memeriksa mata yang bening cembung, insang merah segar, dan elastisitas daging saat ditekan.',
                'content' => "Membeli ikan laut yang segar memastikan sajian masakan Anda tidak amis dan aman bagi kesehatan pencernaan seluruh anggota keluarga.",
            ],
            [
                'id' => 10,
                'title' => 'Cara Membuat Kaldu Sapi Bening & Bebas Lemak Menggumpal',
                'slug' => 'cara-membuat-kaldu-sapi-bening-bebas-lemak-menggumpal',
                'category' => 'Resep Masakan',
                'status' => 'Published',
                'published_at' => '03 Agustus 2026',
                'image' => 'images/cat-daging.jpg',
                'excerpt' => 'Teknik blanching tulang dan daging iga sapi sebelum direbus lama dengan mirepoix wortel dan seledri untuk kaldu gurih nan jernih.',
                'content' => "Kunci kaldu sapi yang jernih adalah merebus tulang dalam air mendidih selama 5 menit pertama lalu membuang air rebusan kotor tersebut sebelum memulai perebusan panjang.",
            ],
            [
                'id' => 11,
                'title' => 'Mengenal Sistem Cold Chain pada Distribusi Frozen Food',
                'slug' => 'mengenal-sistem-cold-chain-pada-distribusi-frozen-food',
                'category' => 'Informasi Produk',
                'status' => 'Published',
                'published_at' => '01 Agustus 2026',
                'image' => 'images/hero-2.jpg',
                'excerpt' => 'Bagaimana rantai dingin menjaga temperatur produk di bawah suhu beku mulai dari pemotongan, penyimpanan gudang, hingga pengantaran ke pintu rumah Anda.',
                'content' => "Cold Chain memastikan pertumbuhan bakteri terhenti dan menjaga kesegaran daging seolah-olah baru saja dipotong dari peternakan.",
            ],
            [
                'id' => 12,
                'title' => 'Tips Memotong Daging Sapi Melawan Serat Agar Empuk Tanpa Pengempuk',
                'slug' => 'tips-memotong-daging-sapi-melawan-serat-agar-empuk-tanpa-pengempuk',
                'category' => 'Edukasi Dapur',
                'status' => 'Published',
                'published_at' => '28 Juli 2026',
                'image' => 'images/prod-beef-slice.jpg',
                'excerpt' => 'Arah potongan pisau terhadap serat otot daging menentukan keempukan gigitan saat dinikmati setelah matang.',
                'content' => "Memotong tegak lurus melintasi arah alur serat (across the grain) akan memperpendek serat otot sehingga daging tidak terasa liat saat dikunyah.",
            ],
            [
                'id' => 13,
                'title' => 'Ide Bekal Sekolah Anak Sehat dengan Olahan Daging & Sayuran',
                'slug' => 'ide-bekal-sekolah-anak-sehat-dengan-olahan-daging-sayuran',
                'category' => 'Resep Masakan',
                'status' => 'Published',
                'published_at' => '25 Juli 2026',
                'image' => 'images/prod-sayur-mix.jpg',
                'excerpt' => 'Inspirasi menu bento bergizi seimbang yang cepat disiapkan di pagi hari menggunakan bahan siap olah Sumber Protein Jogja.',
                'content' => "Kombinasi stik nugget ayam homemade, brokoli kukus mentega, dan telur puyuh kecap menjadi favorit bekal praktis dan bergizi.",
            ],
            [
                'id' => 14,
                'title' => 'Mengenal Sertifikasi Halal pada Produk Daging Potong',
                'slug' => 'mengenal-sertifikasi-halal-pada-produk-daging-potong',
                'category' => 'Informasi Produk',
                'status' => 'Published',
                'published_at' => '20 Juli 2026',
                'image' => 'images/hero-1.jpg',
                'excerpt' => 'Jaminan kepatuhan syariat dalam proses penyembelihan, penanganan higienis, dan sanitasi tempat pemotongan hewan terakreditasi.',
                'content' => "Sumber Protein Jogja menjamin 100% daging sapi dan unggas berasal dari Rumah Potong Hewan resmi bersertifikasi halal MUI & BPJPH.",
            ],
            [
                'id' => 15,
                'title' => 'Cara Mengatur Porsi Masak Mingguan (Meal Prep) untuk Ibu Bekerja',
                'slug' => 'cara-mengatur-porsi-masak-mingguan-meal-prep-ibu-bekerja',
                'category' => 'Tips Penyimpanan',
                'status' => 'Published',
                'published_at' => '15 Juli 2026',
                'image' => 'images/know-thawing.jpg',
                'excerpt' => 'Strategi hemat waktu di dapur dengan mencuci, memotong, dan memarinasi bahan makanan untuk stok 7 hari ke depan.',
                'content' => "Meal prep di akhir pekan menghemat hingga 45 menit waktu memasak harian dan memastikan keluarga tetap makan masakan sehat di rumah.",
            ],
            [
                'id' => 16,
                'title' => 'Tips Memilih Minyak Goreng yang Sehat untuk Memasak Lauk Harian',
                'slug' => 'tips-memilih-minyak-goreng-yang-sehat-untuk-memasak-lauk-harian',
                'category' => 'Edukasi Dapur',
                'status' => 'Draft',
                'published_at' => 'Draft',
                'image' => 'images/hero-3.jpg',
                'excerpt' => 'Memahami smoke point minyak kelapa, minyak jagung, dan canola oil untuk menggoreng renyah tanpa merusak kualitas makanan.',
                'content' => "Draft artikel mengenai pemilihan minyak goreng yang stabil pada suhu tinggi untuk menggoreng ayam ungkep dan aneka seafood.",
            ],
            [
                'id' => 17,
                'title' => 'Perbandingan Nutrisi Daging Ikan Tawar vs Ikan Laut',
                'slug' => 'perbandingan-nutrisi-daging-ikan-tawar-vs-ikan-laut',
                'category' => 'Informasi Produk',
                'status' => 'Draft',
                'published_at' => 'Draft',
                'image' => 'images/cat-ikan.jpg',
                'excerpt' => 'Eksplorasi kandungan asam lemak esensial EPA, DHA, kalsium, dan fosfor pada ikan tawar lokal dibandingkan ikan laut dalam.',
                'content' => "Draft perbandingan nutrisi ikan gurame dan nila dibandingkan dori dan salmon untuk variasi menu mingguan keluarga.",
            ],
            [
                'id' => 18,
                'title' => 'Resep Sup Ikan Gurame Asam Pedas Segar Khas Restoran Sunda',
                'slug' => 'resep-sup-ikan-gurame-asam-pedas-segar-khas-restoran-sunda',
                'category' => 'Resep Masakan',
                'status' => 'Draft',
                'published_at' => 'Draft',
                'image' => 'images/prod-ikan-gurame.jpg',
                'excerpt' => 'Kuah bening segar dengan rempah daun kemangi, tomat hijau, serai, dan cabai rawit utuh berpadu fillet gurame lembut.',
                'content' => "Draft resep lengkap sup gurame kuah asam pedas bening anti amis menggunakan fillet gurame potong segar Sumber Protein Jogja.",
            ],
        ];

        $mediaLibrary = $this->getMediaLibrary();

        return view('admin.knowledge', compact('articles', 'knowledgeCategories', 'mediaLibrary'));
    }

    /**
     * Footer (Ulasan Pelanggan, Kunjungi Outlet, Actual Footer) Management Screen.
     */
    public function footer()
    {
        $footerData = [
            'reviews' => [
                'section_badge' => 'Ulasan Pelanggan',
                'section_title' => 'Apa Kata Mereka?',
                'section_subtitle' => 'Pengalaman nyata dari ibu rumah tangga, chef rumahan, hingga pemilik kedai kuliner di Yogyakarta.',
                'rating' => 4.9,
                'total_reviews' => '180+',
                'displayed_count' => 6,
                'google_place_url' => 'https://maps.google.com/?cid=1234567890123456',
                'source_name' => 'Google Maps Verified Customer Reviews',
                'items' => [
                    [
                        'id' => 1,
                        'name' => 'Rian Hidayat',
                        'role' => 'Pelanggan Rumah Tangga (Sleman)',
                        'rating' => 5,
                        'date' => '2 minggu lalu',
                        'comment' => 'Daging slice-nya segar banget dan potongannya rapi. Sangat cocok buat shabu-shabu di rumah bareng keluarga. Pengiriman sameday cepat dan tetap beku!',
                        'is_active' => true,
                    ],
                    [
                        'id' => 2,
                        'name' => 'Dini Anggraini',
                        'role' => 'Ibu Rumah Tangga (Yogyakarta)',
                        'rating' => 5,
                        'date' => '1 bulan lalu',
                        'comment' => 'Ayam ungkep bumbu kuningnya juara! Tinggal sreng goreng sebentar, bumbunya meresap sampai ke dalam. Praktis banget buat bekal sekolah anak.',
                        'is_active' => true,
                    ],
                    [
                        'id' => 3,
                        'name' => 'Budi Santoso',
                        'role' => 'Owner Cafe & Resto (Bantul)',
                        'rating' => 5,
                        'date' => '1 bulan lalu',
                        'comment' => 'Langganan beli fillet dori dan ayam karkas untuk kebutuhan resto. Kualitas konsisten, higienis, dan harga bersahabat untuk pembelian partai.',
                        'is_active' => true,
                    ],
                    [
                        'id' => 4,
                        'name' => 'Siti Nurhaliza',
                        'role' => 'Pelanggan Setia (Sleman)',
                        'rating' => 5,
                        'date' => '2 bulan lalu',
                        'comment' => 'Sayuran siap masaknya bener-bener ngebantu waktu masak pagi. Sayur sop komplit dan bersih, ga perlu repot potong-potong lagi.',
                        'is_active' => true,
                    ],
                    [
                        'id' => 5,
                        'name' => 'Hendro Wijaya',
                        'role' => 'Pengusaha Catering (Jogja Kota)',
                        'rating' => 5,
                        'date' => '3 bulan lalu',
                        'comment' => 'Pelayanan admin via WhatsApp sangat ramah dan responsif. Daging rendang potongan seragam, mempermudah kalkulasi porsi catering.',
                        'is_active' => true,
                    ],
                    [
                        'id' => 6,
                        'name' => 'Mega Puspita',
                        'role' => 'Pecinta BBQ Rumahan',
                        'rating' => 5,
                        'date' => '3 bulan lalu',
                        'comment' => 'Shortplate slice-nya juicy parah! Marbling lemaknya pas ga bikin eneg. Pasti repeat order terus di Sumber Protein Jogja.',
                        'is_active' => true,
                    ],
                ]
            ],
            'location' => [
                'section_badge' => 'Kunjungi Outlet',
                'section_title' => 'Lokasi & Jam Operasional',
                'section_subtitle' => 'Bisa datang langsung memilih daging segar atau pesan online untuk pengiriman instan ke seluruh area D.I. Yogyakarta.',
                'store_name' => 'Sumber Protein Jogja — Outlet Utama Sleman',
                'address' => 'Jl. Magelang KM 7.5, Mlati, Sleman, D.I. Yogyakarta 55285',
                'google_maps_embed' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3953.2843516345946!2d110.36017527588145!3d-7.759654176950294!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e7a58498f399f91%3A0x6b876112d76f0b4!2sYogyakarta!5e0!3m2!1sid!2sid!4v1700000000000!5m2!1sid!2sid',
                'google_maps_link' => 'https://maps.google.com/?q=Sumber+Protein+Jogja',
                'operational_hours' => 'Senin – Minggu: 07.00 – 19.00 WIB',
                'whatsapp_contact' => '0812-3456-7890',
                'phone_contact' => '(0274) 889977',
                'delivery_coverage' => 'Kota Yogyakarta, Sleman, Bantul, dan sekitarnya',
            ],
            'actual_footer' => [
                'brand_title' => 'Sumber Protein Jogja',
                'brand_desc' => 'Penyedia bahan makanan mentah, frozen food, dan olahan ready-to-cook berkualitas di Yogyakarta. Melayani kebutuhan konsumsi harian keluarga dan suplai horeka/curah.',
                'copyright' => 'Sumber Protein Jogja. Hak Cipta Dilindungi.',
                'social_links' => [
                    'instagram' => 'https://instagram.com/sumberproteinjogja',
                    'tiktok' => 'https://tiktok.com/@sumberproteinjogja',
                    'whatsapp' => 'https://wa.me/6281234567890',
                ],
                'nav_links' => [
                    ['title' => 'Beranda', 'url' => '#hero'],
                    ['title' => 'Kategori Produk', 'url' => '#kategori'],
                    ['title' => 'Katalog Pilihan', 'url' => '#produk'],
                    ['title' => 'Keunggulan Kami', 'url' => '#keunggulan'],
                    ['title' => 'Dapur & Knowledge', 'url' => '#knowledge'],
                    ['title' => 'Ulasan Pelanggan', 'url' => '#testimoni'],
                ]
            ]
        ];

        return view('admin.footer', compact('footerData'));
    }

    /**
     * SEO & Meta Settings Management Screen.
     */
    public function seo()
    {
        $seoData = [
            'meta_title' => 'Sumber Protein Jogja | Bahan Masak Siap Olah, Daging Segar & Frozen Food',
            'meta_description' => 'Penyedia bahan masakan siap olah, daging sapi slice, ayam segar, seafood, dan sayuran higienis fresh & frozen terpercaya di Jogja. Melayani kebutuhan harian & curah.',
            'canonical_url' => 'https://sumberproteinjogja.com/',
            'og_title' => 'Sumber Protein Jogja — Bahan Masak Siap Olah, Tinggal Masak',
            'og_description' => 'Daging sapi, ayam, ikan, dan sayuran segar & frozen food higienis kualitas terbaik di Jogja. Pesan mudah via WhatsApp.',
            'og_image' => 'images/hero-1.jpg',
            'meta_keywords' => 'daging sapi jogja, frozen food sleman, ayam segar jogja, ready to cook jogja, bahan masak siap olah',
            'robots' => 'index, follow',
            'author' => 'Sumber Protein Jogja',
        ];

        $mediaLibrary = $this->getMediaLibrary();

        return view('admin.seo', compact('seoData', 'mediaLibrary'));
    }

    /**
     * Site & Contact Settings Management Screen.
     */
    public function settings()
    {
        $settingsData = [
            'website' => [
                'site_name' => 'Sumber Protein Jogja',
                'brand_name' => 'Sumber Protein',
                'tagline' => 'Penyedia Bahan Segar & Frozen Food Terpercaya di Jogja',
                'tab_title_pattern' => '{page_title} — Sumber Protein Jogja',
                'logo_url' => 'images/hero-1.jpg',
                'favicon_url' => 'images/hero-1.jpg',
            ],
            'contact' => $this->getContactSettings(),
            'admin_panel' => [
                'panel_name' => 'Sumber Protein CMS',
                'badge_tag' => 'CMS Panel v1.0',
                'footer_note' => 'Sumber Protein Jogja © 2026 • Layout Locked • Content Flexible',
            ],
            'admin_user' => [
                'name' => 'Admin Sumber Protein',
                'role' => 'Super Admin',
                'email' => 'admin@sumberproteinjogja.com',
                'phone' => '0812-3456-7890',
                'avatar_text' => 'SP',
                'avatar_image' => 'images/hero-1.jpg',
            ],
        ];

        $mediaLibrary = $this->getMediaLibrary();

        return view('admin.settings', compact('settingsData', 'mediaLibrary'));
    }
}
