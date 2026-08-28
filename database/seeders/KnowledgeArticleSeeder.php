<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\KnowledgeCategory;
use App\Models\KnowledgeArticle;
use App\Services\KnowledgeArticleParser;

class KnowledgeArticleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = KnowledgeCategory::all()->keyBy('slug');

        $articles = [
            [
                'category_slug' => 'tips-penyimpanan',
                'title' => '5 Tips Menyimpan Daging Beku Agar Tetap Segar & Higienis',
                'slug' => '5-tips-menyimpan-daging-beku-agar-tetap-segar-higienis',
                'status' => 'published',
                'image' => 'images/know-thawing.jpg',
                'excerpt' => 'Menyimpan daging sapi dan ayam beku memerlukan teknik pengemasan kedap udara dan kestabilan suhu freezer di bawah -18°C agar nutrisi dan keempukan serat tetap terjaga sempurna.',
                'raw_content' => "Daging beku merupakan solusi praktis bagi keluarga modern untuk menjaga ketersediaan bahan pangan berprotein tinggi di rumah. Namun, proses penyimpanan yang keliru dapat merusak kualitas rasa, tekstur, hingga memicu pertumbuhan bakteri berbahaya.\n\nBerikut adalah 5 langkah krusial untuk menjaga daging beku Anda tetap dalam kondisi prima:\n\n1. Gunakan Kemasan Kedap Udara (Vacuum Sealed)\nUdara adalah musuh utama daging beku karena memicu freezer burn—kondisi di mana permukaan daging mengering dan berubah warna keabu-abuan. Bagi daging menjadi porsi sekali masak sebelum dibekukan.\n\n2. Pertahankan Suhu Freezer Stabil di Bawah -18°C\nSuhu yang berfluktuasi akibat sering membuka tutup pintu freezer akan menyebabkan kristal es membesar dan merusak serat otot daging saat dimasak.\n\n3. Jangan Pernah Membekukan Kembali Daging yang Sudah Cair (Thawed)\nDaging yang telah dicairkan memiliki kandungan air bebas yang tinggi. Jika dibekukan ulang, struktur sel daging akan rusak dan bakteri dapat berkembang biak dengan cepat.\n\n4. Beri Label Tanggal Penyimpanan\nSelalu catat tanggal pembelian dan tanggal mulai disimpan di freezer. Terapkan prinsip FIFO (First In, First Out) agar konsumsi selalu optimal.\n\n5. Pisahkan Daging Mentah dari Makanan Siap Santap\nGunakan wadah terpisah untuk mencegah kontaminasi silang cairan daging mentah ke bahan makanan lain di dalam freezer.",
                'sort_order' => 1,
            ],
            [
                'category_slug' => 'edukasi-dapur',
                'title' => 'Panduan Thawing Daging yang Benar Tanpa Menghilangkan Nutrisi',
                'slug' => 'panduan-thawing-daging-yang-benar-tanpa-menghilangkan-nutrisi',
                'status' => 'published',
                'image' => 'images/know-thawing.jpg',
                'excerpt' => 'Mencairkan daging beku di suhu ruang atau merendamnya dalam air panas berisiko merusak rasa dan membiakkan bakteri. Simak teknik thawing chiller yang higienis.',
                'raw_content' => "Thawing atau proses pencairan daging beku sering kali dianggap sepele, padahal metode yang salah dapat merusak tekstur daging dan membiarkan bakteri berkembang biak dengan sangat cepat di zona bahaya suhu (5°C - 60°C).\n\nTiga Metode Thawing Terbaik yang Direkomendasikan:\n\n1. Metode Kulkas / Chiller (Metode Paling Aman)\nPindahkan daging dari freezer ke rak kulkas bawah selama 8 hingga 12 jam sebelum diolah. Proses pencairan yang lambat ini menjaga kelembapan alami daging dan mencegah kebocoran sari daging (drip loss).\n\n2. Metode Air Dingin Mengalir (Metode Cepat)\nJika waktu Anda terbatas, masukkan daging dalam plastik klip kedap air, lalu rendam di dalam mangkuk berisi air dingin atau di bawah kucuran air mengalir pelan. Ganti air setiap 30 menit.\n\n3. Metode Microwave Defrost (Metode Instan)\nGunakan fitur defrost dengan daya rendah. Pastikan daging langsung dimasak setelah proses defrost selesai.",
                'sort_order' => 2,
            ],
            [
                'category_slug' => 'informasi-produk',
                'title' => 'Mengenal Perbedaan Daging Sapi Shortplate dan Ribeye untuk BBQ',
                'slug' => 'mengenal-perbedaan-daging-sapi-shortplate-dan-ribeye-untuk-bbq',
                'status' => 'published',
                'image' => 'images/cat-daging.jpg',
                'excerpt' => 'Bagi penggemar grill dan shabu-shabu, pahami karakteristik marbling, ketebalan lemak, dan tingkat keempukan antara potongan Shortplate slice dan Ribeye steak cut.',
                'raw_content' => "Memilih potongan daging sapi yang tepat adalah kunci utama keberhasilan sesi memanggang BBQ bersama keluarga.\n\n1. Karakteristik Daging Shortplate Slice:\nShortplate berasal dari bagian perut bawah sapi. Potongan ini memiliki rasio lemak dan daging yang seimbang (sekitar 30-40% lemak), sehingga saat dipanggang di atas grill pan akan mengeluarkan aroma gurih yang intens tanpa perlu tambahan mentega berlebih.\n\n2. Karakteristik Daging Ribeye:\nRibeye berasal dari bagian rusuk sapi. Potongan ini terkenal dengan marbling intrakulit yang lembut dan mata lemak di bagian tengah. Teksturnya sangat empuk dan juicy saat dimasak dengan tingkat kematangan medium-well.",
                'sort_order' => 3,
            ],
            [
                'category_slug' => 'resep-masakan',
                'title' => 'Cara Memasak Ikan Gurame Agar Gurih dan Tidak Berbau Tanah',
                'slug' => 'cara-memasak-ikan-gurame-agar-gurih-dan-tidak-berbau-tanah',
                'status' => 'published',
                'image' => 'images/cat-ikan.jpg',
                'excerpt' => 'Ikan air tawar seperti gurame membutuhkan perlakuan khusus pada pembersihan insang, baluran jeruk nipis, dan bumbu marinasi rempah agar cita rasanya segar dan gurih.',
                'raw_content' => "Ikan gurame adalah salah satu lauk favorit keluarga Indonesia, baik digoreng terbang, dibakar bumbu rujak, maupun dimasak asam manis.\n\nLangkah Mengatasi Bau Lumpur pada Ikan Air Tawar:\n1. Bersihkan selaput hitam di dalam rongga perut ikan hingga benar-benar bersih dan buang insangnya.\n2. Lumuri ikan dengan air perasan jeruk nipis dan garam kasar selama 15 menit, lalu bilas air bersih.\n3. Rendam dalam larutan air asam jawa atau parutan jahe dan ketumbar sebelum digoreng dalam minyak panas melimpah.",
                'sort_order' => 4,
            ],
            [
                'category_slug' => 'tips-penyimpanan',
                'title' => 'Tips Menyimpan Sayuran Hijau di Chiller Supaya Tetap Renyah 7 Hari',
                'slug' => 'tips-menyimpan-sayuran-hijau-di-chiller-supaya-tetap-renyah-7-hari',
                'status' => 'published',
                'image' => 'images/cat-sayur.jpg',
                'excerpt' => 'Sayuran daun seperti bayam, kangkung, dan sawi rentan layu dan membusuk jika terkena kelembapan berlebih. Terapkan metode bungkus kertas tisu dan kontainer kedap.',
                'raw_content' => "Sayuran daun hijau membutuhkan sirkulasi udara yang terkontrol dan perlindungan dari tetesan embun kondensasi kulkas.\n\nLangkah-langkah Penyimpanan:\n1. Jangan mencuci sayuran jika belum akan dimasak hari itu.\n2. Potong bagian akar yang kotor dan buang daun yang sudah menguning.\n3. Bungkus sayuran dengan kertas koran polos atau kitchen towel kering.\n4. Masukkan ke dalam food container atau kantong plastik berlubang dan letakkan di laci khusus sayuran (crisper drawer).",
                'sort_order' => 5,
            ],
            [
                'category_slug' => 'resep-masakan',
                'title' => 'Rahasia Marinasi Ayam Ungkep Bumbu Kuning Meresap Sampai ke Tulang',
                'slug' => 'rahasia-marinasi-ayam-ungkep-bumbu-kuning-meresap-sampai-ke-tulang',
                'status' => 'published',
                'image' => 'images/prod-ayam-bumbu.jpg',
                'excerpt' => 'Kombinasi takaran lengkuas, kunyit bakar, ketumbar sangrai, dan daun salam yang tepat saat proses slow cooking menghasilkan ayam ungkep dengan aroma memikat.',
                'raw_content' => "Ayam ungkep bumbu kuning adalah stok lauk wajib di setiap freezer rumah tangga. Kuncinya terletak pada proses perebusan api kecil (simmering) yang memungkinkan bumbu meresap perlahan ke dalam pori-pori daging tanpa membuat tekstur kulit ayam hancur.",
                'sort_order' => 6,
            ],
            [
                'category_slug' => 'edukasi-dapur',
                'title' => 'Manfaat Mengonsumsi Protein Berkualitas untuk Imunitas Keluarga',
                'slug' => 'manfaat-mengonsumsi-protein-berkualitas-untuk-imunitas-keluarga',
                'status' => 'published',
                'image' => 'images/hero-1.jpg',
                'excerpt' => 'Asam amino esensial dari protein hewani dan nabati berperan penting dalam pembentukan sel antibodi dan regenerasi jaringan tubuh sehari-hari.',
                'raw_content' => "Protein adalah zat pembangun utama dalam tubuh manusia. Mengonsumsi variasi protein hewani seperti daging merah, unggas, telur, dan ikan secara berimbang akan memenuhi kebutuhan zat besi dan vitamin B12 harian.",
                'sort_order' => 7,
            ],
            [
                'category_slug' => 'informasi-produk',
                'title' => 'Perbedaan Ayam Broiler, Ayam Pejantan, dan Ayam Kampung',
                'slug' => 'perbedaan-ayam-broiler-ayam-pejantan-dan-ayam-kampung',
                'status' => 'published',
                'image' => 'images/cat-ayam.jpg',
                'excerpt' => 'Karakteristik serat daging, kadar lemak, dan kecocokan jenis masakan antara ayam pedaging broiler, ayam pejantan gurih, dan ayam kampung asli.',
                'raw_content' => "Setiap jenis ayam memiliki karakteristik unik. Ayam broiler bertekstur empuk dan cepat matang, ayam pejantan memiliki kekenyalan mirip ayam kampung dengan harga lebih terjangkau, sedangkan ayam kampung kaya akan kaldu gurih untuk sajian sup obat tradisional.",
                'sort_order' => 8,
            ],
            [
                'category_slug' => 'edukasi-dapur',
                'title' => 'Panduan Memilih Ikan Laut Segar: Ciri Mata, Insang, dan Sisik',
                'slug' => 'panduan-memilih-ikan-laut-segar-ciri-mata-insang-sisik',
                'status' => 'published',
                'image' => 'images/cat-ikan.jpg',
                'excerpt' => 'Kenali tanda kesegaran ikan laut dengan memeriksa mata yang bening cembung, insang merah segar, dan elastisitas daging saat ditekan.',
                'raw_content' => "Membeli ikan laut yang segar memastikan sajian masakan Anda tidak amis dan aman bagi kesehatan pencernaan seluruh anggota keluarga.",
                'sort_order' => 9,
            ],
            [
                'category_slug' => 'resep-masakan',
                'title' => 'Cara Membuat Kaldu Sapi Bening & Bebas Lemak Menggumpal',
                'slug' => 'cara-membuat-kaldu-sapi-bening-bebas-lemak-menggumpal',
                'status' => 'published',
                'image' => 'images/cat-daging.jpg',
                'excerpt' => 'Teknik blanching tulang dan daging iga sapi sebelum direbus lama dengan mirepoix wortel dan seledri untuk kaldu gurih nan jernih.',
                'raw_content' => "Kunci kaldu sapi yang jernih adalah merebus tulang dalam air mendidih selama 5 menit pertama lalu membuang air rebusan kotor tersebut sebelum memulai perebusan panjang.",
                'sort_order' => 10,
            ],
            [
                'category_slug' => 'informasi-produk',
                'title' => 'Mengenal Sistem Cold Chain pada Distribusi Frozen Food',
                'slug' => 'mengenal-sistem-cold-chain-pada-distribusi-frozen-food',
                'status' => 'published',
                'image' => 'images/hero-2.jpg',
                'excerpt' => 'Bagaimana rantai dingin menjaga temperatur produk di bawah suhu beku mulai dari pemotongan, penyimpanan gudang, hingga pengantaran ke pintu rumah Anda.',
                'raw_content' => "Cold Chain memastikan pertumbuhan bakteri terhenti dan menjaga kesegaran daging seolah-olah baru saja dipotong dari peternakan.",
                'sort_order' => 11,
            ],
            [
                'category_slug' => 'edukasi-dapur',
                'title' => 'Tips Memotong Daging Sapi Melawan Serat Agar Empuk Tanpa Pengempuk',
                'slug' => 'tips-memotong-daging-sapi-melawan-serat-agar-empuk-tanpa-pengempuk',
                'status' => 'published',
                'image' => 'images/prod-beef-slice.jpg',
                'excerpt' => 'Arah potongan pisau terhadap serat otot daging menentukan keempukan gigitan saat dinikmati setelah matang.',
                'raw_content' => "Memotong tegak lurus melintasi arah alur serat (across the grain) akan memperpendek serat otot sehingga daging tidak terasa liat saat dikunyah.",
                'sort_order' => 12,
            ],
            [
                'category_slug' => 'resep-masakan',
                'title' => 'Ide Bekal Sekolah Anak Sehat dengan Olahan Daging & Sayuran',
                'slug' => 'ide-bekal-sekolah-anak-sehat-dengan-olahan-daging-sayuran',
                'status' => 'published',
                'image' => 'images/prod-sayur-mix.jpg',
                'excerpt' => 'Inspirasi menu bento bergizi seimbang yang cepat disiapkan di pagi hari menggunakan bahan siap olah Sumber Protein Jogja.',
                'raw_content' => "Kombinasi stik nugget ayam homemade, brokoli kukus mentega, dan telur puyuh kecap menjadi favorit bekal praktis dan bergizi.",
                'sort_order' => 13,
            ],
            [
                'category_slug' => 'informasi-produk',
                'title' => 'Mengenal Sertifikasi Halal pada Produk Daging Potong',
                'slug' => 'mengenal-sertifikasi-halal-pada-produk-daging-potong',
                'status' => 'published',
                'image' => 'images/hero-1.jpg',
                'excerpt' => 'Jaminan kepatuhan syariat dalam proses penyembelihan, penanganan higienis, dan sanitasi tempat pemotongan hewan terakreditasi.',
                'raw_content' => "Sumber Protein Jogja menjamin 100% daging sapi dan unggas berasal dari Rumah Potong Hewan resmi bersertifikasi halal MUI & BPJPH.",
                'sort_order' => 14,
            ],
            [
                'category_slug' => 'tips-penyimpanan',
                'title' => 'Cara Mengatur Porsi Masak Mingguan (Meal Prep) untuk Ibu Bekerja',
                'slug' => 'cara-mengatur-porsi-masak-mingguan-meal-prep-ibu-bekerja',
                'status' => 'published',
                'image' => 'images/know-thawing.jpg',
                'excerpt' => 'Strategi hemat waktu di dapur dengan mencuci, memotong, dan memarinasi bahan makanan untuk stok 7 hari ke depan.',
                'raw_content' => "Meal prep di akhir pekan menghemat hingga 45 menit waktu memasak harian dan memastikan keluarga tetap makan masakan sehat di rumah.",
                'sort_order' => 15,
            ],
            [
                'category_slug' => 'edukasi-dapur',
                'title' => 'Tips Memilih Minyak Goreng yang Sehat untuk Memasak Lauk Harian',
                'slug' => 'tips-memilih-minyak-goreng-yang-sehat-untuk-memasak-lauk-harian',
                'status' => 'draft',
                'image' => 'images/hero-3.jpg',
                'excerpt' => 'Memahami smoke point minyak kelapa, minyak jagung, dan canola oil untuk menggoreng renyah tanpa merusak kualitas makanan.',
                'raw_content' => "Draft artikel mengenai pemilihan minyak goreng yang stabil pada suhu tinggi untuk menggoreng ayam ungkep dan aneka seafood.",
                'sort_order' => 16,
            ],
            [
                'category_slug' => 'informasi-produk',
                'title' => 'Perbandingan Nutrisi Daging Ikan Tawar vs Ikan Laut',
                'slug' => 'perbandingan-nutrisi-daging-ikan-tawar-vs-ikan-laut',
                'status' => 'draft',
                'image' => 'images/cat-ikan.jpg',
                'excerpt' => 'Eksplorasi kandungan asam lemak esensial EPA, DHA, kalsium, dan fosfor pada ikan tawar lokal dibandingkan ikan laut dalam.',
                'raw_content' => "Draft perbandingan nutrisi ikan gurame dan nila dibandingkan dori dan salmon untuk variasi menu mingguan keluarga.",
                'sort_order' => 17,
            ],
            [
                'category_slug' => 'resep-masakan',
                'title' => 'Resep Sup Ikan Gurame Asam Pedas Segar Khas Restoran Sunda',
                'slug' => 'resep-sup-ikan-gurame-asam-pedas-segar-khas-restoran-sunda',
                'status' => 'draft',
                'image' => 'images/prod-ikan-gurame.jpg',
                'excerpt' => 'Kuah bening segar dengan rempah daun kemangi, tomat hijau, serai, dan cabai rawit utuh berpadu fillet gurame lembut.',
                'raw_content' => "Draft resep lengkap sup gurame kuah asam pedas bening anti amis menggunakan fillet gurame potong segar Sumber Protein Jogja.",
                'sort_order' => 18,
            ],
        ];

        foreach ($articles as $art) {
            $catSlug = $art['category_slug'];
            $catId = $categories[$catSlug]->id ?? null;
            if (!$catId) {
                continue;
            }

            // Convert raw text into Canonical Schema v1 JSON AST
            $contentAst = KnowledgeArticleParser::parse($art['raw_content']);

            KnowledgeArticle::updateOrCreate(
                ['slug' => $art['slug']],
                [
                    'category_id' => $catId,
                    'title' => $art['title'],
                    'slug' => $art['slug'],
                    'excerpt' => $art['excerpt'],
                    'content' => $contentAst,
                    'image' => $art['image'],
                    'status' => $art['status'],
                    'sort_order' => $art['sort_order'],
                ]
            );
        }
    }
}
