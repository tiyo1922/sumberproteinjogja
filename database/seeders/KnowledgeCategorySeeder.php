<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\KnowledgeCategory;

class KnowledgeCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Tips Penyimpanan',
                'slug' => 'tips-penyimpanan',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Edukasi Dapur',
                'slug' => 'edukasi-dapur',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Informasi Produk',
                'slug' => 'informasi-produk',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Resep Masakan',
                'slug' => 'resep-masakan',
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'Tips Belanja',
                'slug' => 'tips-belanja',
                'sort_order' => 5,
                'is_active' => true,
            ],
            [
                'name' => 'Edukasi Protein',
                'slug' => 'edukasi-protein',
                'sort_order' => 6,
                'is_active' => true,
            ],
        ];

        foreach ($categories as $cat) {
            KnowledgeCategory::updateOrCreate(
                ['slug' => $cat['slug']],
                $cat
            );
        }
    }
}
