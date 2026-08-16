<?php

namespace App\Http\Controllers;

class LandingController
{
    public function index()
    {
        $categories = [
            [
                'id' => 'daging',
                'name' => 'Daging Sapi',
                'subtitle' => 'Slice, Sengkel, Ribeye & Giling',
                'badge' => 'Sertifikasi Halal',
                'image' => 'images/cat-daging.jpg',
                'count' => '12+ Variasi',
                'description' => 'Daging sapi segar & frozen potongan higienis tanpa pengawet.'
            ],
            [
                'id' => 'ayam',
                'name' => 'Ayam Segar & Olahan',
                'subtitle' => 'Fillet, Parting, Utuh & Ungkep',
                'badge' => 'Potong Segar Tiap Subuh',
                'image' => 'images/cat-ayam.jpg',
                'count' => '16+ Variasi',
                'description' => 'Ayam potong higienis standar cold-chain, plain maupun berbumbu.'
            ],
            [
                'id' => 'ikan',
                'name' => 'Ikan & Seafood',
                'subtitle' => 'Salmon, Gurame, Dori & Udang',
                'badge' => 'Segar Beku Kapal',
                'image' => 'images/cat-ikan.jpg',
                'count' => '10+ Variasi',
                'description' => 'Fillet tanpa duri dan ikan utuh segar beku kaya nutrisi omega-3.'
            ],
            [
                'id' => 'sayur',
                'name' => 'Sayuran Siap Olah',
                'subtitle' => 'Sayur Sup, Capcay & Sayur Segar',
                'badge' => 'Bersih Tinggal Cemplung',
                'image' => 'images/cat-sayur.jpg',
                'count' => '8+ Variasi',
                'description' => 'Sayuran organik & hidroponik cuci bersih praktis untuk masakan harian.'
            ],
        ];

        $products = [
            [
                'id' => 1,
                'name' => 'Daging Sapi Shortplate Slice Premium',
                'category' => 'daging',
                'type_category' => 'daging',
                'type_badges' => [
                    ['text' => 'Frozen', 'class' => 'badge-frozen'],
                    ['text' => 'Plain', 'class' => 'badge-primary'],
                    ['text' => 'Rumah Tangga', 'class' => 'badge-dark'],
                ],
                'weight' => '500 gram',
                'price' => 58000,
                'price_formatted' => 'Rp 58.000',
                'image' => 'images/prod-beef-slice.jpg',
                'description' => 'Daging sapi slice tipis 1.5mm empuk juicy, cocok untuk sukiyaki, grill BBQ, atau tumisan.',
                'wa_message' => 'Halo Sumber Protein Jogja, saya ingin pesan *Daging Sapi Shortplate Slice Premium 500g* (Rp 58.000).'
            ],
            [
                'id' => 2,
                'name' => 'Ayam Ungkep Bumbu Kuning Lengkuas',
                'category' => 'ayam',
                'type_category' => 'ready-to-cook',
                'type_badges' => [
                    ['text' => 'Ready to Cook', 'class' => 'badge-ready'],
                    ['text' => 'Berbumbu', 'class' => 'badge-accent'],
                    ['text' => 'Rumah Tangga', 'class' => 'badge-dark'],
                ],
                'weight' => '1 Ekor (8 Potong / 800g)',
                'price' => 45000,
                'price_formatted' => 'Rp 45.000',
                'image' => 'images/prod-ayam-bumbu.jpg',
                'description' => 'Ayam bumbu rempah kuning lengkuas khas Jogja meresap sempurna. Tinggal goreng/airfryer 10 menit.',
                'wa_message' => 'Halo Sumber Protein Jogja, saya ingin pesan *Ayam Ungkep Bumbu Kuning Lengkuas 1 Ekor* (Rp 45.000).'
            ],
            [
                'id' => 3,
                'name' => 'Fillet Ikan Gurame Bersih Tanpa Duri',
                'category' => 'ikan',
                'type_category' => 'ikan',
                'type_badges' => [
                    ['text' => 'Frozen', 'class' => 'badge-frozen'],
                    ['text' => 'Plain', 'class' => 'badge-primary'],
                    ['text' => 'Rumah Tangga', 'class' => 'badge-dark'],
                ],
                'weight' => '500 gram (2-3 Fillet)',
                'price' => 48000,
                'price_formatted' => 'Rp 48.000',
                'image' => 'images/prod-ikan-fillet.jpg',
                'description' => 'Fillet gurame air tawar segar tanpa bau tanah, sudah dibersihkan sisik & tulang. Siap tepung/asam manis.',
                'wa_message' => 'Halo Sumber Protein Jogja, saya ingin pesan *Fillet Ikan Gurame Bersih Tanpa Duri 500g* (Rp 48.000).'
            ],
            [
                'id' => 4,
                'name' => 'Paket Sayur Sup Sehat Komplit Siap Masak',
                'category' => 'sayur',
                'type_category' => 'ready-to-cook',
                'type_badges' => [
                    ['text' => 'Ready to Cook', 'class' => 'badge-ready'],
                    ['text' => 'Plain', 'class' => 'badge-primary'],
                    ['text' => 'Rumah Tangga', 'class' => 'badge-dark'],
                ],
                'weight' => '400 gram pack',
                'price' => 14000,
                'price_formatted' => 'Rp 14.000',
                'image' => 'images/prod-sayur-sup.jpg',
                'description' => 'Wortel, kentang, buncis, kembang kol, daun bawang seledri sudah dicuci & dipotong higienis.',
                'wa_message' => 'Halo Sumber Protein Jogja, saya ingin pesan *Paket Sayur Sup Sehat Komplit 400g* (Rp 14.000).'
            ],
            [
                'id' => 5,
                'name' => 'Dada Ayam Fillet Boneless Skinless (BL)',
                'category' => 'ayam',
                'type_category' => 'ayam',
                'type_badges' => [
                    ['text' => 'Frozen', 'class' => 'badge-frozen'],
                    ['text' => 'Plain', 'class' => 'badge-primary'],
                    ['text' => 'Rumah Tangga', 'class' => 'badge-dark'],
                ],
                'weight' => '1.000 gram (1 Kg)',
                'price' => 52000,
                'price_formatted' => 'Rp 52.000',
                'image' => 'images/cat-ayam.jpg',
                'description' => 'Dada ayam tanpa tulang dan tanpa kulit kualitas tinggi, favorit gym goers & menu diet sehat tinggi protein.',
                'wa_message' => 'Halo Sumber Protein Jogja, saya ingin pesan *Dada Ayam Fillet Boneless 1 Kg* (Rp 52.000).'
            ],
            [
                'id' => 6,
                'name' => 'Daging Sapi Sengkel / Shank Cut Super',
                'category' => 'daging',
                'type_category' => 'daging',
                'type_badges' => [
                    ['text' => 'Frozen', 'class' => 'badge-frozen'],
                    ['text' => 'Plain', 'class' => 'badge-primary'],
                    ['text' => 'Rumah Tangga', 'class' => 'badge-dark'],
                ],
                'weight' => '1.000 gram (1 Kg)',
                'price' => 125000,
                'price_formatted' => 'Rp 125.000',
                'image' => 'images/cat-daging.jpg',
                'description' => 'Potongan sengkel dengan urat lembut khas, sangat empuk dan gurih untuk menu Soto, Rawon, Rendang, dan Semur.',
                'wa_message' => 'Halo Sumber Protein Jogja, saya ingin pesan *Daging Sapi Sengkel Super 1 Kg* (Rp 125.000).'
            ],
            [
                'id' => 7,
                'name' => 'Ayam Parting Segar Beku Curah Horeka',
                'category' => 'ayam',
                'type_category' => 'curah',
                'type_badges' => [
                    ['text' => 'Curah (Bulk)', 'class' => 'badge-curah'],
                    ['text' => 'Frozen', 'class' => 'badge-frozen'],
                    ['text' => 'Horeka / Katering', 'class' => 'badge-dark'],
                ],
                'weight' => 'Kemasan 10 Kg',
                'price' => 380000,
                'price_formatted' => 'Rp 380.000',
                'image' => 'images/hero-1.jpg',
                'description' => 'Ayam potong 8 / 10 / 12 porsi untuk kebutuhan warung makan, resto, catering pernikahan & hotel Jogja.',
                'wa_message' => 'Halo Sumber Protein Jogja, saya ingin pesan *Ayam Parting Segar Beku Curah 10 Kg* (Rp 380.000).'
            ],
            [
                'id' => 8,
                'name' => 'Atlantic Salmon Portion Steak Cut',
                'category' => 'ikan',
                'type_category' => 'ikan',
                'type_badges' => [
                    ['text' => 'Frozen', 'class' => 'badge-frozen'],
                    ['text' => 'Plain', 'class' => 'badge-primary'],
                    ['text' => 'Rumah Tangga', 'class' => 'badge-dark'],
                ],
                'weight' => '200 gram portion',
                'price' => 55000,
                'price_formatted' => 'Rp 55.000',
                'image' => 'images/hero-2.jpg',
                'description' => 'Portion cut salmon Norwegia kaya Omega-3, tekstur lembut oranye cerah, ideal untuk pan-seared atau menu MPASI bayi.',
                'wa_message' => 'Halo Sumber Protein Jogja, saya ingin pesan *Atlantic Salmon Portion Steak 200g* (Rp 55.000).'
            ],
            [
                'id' => 9,
                'name' => 'Daging Sapi Giling Curah Fat 15% (Bulk)',
                'category' => 'daging',
                'type_category' => 'curah',
                'type_badges' => [
                    ['text' => 'Curah (Bulk)', 'class' => 'badge-curah'],
                    ['text' => 'Frozen', 'class' => 'badge-frozen'],
                    ['text' => 'UMKM Kuliner', 'class' => 'badge-dark'],
                ],
                'weight' => 'Kemasan 5 Kg',
                'price' => 495000,
                'price_formatted' => 'Rp 495.000',
                'image' => 'images/cat-daging.jpg',
                'description' => 'Minced beef rasio lemak 85/15 standar restoran, cocok untuk produksi patty burger, saus bolognese, atau isian dimsum.',
                'wa_message' => 'Halo Sumber Protein Jogja, saya ingin pesan *Daging Sapi Giling Curah 5 Kg* (Rp 495.000).'
            ],
            [
                'id' => 10,
                'name' => 'Daging Sapi Marinated Black Pepper Ready to Cook',
                'category' => 'daging',
                'type_category' => 'ready-to-cook',
                'type_badges' => [
                    ['text' => 'Ready to Cook', 'class' => 'badge-ready'],
                    ['text' => 'Berbumbu', 'class' => 'badge-accent'],
                    ['text' => 'Rumah Tangga', 'class' => 'badge-dark'],
                ],
                'weight' => '400 gram pack',
                'price' => 62000,
                'price_formatted' => 'Rp 62.000',
                'image' => 'images/hero-3.jpg',
                'description' => 'Potongan daging sapi empuk dimarinasi dengan lada hitam aromatik dan saus spesial. Cukup tumis 7 menit.',
                'wa_message' => 'Halo Sumber Protein Jogja, saya ingin pesan *Daging Sapi Marinated Black Pepper 400g* (Rp 62.000).'
            ]
        ];

        $benefits = [
            [
                'title' => 'Pilihan Produk Lengkap',
                'subtitle' => 'Daging sapi, ayam, ikan laut/tawar, hingga sayuran harian dalam satu tempat.',
                'icon' => 'grid'
            ],
            [
                'title' => 'Frozen & Terjaga Higienis',
                'subtitle' => 'Dibekukan cepat dengan standar cold-chain terjaga agar kesegaran dan nutrisi terkunci.',
                'icon' => 'shield'
            ],
            [
                'title' => 'Ready to Cook Praktis',
                'subtitle' => 'Sudah dipotong, dibersihkan, dan tersedia varian berbumbu siap masak tanpa repot.',
                'icon' => 'clock'
            ],
            [
                'title' => 'Rumah Tangga & Pembelian Curah',
                'subtitle' => 'Fleksibel mulai dari eceran 250g untuk menu keluarga hingga pesanan partai besar (curah) resto/katering.',
                'icon' => 'truck'
            ],
        ];

        $knowledgeArticles = [
            [
                'id' => 1,
                'category' => 'Tips',
                'badge_class' => 'bg-emerald-50 text-emerald-800',
                'read_time' => '3 mnt baca',
                'title' => 'Cara Thawing Daging Frozen yang Benar Agar Nutrisi Tetap Terjaga',
                'excerpt' => 'Hindari mencairkan daging di suhu ruang terbuka. Pelajari metode refrigerator thawing dan cold water thawing yang aman dari bakteri.',
                'image' => 'images/know-thawing.jpg',
            ],
            [
                'id' => 2,
                'category' => 'Resep',
                'badge_class' => 'bg-amber-50 text-amber-800',
                'read_time' => '4 mnt baca',
                'title' => 'Rahasia Ayam Ungkep Bumbu Kuning Gurih Meresap Sampai ke Tulang',
                'excerpt' => 'Tips mematangkan ayam siap masak dengan api kecil dan cara menggorengnya agar kulit renyah namun daging tetap lembut juicy.',
                'image' => 'images/prod-ayam-bumbu.jpg',
            ],
            [
                'id' => 3,
                'category' => 'Penyimpanan',
                'badge_class' => 'bg-sky-50 text-sky-800',
                'read_time' => '5 mnt baca',
                'title' => 'Panduan Suhu & Durasi Maksimal Penyimpanan Ikan & Seafood di Freezer',
                'excerpt' => 'Ketahui batas waktu ideal penyimpanan salmon, gurame, dan udang beku agar kualitas tekstur dan rasanya tidak menurun.',
                'image' => 'images/hero-2.jpg',
            ],
            [
                'id' => 4,
                'category' => 'Informasi Produk',
                'badge_class' => 'bg-purple-50 text-purple-800',
                'read_time' => '4 mnt baca',
                'title' => 'Mengenal Potongan Daging Sapi: Shortplate, Sengkel, vs Ribeye',
                'excerpt' => 'Panduan praktis memilih bagian daging sapi yang tepat sesuai jenis masakan Indonesia dan masakan modern.',
                'image' => 'images/prod-beef-slice.jpg',
            ],
            [
                'id' => 5,
                'category' => 'Resep',
                'badge_class' => 'bg-amber-50 text-amber-800',
                'read_time' => '3 mnt baca',
                'title' => 'Trik Menumis Sayuran Siap Olah Agar Tetap Renyah & Hijau Segar',
                'excerpt' => 'Langkah sederhana mempertahankan warna alami dan tekstur renyah sayuran saat dimasak dengan api besar.',
                'image' => 'images/prod-sayur-sup.jpg',
            ],
            [
                'id' => 6,
                'category' => 'Tips',
                'badge_class' => 'bg-emerald-50 text-emerald-800',
                'read_time' => '4 mnt baca',
                'title' => 'Keuntungan Belanja Bahan Makanan Curah (Bulk) untuk Usaha Kuliner Jogja',
                'excerpt' => 'Strategi menghemat food cost warung makan dan katering dengan suplai bahan baku fresh frozen stabil.',
                'image' => 'images/hero-1.jpg',
            ],
        ];

        $productKnowledge = [
            [
                'name' => 'Daging Sapi',
                'tag' => 'Grade Pilihan',
                'desc' => 'Daging sapi segar lokal dan impor pilihan. Diproses dengan higienitas tinggi, dipotong presisi menggunakan mesin modern, dan dikemas vacuum untuk menjaga kelembapan alami.',
                'features' => ['Halal Certified', 'Bebas Pengawet', 'Kemasan Vacuum Food-grade', 'Tersedia Potongan Custom']
            ],
            [
                'name' => 'Ayam Pilihan',
                'tag' => 'Segar & Bersih',
                'desc' => 'Ayam broiler dan kampung hasil pemotongan subuh bersertifikat Halal MUI. Tersedia dalam kondisi fresh maupun frozen dengan teknologi blast freezer untuk mencegah pertumbuhan bakteri.',
                'features' => ['100% Halal MUI', 'Bebas Bau & Lendir', 'Rantai Dingin Terjamin', 'Varian Bumbu Tradisional']
            ],
            [
                'name' => 'Ikan & Seafood',
                'tag' => 'Segar Beku Kapal',
                'desc' => 'Ikan air laut dan air tawar dibekukan seketika di atas kapal nelayan untuk mengunci kesegaran alami laut. Fillet bersih tanpa duri siap olah untuk anak-anak dan keluarga.',
                'features' => ['Kaya Omega 3 & Protein', 'Tanpa Duri (Boneless)', 'Bebas Formalin/Kimia', 'Higienis Siap Masak']
            ],
            [
                'name' => 'Sayuran Segar',
                'tag' => 'Bebas Pestisida Berlebih',
                'desc' => 'Sayuran segar dipetik dari petani lokal Yogyakarta dan lereng Merapi. Dicuci menggunakan air ozon steril, dipotong higienis, dan dikemas kedap udara.',
                'features' => ['Petani Lokal Jogja', 'Cuci Bersih Ozon', 'Tahan Lebih Lama', 'Paket Resep Komplit']
            ],
        ];

        $testimonials = [
            [
                'name' => 'Ratna Dewi Kusuma',
                'role' => 'Ibu Rumah Tangga, Sleman',
                'rating' => 5,
                'date' => '3 hari yang lalu',
                'review' => 'Sebagai ibu pekerja, belanja di Sumber Protein Jogja sangat menghemat waktu. Ayam bumbu kuningnya tinggal goreng, daging slicenya fresh banget dan nggak banyak lemak. Anak-anak suka sekali!',
                'avatar' => 'RD'
            ],
            [
                'name' => 'Bambang Haryanto',
                'role' => 'Owner Kedai Ricebowl, Kotagede',
                'rating' => 5,
                'date' => '1 minggu yang lalu',
                'review' => 'Sudah 4 bulan suplai fillet dada ayam curah untuk resto saya dari sini. Kualitasnya sangat stabil, potongan rapi, dan pengiriman tepat waktu. Harga partai besarnya sangat kompetitif di Jogja.',
                'avatar' => 'BH'
            ],
            [
                'name' => 'dr. Nadia Paramita',
                'role' => 'Dokter & Home Chef, Bantul',
                'rating' => 5,
                'date' => '2 minggu yang lalu',
                'review' => 'Salmon steak dan fillet guramenya benar-benar fresh, tidak amis sama sekali. Senang sekali ada toko protein selengkap ini dengan standar packaging vacuum yang higienis.',
                'avatar' => 'NP'
            ],
        ];

        $storeInfo = [
            'name' => 'Sumber Protein Jogja',
            'tagline' => 'Bahan Masak Siap Olah, Tinggal Masak.',
            'address' => 'Jl. Kaliurang Km. 8.5 No. 42, Sinduharjo, Ngaglik, Sleman, D.I. Yogyakarta 55581',
            'hours' => 'Senin – Minggu: 07.00 – 19.00 WIB',
            'phone' => '+62 812-3456-7890',
            'whatsapp' => '6281234567890',
            'email' => 'halo@sumberproteinjogja.id',
            'instagram' => '@sumberproteinjogja',
            'maps_url' => 'https://maps.google.com/?q=Sumber+Protein+Jogja+Jl+Kaliurang+Yogyakarta'
        ];

        return view('landing', compact(
            'categories',
            'products',
            'benefits',
            'knowledgeArticles',
            'productKnowledge',
            'testimonials',
            'storeInfo'
        ));
    }
}
