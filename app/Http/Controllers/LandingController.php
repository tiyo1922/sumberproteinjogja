<?php

namespace App\Http\Controllers;

class LandingController
{
    public function index()
    {
        $categorySection = [
            'label' => 'Kategori Utama',
            'title' => 'Mau Masak Apa Hari Ini?',
            'subtitle' => 'Pilih bahan masak sesuai kebutuhanmu. Dari potongan daging segar, ayam bumbu, ikan laut, hingga sayuran siap cemplung.'
        ];

        $categories = [
            [
                'id' => 1,
                'name' => 'Daging Sapi',
                'slug' => 'daging-sapi',
                'subtitle' => 'Slice, Sengkel, Ribeye & Giling',
                'badge' => 'Sertifikasi Halal',
                'image' => 'images/cat-daging.jpg',
                'color' => 'orange',
                'status' => 'active_landing',
                'description' => 'Daging sapi segar & frozen potongan higienis tanpa pengawet.'
            ],
            [
                'id' => 2,
                'name' => 'Ayam Segar & Olahan',
                'slug' => 'ayam-segar',
                'subtitle' => 'Fillet, Parting, Utuh & Ungkep',
                'badge' => 'Potong Segar Tiap Subuh',
                'image' => 'images/cat-ayam.jpg',
                'color' => 'yellow',
                'status' => 'active_landing',
                'description' => 'Ayam potong higienis standar cold-chain, plain maupun berbumbu.'
            ],
            [
                'id' => 3,
                'name' => 'Ikan & Seafood',
                'slug' => 'ikan-seafood',
                'subtitle' => 'Salmon, Gurame, Dori & Udang',
                'badge' => 'Segar Beku Kapal',
                'image' => 'images/cat-ikan.jpg',
                'color' => 'blue',
                'status' => 'active_landing',
                'description' => 'Fillet tanpa duri dan ikan utuh segar beku kaya nutrisi omega-3.'
            ],
            [
                'id' => 4,
                'name' => 'Sayuran Siap Olah',
                'slug' => 'sayuran-siap-olah',
                'subtitle' => 'Sayur Sup, Capcay & Sayur Segar',
                'badge' => 'Bersih Tinggal Cemplung',
                'image' => 'images/cat-sayur.jpg',
                'color' => 'green',
                'status' => 'active_landing',
                'description' => 'Sayuran organik & hidroponik cuci bersih praktis untuk masakan harian.'
            ],
            [
                'id' => 5,
                'name' => 'Frozen Food & Olahan',
                'slug' => 'frozen-food',
                'subtitle' => 'Nugget, Sosis, Bakso & Olahan',
                'badge' => 'Higienis Siap Masak',
                'image' => 'images/prod-ayam-bumbu.jpg',
                'color' => 'purple',
                'status' => 'active_catalog',
                'description' => 'Olahan daging dan ayam siap saji praktis untuk bekal keluarga.'
            ],
        ];

        $products = [
            [
                'id' => 1,
                'name' => 'Daging Sapi Shortplate Slice Premium',
                'category_id' => 1,
                'status' => 'Aktif',
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
                'category_id' => 2,
                'status' => 'Aktif',
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
                'category_id' => 3,
                'status' => 'Aktif',
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
                'category_id' => 4,
                'status' => 'Aktif',
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
                'category_id' => 2,
                'status' => 'Aktif',
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
                'category_id' => 1,
                'status' => 'Aktif',
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
                'category_id' => 2,
                'status' => 'Aktif',
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
                'category_id' => 3,
                'status' => 'Aktif',
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
                'category_id' => 1,
                'status' => 'Aktif',
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
                'category_id' => 1,
                'status' => 'Aktif',
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

        // Dynamic Product Count per Category
        foreach ($categories as &$cat) {
            $activeCount = count(array_filter($products, function($p) use ($cat) {
                return ($p['category_id'] ?? null) == $cat['id'] && ($p['status'] ?? 'Aktif') === 'Aktif';
            }));
            $cat['products_count'] = $activeCount;
            $cat['count'] = $activeCount . '+ Variasi';
        }
        unset($cat);

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
                'category' => 'Penyimpanan Daging',
                'badge_class' => 'bg-emerald-50 text-emerald-800',
                'read_time' => '5 mnt baca',
                'title' => 'Tips Menyimpan Daging Sapi Frozen agar Tetap Berkualitas',
                'excerpt' => 'Menyimpan daging sapi beku di rumah membutuhkan penanganan tepat agar tidak terjadi freezer burn dan rasa gurih alaminya tetap terjaga sempurna.',
                'image' => 'images/cat-daging.jpg',
                'published' => true,
                'content' => '<p class="lead text-base sm:text-lg text-gray-700 font-medium mb-4 leading-relaxed">Menyimpan daging sapi beku di rumah bukanlah sekadar memasukkannya ke dalam lemari es. Tanpa perlakuan yang tepat, daging dapat mengalami penurunan kualitas rasa, perubahan warna menjadi keabuan, atau bahkan mengalami <em>freezer burn</em> yang membuat serat daging menjadi kering dan keras saat dimasak.</p>
                <h3 class="text-lg sm:text-xl font-bold text-brand-dark mt-6 mb-3">1. Pertahankan Suhu Stabil di Bawah -18°C</h3>
                <p class="text-sm sm:text-base text-gray-600 mb-4 leading-relaxed">Kunci utama kesegaran daging sapi beku adalah kestabilan suhu. Pastikan freezer Anda disetel pada suhu minimal -18°C atau lebih rendah. Hindari meletakkan daging di rak pintu freezer karena area tersebut paling rentan mengalami fluktuasi suhu setiap kali pintu dibuka.</p>
                <h3 class="text-lg sm:text-xl font-bold text-brand-dark mt-6 mb-3">2. Pentingnya Kemasan Kedap Udara (Vacuum Pack)</h3>
                <p class="text-sm sm:text-base text-gray-600 mb-4 leading-relaxed">Paparan oksigen di dalam freezer adalah penyebab utama hilangnya kelembapan alami daging. Produk dari Sumber Protein Jogja telah dikemas secara <em>vacuum sealed</em> menggunakan plastik food-grade kedap udara. Jika kemasan sudah dibuka dan Anda hanya ingin memasak sebagian, selalu bungkus kembali sisa daging dengan cling wrap rapat sebelum dimasukkan ke dalam wadah kedap udara.</p>
                <div class="my-6 p-4 sm:p-5 rounded-modern bg-brand-soft-green/60 border border-brand-soft-green-border">
                    <h4 class="font-bold text-brand-primary text-sm sm:text-base mb-2">💡 Tips Penting Thawing:</h4>
                    <p class="text-xs sm:text-sm text-brand-dark leading-relaxed">Jangan pernah mencairkan daging sapi di suhu ruang terbuka atau merendamnya dalam air panas. Metode paling aman adalah memindahkannya ke chiller (kulkas bawah) semalam sebelum dimasak agar nutrisi dan sari daging (myoglobin) tidak terbuang percuma.</p>
                </div>
                <h3 class="text-lg sm:text-xl font-bold text-brand-dark mt-6 mb-3">3. Porsi Sekali Masak (Single Portioning)</h3>
                <p class="text-sm sm:text-base text-gray-600 mb-4 leading-relaxed">Membekukan ulang daging yang sudah dicairkan (refreezing) sangat tidak disarankan karena dapat merusak dinding sel daging dan memicu perkembangbiakan bakteri. Oleh karena itu, bagilah daging segar/curah ke dalam porsi-porsi kecil sesuai kebutuhan sekali santap keluarga sebelum pertama kali dibekukan.</p>
                <ul class="list-disc list-inside space-y-2 text-sm sm:text-base text-gray-600 mb-4 pl-2">
                    <li>Gunakan label tanggal pembekuan pada setiap wadah penyimpanan.</li>
                    <li>Letakkan daging yang baru dibeli di bagian belakang dan gunakan stok lama terlebih dahulu (prinsip FIFO).</li>
                    <li>Daging sapi slice idealnya dikonsumsi dalam rentang waktu 2–3 bulan untuk kenikmatan tekstur terbaik.</li>
                </ul>'
            ],
            [
                'id' => 2,
                'category' => 'Edukasi Produk',
                'badge_class' => 'bg-purple-50 text-purple-800',
                'read_time' => '6 mnt baca',
                'title' => 'Perbedaan Daging Fresh dan Frozen yang Perlu Kamu Ketahui',
                'excerpt' => 'Banyak mitos bahwa daging beku kurang bergizi dibandingkan daging segar. Faktanya, teknologi flash freezing modern justru mengunci nutrisi seketika.',
                'image' => 'images/prod-beef-slice.jpg',
                'published' => true,
                'content' => '<p class="lead text-base sm:text-lg text-gray-700 font-medium mb-4 leading-relaxed">Di masyarakat, masih sering berkembang anggapan bahwa daging segar (fresh meat) yang baru dipotong di pasar selalu lebih baik daripada daging beku (frozen meat). Namun secara sains pangan dan teknologi cold-chain modern, keduanya memiliki keunggulan masing-masing yang perlu dipahami oleh konsumen cerdas.</p>
                <h3 class="text-lg sm:text-xl font-bold text-brand-dark mt-6 mb-3">Teknologi Flash Freezing vs Pembekuan Tradisional</h3>
                <p class="text-sm sm:text-base text-gray-600 mb-4 leading-relaxed">Daging beku berkualitas di Sumber Protein Jogja diproses melalui teknik <em>blast freezing</em> (pembekuan cepat berkecepatan tinggi) sesaat setelah pemotongan dan pemeriksaan higienitas. Proses ini membentuk kristal es berukuran mikro yang tidak merusak serat daging, sehingga saat dicairkan, tekstur dan rasa daging tetap lezat sama seperti saat pertama kali dipotong.</p>
                <h3 class="text-lg sm:text-xl font-bold text-brand-dark mt-6 mb-3">Perbandingan Nutrisi dan Keamanan Bakteri</h3>
                <p class="text-sm sm:text-base text-gray-600 mb-4 leading-relaxed">Secara kandungan protein, zat besi, dan vitamin B kompleks, tidak ada perbedaan signifikan antara daging fresh dan daging yang dibekukan dengan benar. Justru dari sisi mikrobiologis, suhu beku di bawah -18°C menonaktifkan pertumbuhan bakteri pembusuk (seperti Salmonella dan E. coli) yang kerap berkembang cepat pada daging fresh yang terpapar udara pasar terbuka selama berjam-jam.</p>
                <div class="my-6 p-4 sm:p-5 rounded-modern bg-brand-cream/80 border border-gray-200">
                    <h4 class="font-bold text-brand-dark text-sm sm:text-base mb-2">📋 Ringkasan Perbandingan:</h4>
                    <ul class="list-disc list-inside space-y-1.5 text-xs sm:text-sm text-gray-600">
                        <li><strong>Daging Fresh:</strong> Cocok untuk olahan yang langsung dimasak hari itu juga (misal: sop balungan atau sate fresh).</li>
                        <li><strong>Daging Frozen:</strong> Sangat higienis, umur simpan hingga berbulan-bulan, cocok untuk meal-prep keluarga dan suplai usaha kuliner yang butuh konsistensi harga.</li>
                    </ul>
                </div>
                <h3 class="text-lg sm:text-xl font-bold text-brand-dark mt-6 mb-3">Konsistensi Bentuk dan Potongan</h3>
                <p class="text-sm sm:text-base text-gray-600 mb-4 leading-relaxed">Daging beku memungkinkan pemotongan presisi tinggi, seperti beef slice tipis 1.5mm untuk shabu-shabu atau sukiyaki. Potongan setipis ini hampir mustahil dihasilkan secara konsisten dari daging fresh suhu ruang tanpa mesin pengiris bersuhu dingin terkontrol.</p>'
            ],
            [
                'id' => 3,
                'category' => 'Manajemen Dapur',
                'badge_class' => 'bg-amber-50 text-amber-800',
                'read_time' => '5 mnt baca',
                'title' => 'Cara Menyimpan Ayam Fillet agar Praktis untuk Masak Harian',
                'excerpt' => 'Ayam fillet adalah primadona protein harian keluarga. Pelajari trik membagi porsi per masak dan marinasi dini sebelum masuk freezer.',
                'image' => 'images/cat-ayam.jpg',
                'published' => true,
                'content' => '<p class="lead text-base sm:text-lg text-gray-700 font-medium mb-4 leading-relaxed">Dada ayam fillet dan paha ayam fillet merupakan bahan masakan paling fleksibel di dapur. Kandungan proteinnya tinggi, rendah lemak, dan sangat mudah diolah menjadi beragam masakan mulai dari ayam katsu, tumisan oriental, hingga sup hangat keluarga. Namun, menyimpan ayam fillet dalam satu bongkahan besar di freezer sering kali merepotkan saat waktu masak tiba.</p>
                <h3 class="text-lg sm:text-xl font-bold text-brand-dark mt-6 mb-3">1. Hindari Mencuci Ayam dengan Air Mengalir Sebelum Dibekukan</h3>
                <p class="text-sm sm:text-base text-gray-600 mb-4 leading-relaxed">Mencuci daging ayam mentah di bawah kran air dapat memercikkan bakteri ke area wastafel dan peralatan dapur lain. Ayam fillet dari Sumber Protein Jogja telah dibersihkan secara higienis dalam ruangan terkontrol. Cukup keringkan permukaannya dengan kitchen paper bersih sebelum diporsi dan dikemas.</p>
                <h3 class="text-lg sm:text-xl font-bold text-brand-dark mt-6 mb-3">2. Trik Pembekuan Rata (Flat Freeze Technique)</h3>
                <p class="text-sm sm:text-base text-gray-600 mb-4 leading-relaxed">Susun fillet ayam secara mendatar (tidak bertumpuk tebal) di dalam kantong zip-lock atau wadah tipis. Cara pembekuan pipih ini memiliki dua keuntungan besar: menghemat ruang penyimpanan di freezer dan mempercepat proses pencairan (thawing) hingga 3 kali lebih cepat dibandingkan ayam yang membeku menggumpal.</p>
                <div class="my-6 p-4 sm:p-5 rounded-modern bg-brand-soft-green/60 border border-brand-soft-green-border">
                    <h4 class="font-bold text-brand-primary text-sm sm:text-base mb-2">🍗 Rahasia Marinasi Awal (Early Marination):</h4>
                    <p class="text-xs sm:text-sm text-brand-dark leading-relaxed">Bumbui potongan ayam fillet dengan bawang putih, garam, lada, dan sedikit minyak zaitun sebelum dibekukan. Saat ayam mengalami proses defrosting di chiller keesokan harinya, bumbu akan terserap jauh lebih dalam hingga serat terdalam daging ayam.</p>
                </div>
                <h3 class="text-lg sm:text-xl font-bold text-brand-dark mt-6 mb-3">3. Pemisahan Berdasarkan Jenis Masakan</h3>
                <p class="text-sm sm:text-base text-gray-600 mb-4 leading-relaxed">Siapkan potongan sesuai rencana menu mingguan: potong dadu untuk sate/tumisan, potong memanjang untuk nugget/strip, dan biarkan utuh pipih untuk steak atau katsu. Beri label penanda pada wadah agar siapa pun yang memasak di rumah dapat langsung mengambil paket yang tepat tanpa kebingungan.</p>'
            ],
            [
                'id' => 4,
                'category' => 'Seafood Guide',
                'badge_class' => 'bg-sky-50 text-sky-800',
                'read_time' => '6 mnt baca',
                'title' => 'Tips Memilih Ikan dan Seafood untuk Menu Keluarga',
                'excerpt' => 'Memastikan ikan dan seafood tetap segar tanpa aroma amis berlebih dimulai dari pemilihan kualitas pembekuan dan cara penanganan pertama.',
                'image' => 'images/cat-ikan.jpg',
                'published' => true,
                'content' => '<p class="lead text-base sm:text-lg text-gray-700 font-medium mb-4 leading-relaxed">Ikan dan seafood adalah sumber nutrisi luar biasa kaya asam lemak Omega-3, yodium, dan protein berkualitas tinggi untuk tumbuh kembang anak dan kesehatan jantung keluarga. Namun, banyak ibu rumah tangga ragu membeli ikan beku karena khawatir aromanya amis atau teksturnya lembek saat digoreng.</p>
                <h3 class="text-lg sm:text-xl font-bold text-brand-dark mt-6 mb-3">Ciri Ikan Beku yang Berkualitas Prima</h3>
                <p class="text-sm sm:text-base text-gray-600 mb-4 leading-relaxed">Ikan beku yang diproses dengan standar higienis memiliki lapisan es pelindung bening dan tipis (ice glazing) yang berfungsi mencegah kontak langsung dengan udara luar. Hindari ikan beku yang memiliki tumpukan kristal salju tebal keruh di dalam kemasan, karena itu menandakan ikan pernah mencair dan dibekukan kembali.</p>
                <h3 class="text-lg sm:text-xl font-bold text-brand-dark mt-6 mb-3">Menghilangkan Bau Amis dengan Bahan Alami</h3>
                <p class="text-sm sm:text-base text-gray-600 mb-4 leading-relaxed">Setelah fillet ikan dicairkan di chiller, lumuri dengan perasan air jeruk nipis atau lemon dan parutan jahe segar selama 10–15 menit. Asam sitrat pada jeruk akan menetralkan senyawa trimetilamin penyebab bau amis pada seafood tanpa merusak cita rasa gurih daging ikan.</p>
                <div class="my-6 p-4 sm:p-5 rounded-modern bg-brand-cream/80 border border-gray-200">
                    <h4 class="font-bold text-brand-dark text-sm sm:text-base mb-2">🐟 Pilihan Ikan Terbaik untuk Anak & Balita:</h4>
                    <p class="text-xs sm:text-sm text-gray-600 leading-relaxed">Fillet salmon Norwegia dan fillet dori/gurame tanpa duri boneless adalah pilihan paling aman untuk menu MPASI dan anak-anak. Dagingnya lembut, bebas duri halus, dan cepat matang saat dikukus atau dipanggang ringan dengan mentega.</p>
                </div>
                <h3 class="text-lg sm:text-xl font-bold text-brand-dark mt-6 mb-3">Teknik Memasak Ikan Fillet agar Tidak Hancur</h3>
                <p class="text-sm sm:text-base text-gray-600 mb-4 leading-relaxed">Saat menggoreng atau menumis fillet ikan, pastikan wajan sudah benar-benar panas dan lapisi sedikit tepung tipis pada permukaan ikan. Jangan membolak-balik ikan terlalu sering; cukup balik satu kali setelah sisi bawah terbentuk lapisan renyah keemasan.</p>'
            ],
            [
                'id' => 5,
                'category' => 'Solusi Praktis',
                'badge_class' => 'bg-emerald-50 text-emerald-800',
                'read_time' => '5 mnt baca',
                'title' => 'Kenapa Produk Ready to Cook Cocok untuk Keluarga Sibuk?',
                'excerpt' => 'Kombinasi bahan protein yang telah dipotong rapi dan dimarinasi bumbu rempah alami menghemat waktu persiapan dapur hingga 70%.',
                'image' => 'images/prod-ayam-bumbu.jpg',
                'published' => true,
                'content' => '<p class="lead text-base sm:text-lg text-gray-700 font-medium mb-4 leading-relaxed">Aktivitas harian yang padat antara pekerjaan kantor, urusan rumah tangga, dan mengasuh anak kerap membuat waktu memasak menjadi beban tersendiri. Produk <em>Ready to Cook</em> (RTC) hadir sebagai jalan tengah terbaik: menyajikan makanan rumahan bergizi tanpa harus menghabiskan waktu berjam-jam mengulek bumbu dan membersihkan sisa potongan.</p>
                <h3 class="text-lg sm:text-xl font-bold text-brand-dark mt-6 mb-3">1. Memangkas Waktu Persiapan Dapur (Food Prep)</h3>
                <p class="text-sm sm:text-base text-gray-600 mb-4 leading-relaxed">Tahapan paling melelahkan saat memasak biasanya bukanlah proses menggoreng atau merebusnya, melainkan membersihkan lemak, memotong daging, mengupas bawang, serta menghaluskan rempah. Dengan ayam ungkep bumbu kuning atau beef slice marinasi lada hitam, Anda cukup membuka kemasan dan langsung memasaknya dalam 7–10 menit.</p>
                <h3 class="text-lg sm:text-xl font-bold text-brand-dark mt-6 mb-3">2. Jauh Lebih Sehat Dibandingkan Makanan Cepat Saji</h3>
                <p class="text-sm sm:text-base text-gray-600 mb-4 leading-relaxed">Berbeda dengan produk olahan pabrik berpengawet tinggi atau makanan siap saji dari luar yang tinggi garam dan minyak jenuh, produk Ready to Cook dari Sumber Protein Jogja diolah dengan rempah nusantara asli tanpa bahan pengawet kimia sintetis. Anda memegang kendali penuh atas minyak dan teknik memasak di rumah.</p>
                <div class="my-6 p-4 sm:p-5 rounded-modern bg-brand-soft-green/60 border border-brand-soft-green-border">
                    <h4 class="font-bold text-brand-primary text-sm sm:text-base mb-2">⏱️ Hemat Anggaran & Bebas Food Waste:</h4>
                    <p class="text-xs sm:text-sm text-brand-dark leading-relaxed">Membeli bumbu satuan dalam jumlah banyak sering kali berakhir terbuang karena membusuk di lemari pendingin. Produk siap masak memastikan takaran bumbu presisi dan tidak ada bahan baku yang terbuang sia-sia (zero food waste).</p>
                </div>
                <h3 class="text-lg sm:text-xl font-bold text-brand-dark mt-6 mb-3">3. Fleksibilitas Metode Masak Modern</h3>
                <p class="text-sm sm:text-base text-gray-600 mb-4 leading-relaxed">Produk siap masak sangat adaptif dengan peralatan modern seperti Air Fryer, Teflon grill pan, maupun oven microwave. Anda bisa menyiapkan sarapan bergizi tinggi atau makan malam lezat keluarga hanya dengan satu sentuhan tombol timer.</p>'
            ],
            [
                'id' => 6,
                'category' => 'Panduan Belanja',
                'badge_class' => 'bg-rose-50 text-rose-800',
                'read_time' => '5 mnt baca',
                'title' => 'Plain atau Berbumbu? Cara Memilih Produk Sesuai Kebutuhan',
                'excerpt' => 'Pahami kapan harus memilih daging polos (plain) untuk resep kustom dan kapan produk berbumbu menjadi penyelamat waktu santap malam.',
                'image' => 'images/hero-3.jpg',
                'published' => true,
                'content' => '<p class="lead text-base sm:text-lg text-gray-700 font-medium mb-4 leading-relaxed">Saat berbelanja bahan makanan beku di Sumber Protein Jogja, Anda akan menemukan dua kategori utama: varian <strong>Plain (Polos)</strong> dan varian <strong>Berbumbu / Marinated (Siap Masak)</strong>. Memahami perbedaan fungsi keduanya akan membantu Anda merencanakan belanja mingguan secara lebih hemat dan efisien.</p>
                <h3 class="text-lg sm:text-xl font-bold text-brand-dark mt-6 mb-3">Kapan Sebaiknya Memilih Produk Plain?</h3>
                <p class="text-sm sm:text-base text-gray-600 mb-4 leading-relaxed">Varian Plain adalah daging murni 100% tanpa tambahan bumbu apa pun. Pilihan ini sangat tepat jika Anda:</p>
                <ul class="list-disc list-inside space-y-2 text-sm sm:text-base text-gray-600 mb-4 pl-2">
                    <li>Ingin membuat masakan berkuah khas seperti Rawon, Soto Daging, Semur, atau Gulai dengan resep rahasia keluarga.</li>
                    <li>Menyiapkan makanan pendamping ASI (MPASI) bayi yang memerlukan kontrol ketat terhadap kadar natrium dan bumbu aromatik.</li>
                    <li>Sedang menjalani diet khusus (seperti fitness clean-eating, diet rendah garam, atau keto).</li>
                </ul>
                <h3 class="text-lg sm:text-xl font-bold text-brand-dark mt-6 mb-3">Kapan Produk Berbumbu Menjadi Pilihan Terbaik?</h3>
                <p class="text-sm sm:text-base text-gray-600 mb-4 leading-relaxed">Produk berbumbu adalah penyelamat hari kerja yang sibuk. Bumbu telah dimarinasi secara vakum sehingga meresap hingga serat terdalam. Cocok untuk hidangan cepat saji seperti grill daging lada hitam, ayam goreng lengkuas, atau teriyaki bowl kilat.</p>
                <div class="my-6 p-4 sm:p-5 rounded-modern bg-brand-cream/80 border border-gray-200">
                    <h4 class="font-bold text-brand-dark text-sm sm:text-base mb-2">🎯 Formula Belanja Ideal Mingguan:</h4>
                    <p class="text-xs sm:text-sm text-gray-600 leading-relaxed">Kombinasikan <strong>60% produk plain</strong> (sebagai stok bahan dasar serbaguna) dan <strong>40% produk berbumbu</strong> (sebagai menu cepat di hari-hari padat aktivitas) agar variasi menu keluarga tidak pernah membosankan.</p>
                </div>'
            ],
            [
                'id' => 7,
                'category' => 'Organisasi Freezer',
                'badge_class' => 'bg-indigo-50 text-indigo-800',
                'read_time' => '6 mnt baca',
                'title' => 'Tips Mengatur Stok Frozen Food di Freezer Rumah',
                'excerpt' => 'Terapkan prinsip FIFO (First In, First Out) dan penataan vertikal agar tidak ada bahan makanan yang terlupakan dan terbuang sia-sia.',
                'image' => 'images/cat-sayur.jpg',
                'published' => true,
                'content' => '<p class="lead text-base sm:text-lg text-gray-700 font-medium mb-4 leading-relaxed">Freezer yang berantakan tidak hanya menyulitkan pencarian bahan saat hendak memasak, tetapi juga memicu pemborosan makanan karena ada paket daging atau sayuran yang tertimbun di dasar lemari es hingga melewati masa simpan idealnya.</p>
                <h3 class="text-lg sm:text-xl font-bold text-brand-dark mt-6 mb-3">1. Sistem Zonasi Kategori Makanan</h3>
                <p class="text-sm sm:text-base text-gray-600 mb-4 leading-relaxed">Bagi ruang freezer Anda ke dalam zona-zona yang jelas: rak paling bawah untuk daging merah mentah, rak tengah untuk unggas dan seafood, serta rak paling atas untuk produk siap masak (RTC), sayuran beku, dan es batu. Hal ini juga mencegah risiko kontaminasi silang aroma antar bahan.</p>
                <h3 class="text-lg sm:text-xl font-bold text-brand-dark mt-6 mb-3">2. Penataan Berdiri / Vertikal (Metode File Folder)</h3>
                <p class="text-sm sm:text-base text-gray-600 mb-4 leading-relaxed">Alih-alih menumpuk kemasan plastik secara horizontal ke atas, susun kemasan secara vertikal seperti menyusun buku di rak. Dengan cara ini, Anda bisa melihat seluruh isi freezer dalam satu pandangan mata tanpa perlu membongkar tumpukan di atasnya.</p>
                <div class="my-6 p-4 sm:p-5 rounded-modern bg-brand-soft-green/60 border border-brand-soft-green-border">
                    <h4 class="font-bold text-brand-primary text-sm sm:text-base mb-2">🏷️ Aturan Emas FIFO (First In, First Out):</h4>
                    <p class="text-xs sm:text-sm text-brand-dark leading-relaxed">Selalu letakkan produk yang baru dibeli di barisan belakang atau bawah, dan majukan stok yang lebih lama ke bagian depan agar segera diolah lebih dahulu.</p>
                </div>
                <h3 class="text-lg sm:text-xl font-bold text-brand-dark mt-6 mb-3">3. Beri Ruang untuk Sirkulasi Udara Dingin</h3>
                <p class="text-sm sm:text-base text-gray-600 mb-4 leading-relaxed">Jangan mengisi freezer hingga kapasitas 100% sesak menutupi lubang ventilasi (air vents). Sisakan setidaknya 15–20% ruang kosong agar sirkulasi udara beku dapat mengalir merata ke seluruh sudut makanan, menjaga suhu konstan di -18°C dan menghemat daya listrik lemari es.</p>'
            ],
            [
                'id' => 8,
                'category' => 'Teknik Memasak',
                'badge_class' => 'bg-amber-50 text-amber-800',
                'read_time' => '6 mnt baca',
                'title' => 'Panduan Memasak Produk Frozen agar Hasilnya Tetap Lezat',
                'excerpt' => 'Dari teknik searing daging slice tanpa mencairkan berlebih hingga menggoreng ayam berbumbu agar tidak gosong di luar namun matang sempurna di dalam.',
                'image' => 'images/know-thawing.jpg',
                'published' => true,
                'content' => '<p class="lead text-base sm:text-lg text-gray-700 font-medium mb-4 leading-relaxed">Memasak bahan makanan beku memerlukan pemahaman tentang reaksi termal antara suhu dingin bahan dan panas wajan. Kesalahan umum yang sering terjadi adalah memasukkan bahan yang masih membeku batu ke dalam minyak bersuhu sangat tinggi, yang mengakibatkan bagian luar gosong sementara bagian dalam masih dingin atau mentah.</p>
                <h3 class="text-lg sm:text-xl font-bold text-brand-dark mt-6 mb-3">1. Daging Sapi Slice: Cepat dan Suhu Tinggi</h3>
                <p class="text-sm sm:text-base text-gray-600 mb-4 leading-relaxed">Untuk beef slice tipis (shortplate / sengkel slice), Anda sebenarnya tidak perlu mencairkannya hingga lemas total. Daging slice yang setengah beku (semi-thawed) justru lebih mudah dipisahkan lembar demi lembar. Masak di atas wajan anti lengket yang sangat panas selama 1–2 menit saja agar sari daging terkunci dan teksturnya tetap juicy.</p>
                <h3 class="text-lg sm:text-xl font-bold text-brand-dark mt-6 mb-3">2. Ayam Ungkep & Produk Berbumbu: Api Sedang-Kecil</h3>
                <p class="text-sm sm:text-base text-gray-600 mb-4 leading-relaxed">Bahan yang dimarinasi mengandung gula alami, bawang, dan rempah yang mudah terkaramelisasi. Saat menggoreng ayam ungkep, gunakan api sedang-kecil dan pastikan ayam sudah mencair sempurna hingga ke tulang sebelum masuk penggorengan. Hal ini memastikan panas merata dan bumbu tidak pahit terbakar.</p>
                <div class="my-6 p-4 sm:p-5 rounded-modern bg-brand-cream/80 border border-gray-200">
                    <h4 class="font-bold text-brand-dark text-sm sm:text-base mb-2">🥦 Trik Sayuran Beku (Frozen Veggies):</h4>
                    <p class="text-xs sm:text-sm text-gray-600 leading-relaxed">Jangan mencairkan sayuran beku (seperti jagung manis, kacang polong, wortel mix) sebelum dimasak. Masukkan sayuran langsung dari freezer ke dalam sup mendidih atau tumisan panas agar teksturnya tetap renyah dan warnanya tetap cerah menggugah selera.</p>
                </div>
                <h3 class="text-lg sm:text-xl font-bold text-brand-dark mt-6 mb-3">3. Istirahatkan Daging Setelah Dimasak (Resting)</h3>
                <p class="text-sm sm:text-base text-gray-600 mb-4 leading-relaxed">Khusus untuk potongan steak atau daging tebal, jangan langsung memotong daging sesaat setelah diangkat dari pan. Biarkan beristirahat (resting) selama 3–5 menit di atas talenan agar sari daging yang berkumpul di tengah dapat terdistribusi kembali ke seluruh serat daging.</p>'
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

        // Dynamic Product Count calculation per category from active products
        foreach ($categories as &$cat) {
            $activeCount = count(array_filter($products, function($p) use ($cat) {
                return ($p['category_id'] ?? null) == $cat['id'] && ($p['status'] ?? 'Aktif') === 'Aktif';
            }));
            $cat['products_count'] = $activeCount;
            $cat['count'] = $activeCount . '+ Variasi';
        }
        unset($cat);

        return view('landing', compact(
            'categorySection',
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
