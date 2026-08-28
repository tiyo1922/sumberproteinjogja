<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Review;
use Carbon\Carbon;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $reviews = [
            [
                'reviewer_name' => 'Ratna Dewi Kusuma',
                'reviewer_title' => 'Ibu Rumah Tangga',
                'reviewer_location' => 'Sleman',
                'review_text' => 'Sebagai ibu pekerja, belanja di Sumber Protein Jogja sangat menghemat waktu. Ayam bumbu kuningnya tinggal goreng, daging slicenya fresh banget dan nggak banyak lemak. Anak-anak suka sekali!',
                'rating' => 5,
                'reviewed_at' => Carbon::parse('2026-08-24 10:00:00'),
                'avatar' => null,
                'source' => 'manual',
                'google_review_id' => null,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'reviewer_name' => 'Bambang Haryanto',
                'reviewer_title' => 'Owner Kedai',
                'reviewer_location' => 'Kotagede',
                'review_text' => 'Sudah 4 bulan suplai fillet dada ayam curah untuk resto saya dari sini. Kualitasnya sangat stabil, potongan rapi, dan pengiriman tepat waktu. Harga partai besarnya sangat kompetitif di Jogja.',
                'rating' => 5,
                'reviewed_at' => Carbon::parse('2026-08-20 14:30:00'),
                'avatar' => null,
                'source' => 'manual',
                'google_review_id' => null,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'reviewer_name' => 'dr. Nadia Paramita',
                'reviewer_title' => 'Dokter & Home Chef',
                'reviewer_location' => 'Bantul',
                'review_text' => 'Salmon steak dan fillet guramenya benar-benar fresh, tidak amis sama sekali. Senang sekali ada toko protein selengkap ini dengan standar packaging vacuum yang higienis.',
                'rating' => 5,
                'reviewed_at' => Carbon::parse('2026-08-13 09:15:00'),
                'avatar' => null,
                'source' => 'manual',
                'google_review_id' => null,
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'reviewer_name' => 'Rian Hidayat',
                'reviewer_title' => 'Pelanggan Rumah Tangga',
                'reviewer_location' => 'Sleman',
                'review_text' => 'Daging slice-nya segar banget dan potongannya rapi. Sangat cocok buat shabu-shabu di rumah bareng keluarga. Pengiriman sameday cepat dan tetap beku!',
                'rating' => 5,
                'reviewed_at' => Carbon::parse('2026-08-13 16:45:00'),
                'avatar' => null,
                'source' => 'manual',
                'google_review_id' => null,
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'reviewer_name' => 'Dini Anggraini',
                'reviewer_title' => 'Ibu Rumah Tangga',
                'reviewer_location' => 'Yogyakarta',
                'review_text' => 'Ayam ungkep bumbu kuningnya juara! Tinggal sreng goreng sebentar, bumbunya meresap sampai ke dalam. Praktis banget buat bekal sekolah anak.',
                'rating' => 5,
                'reviewed_at' => Carbon::parse('2026-07-27 11:20:00'),
                'avatar' => null,
                'source' => 'manual',
                'google_review_id' => null,
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'reviewer_name' => 'Hendro Wijaya',
                'reviewer_title' => 'Pengusaha Catering',
                'reviewer_location' => 'Jogja Kota',
                'review_text' => 'Pelayanan admin via WhatsApp sangat ramah dan responsif. Daging rendang potongan seragam, mempermudah kalkulasi porsi catering.',
                'rating' => 5,
                'reviewed_at' => Carbon::parse('2026-05-27 08:00:00'),
                'avatar' => null,
                'source' => 'manual',
                'google_review_id' => null,
                'is_active' => false,
                'sort_order' => 6,
            ],
        ];

        foreach ($reviews as $rev) {
            Review::updateOrCreate(
                [
                    'reviewer_name' => $rev['reviewer_name'],
                    'reviewer_title' => $rev['reviewer_title'],
                ],
                $rev
            );
        }
    }
}
