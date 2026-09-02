<?php

namespace App\Repositories\Contracts;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

interface CategoryRepositoryInterface
{
    /**
     * Get all categories ordered by sort_order.
     */
    public function getAll(): Collection;

    /**
     * Get only active categories ordered by sort_order.
     */
    public function getActive(): Collection;

    /**
     * Get active landing categories (is_active = 1) with eager active product count.
     */
    public function getActiveLandingWithProductCount(): Collection;

    /**
     * Get active catalog categories (is_active = 1 or 2) with eager active product count.
     */
    public function getActiveCatalogWithProductCount(): Collection;

    /**
     * Get active categories with eager active product count (default).
     */
    public function getActiveWithProductCount(): Collection;

    /**
     * Find category by primary ID.
     */
    public function findById(int $id): ?Category;

    /**
     * Find category by slug.
     */
    public function findBySlug(string $slug): ?Category;

    /**
     * Create a new category.
     */
    public function create(array $data): Category;

    /**
     * Update an existing category.
     */
    public function update(int $id, array $data): bool;

    /**
     * Delete a category.
     */
    public function delete(int $id): bool;

    /**
     * Count products associated with a category.
     */
    public function countProducts(int $id): int;

    /**
     * Check if a category has any associated products.
     */
    public function hasProducts(int $id): bool;

    /**
     * Toggle the active status of a category cycling through (inactive -> active_landing -> active_catalog -> inactive) or to targetStatus.
     */
    public function toggleActive(int $id, ?string $targetStatus = null): ?Category;

    /**
     * Update sort order for multiple categories.
     *
     * @param array<int, int> $orderMap Array of [categoryId => sortOrder]
     */
    public function reorder(array $orderMap): bool;
}
