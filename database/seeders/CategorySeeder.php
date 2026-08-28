<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Daging Sapi',
                'slug' => 'daging-sapi',
                'color' => 'orange',
                'image' => 'images/cat-daging.jpg',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Ayam Segar & Olahan',
                'slug' => 'ayam-segar',
                'color' => 'yellow',
                'image' => 'images/cat-ayam.jpg',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Ikan & Seafood',
                'slug' => 'ikan-seafood',
                'color' => 'blue',
                'image' => 'images/cat-ikan.jpg',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Sayuran Siap Olah',
                'slug' => 'sayuran-siap-olah',
                'color' => 'green',
                'image' => 'images/cat-sayur.jpg',
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'Frozen Food & Olahan',
                'slug' => 'frozen-food',
                'color' => 'purple',
                'image' => 'images/cat-frozen.jpg',
                'sort_order' => 5,
                'is_active' => true,
            ],
        ];

        foreach ($categories as $cat) {
            Category::updateOrCreate(
                ['slug' => $cat['slug']],
                $cat
            );
        }
    }
}
