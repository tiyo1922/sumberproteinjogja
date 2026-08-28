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
     * Get active categories with dynamic active product count.
     */
    public function getActiveWithProductCount(): Collection
    {
        $categories = Category::active()
            ->orderBy('sort_order', 'asc')
            ->withCount(['products' => function ($query) {
                $query->where('is_active', true);
            }])
            ->get();

        foreach ($categories as $category) {
            $count = $category->products_count ?? 0;
            $category->count = $count . '+ Variasi';
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
     * Toggle the active status of a category.
     */
    public function toggleActive(int $id): ?Category
    {
        $category = $this->findById($id);
        if (!$category) {
            return null;
        }

        $category->is_active = !$category->is_active;
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
        foreach ($orderMap as $id => $order) {
            Category::where('id', $id)->update(['sort_order' => (int) $order]);
        }

        return true;
    }
}
