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
     * Get active categories with eager active product count.
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
     * Toggle the active status of a category.
     */
    public function toggleActive(int $id): ?Category;

    /**
     * Update sort order for multiple categories.
     *
     * @param array<int, int> $orderMap Array of [categoryId => sortOrder]
     */
    public function reorder(array $orderMap): bool;
}
