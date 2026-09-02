<?php

namespace App\Repositories\Eloquent;

use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class EloquentCategoryRepository implements CategoryRepositoryInterface
{
    /**
     * Get all categories ordered by sort_order.
     */
    public function getAll(): Collection
    {
        return Category::orderBy('sort_order', 'asc')->get();
    }

    /**
     * Get only active categories ordered by sort_order.
     */
    public function getActive(): Collection
    {
        return Category::active()->orderBy('sort_order', 'asc')->get();
    }

    /**
     * Get active landing categories (is_active = 1) with dynamic active product count.
     */
    public function getActiveLandingWithProductCount(): Collection
    {
        $categories = Category::activeLanding()
            ->orderBy('sort_order', 'asc')
            ->withCount(['products' => function ($query) {
                $query->where('is_active', true);
            }])
            ->get();

        return $this->enrichMetadata($categories);
    }

    /**
     * Get active catalog categories (is_active = 1 or 2) with dynamic active product count.
     */
    public function getActiveCatalogWithProductCount(): Collection
    {
        $categories = Category::activeCatalog()
            ->orderBy('sort_order', 'asc')
            ->withCount(['products' => function ($query) {
                $query->where('is_active', true);
            }])
            ->get();

        return $this->enrichMetadata($categories);
    }

    /**
     * Get active categories with dynamic active product count (default landing page).
     */
    public function getActiveWithProductCount(): Collection
    {
        return $this->getActiveLandingWithProductCount();
    }

    /**
     * Enrich category collection with UI metadata.
     */
    protected function enrichMetadata(Collection $categories): Collection
    {
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

        foreach ($categories as $category) {
            $count = $category->products_count ?? 0;
            $category->count = $count . '+ Variasi';
            $rawActive = (int) $category->is_active;
            $category->status = ($rawActive === 1) ? 'active_landing' : (($rawActive === 2) ? 'active_catalog' : 'inactive');

            $meta = $defaultMetadata[$category->slug] ?? [];
            if (empty($category->subtitle)) {
                $category->subtitle = $meta['subtitle'] ?? 'Pilihan Bahan Segar & Berkualitas';
            }
            if (empty($category->description)) {
                $category->description = $meta['description'] ?? 'Bahan masak segar dan higienis pilihan keluarga.';
            }
            if (empty($category->badge)) {
                $category->badge = $meta['badge'] ?? 'Sertifikasi Halal';
            }
        }

        return $categories;
    }

    /**
     * Find category by primary ID.
     */
    public function findById(int $id): ?Category
    {
        return Category::find($id);
    }

    /**
     * Find category by slug.
     */
    public function findBySlug(string $slug): ?Category
    {
        return Category::where('slug', $slug)->first();
    }

    /**
     * Create a new category.
     */
    public function create(array $data): Category
    {
        return Category::create($data);
    }

    /**
     * Update an existing category.
     */
    public function update(int $id, array $data): bool
    {
        $category = $this->findById($id);
        if (!$category) {
            return false;
        }

        return (bool) $category->update($data);
    }

    /**
     * Delete a category.
     */
    public function delete(int $id): bool
    {
        $category = $this->findById($id);
        if (!$category) {
            return false;
        }

        return (bool) $category->delete();
    }

    /**
     * Count products associated with a category.
     */
    public function countProducts(int $id): int
    {
        $category = $this->findById($id);
        if (!$category) {
            return 0;
        }

        return $category->products()->count();
    }

    /**
     * Check if a category has any associated products.
     */
    public function hasProducts(int $id): bool
    {
        return $this->countProducts($id) > 0;
    }

    /**
     * Toggle the active status of a category cycling through (inactive -> active_landing -> active_catalog -> inactive) or to targetStatus.
     */
    public function toggleActive(int $id, ?string $targetStatus = null): ?Category
    {
        $category = $this->findById($id);
        if (!$category) {
            return null;
        }

        if ($targetStatus !== null) {
            if ($targetStatus === 'active_landing' || $targetStatus === '1') {
                $category->is_active = 1;
            } elseif ($targetStatus === 'active_catalog' || $targetStatus === '2') {
                $category->is_active = 2;
            } else {
                $category->is_active = 0;
            }
        } else {
            $curr = (int) $category->is_active;
            $category->is_active = ($curr === 1) ? 2 : (($curr === 2) ? 0 : 1);
        }

        $category->save();

        return $category;
    }

    /**
     * Update sort order for multiple categories.
     *
     * @param array<int, int> $orderMap Array of [categoryId => sortOrder]
     */
    public function reorder(array $orderMap): bool
    {
        foreach ($orderMap as $key => $value) {
            if (is_array($value)) {
                $id = $value['id'] ?? $key;
                $sort = $value['sort_order'] ?? ($value['order'] ?? $key);
            } else {
                $id = $key;
                $sort = $value;
            }
            Category::where('id', $id)->update(['sort_order' => (int) $sort]);
        }

        return true;
    }
}
