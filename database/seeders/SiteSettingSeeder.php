<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SiteSetting;

class SiteSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            'hero' => [
                'badge' => 'Pusat Bahan Segar & Frozen Jogja',
                'title' => 'Belanja Daging Sapi, Ayam, Seafood & Sayuran Siap Olah Praktis',
                'subtitle' => 'Pilihan tepat keluarga & pengusaha kuliner di Yogyakarta. Produk higienis dengan standar cold-chain terjamin, dipotong rapi, dan dikirim aman sampai ke dapur Anda.',
                'whatsapp_button_text' => 'Konsultasi & Order Cepat via WhatsApp',
                'catalog_button_text' => 'Lihat Katalog Lengkap',
            ],
            'hero_trust_items' => [
                ['id' => 1, 'text' => '100% Halal & Higienis', 'is_active' => true, 'sort_order' => 1],
                ['id' => 2, 'text' => 'Standar Rantai Dingin (Cold Chain)', 'is_active' => true, 'sort_order' => 2],
                ['id' => 3, 'text' => 'Pengiriman Cepat Se-Jogja', 'is_active' => true, 'sort_order' => 3],
            ],
            'hero_partners' => [
                'badge' => 'Kepercayaan Mitra',
                'title' => 'Telah Dipercaya Restoran, Cafe, Catering & Rumah Tangga di Jogja',
                'partners' => [
                    ['id' => 1, 'name' => 'Partner 1', 'logo' => 'images/mitra-placeholder.png', 'is_active' => true, 'sort_order' => 1],
                    ['id' => 2, 'name' => 'Partner 2', 'logo' => 'images/mitra-placeholder.png', 'is_active' => true, 'sort_order' => 2],
                    ['id' => 3, 'name' => 'Partner 3', 'logo' => 'images/mitra-placeholder.png', 'is_active' => true, 'sort_order' => 3],
                ],
            ],
            'flash_sale' => [
                'enabled' => false,
                'end_at' => null,
                'title' => 'Flash Sale Hari Ini',
                'subtitle' => 'Promo terbatas untuk produk pilihan berprotein tinggi.',
            ],
            'review_settings' => [
                'review_mode' => 'manual',
                'google_place_id' => null,
                'google_rating' => null,
                'google_total_reviews' => null,
                'last_synced_at' => null,
            ],
            'benefits' => [
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
                ],
            ],
            'quality_standards' => [
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
                            'Tersedia Potongan Custom',
                        ],
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
                            'Varian Bumbu Tradisional',
                        ],
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
                            'Higienis Siap Masak',
                        ],
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
                            'Paket Resep Komplit',
                        ],
                    ],
                ],
            ],
        ];

        foreach ($settings as $key => $value) {
            SiteSetting::set($key, $value);
        }
    }
}
