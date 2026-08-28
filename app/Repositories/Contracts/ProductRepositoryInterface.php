<?php

namespace App\Repositories\Contracts;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

interface ProductRepositoryInterface
{
    /**
     * Get all products with category relation.
     */
    public function getAll(): Collection;

    /**
     * Get active product catalog with category relation ordered by sort_order.
     */
    public function getActiveCatalog(): Collection;

    /**
     * Get active Flash Sale products ordered by flash_sale_sort_order and id.
     */
    public function getFlashSaleProducts(): Collection;

    /**
     * Find product by primary ID with category relation.
     */
    public function findById(int $id): ?Product;

    /**
     * Find product by unique slug with category relation.
     */
    public function findBySlug(string $slug): ?Product;

    /**
     * Get active products by category ID.
     */
    public function getByCategory(int $categoryId): Collection;

    /**
     * Create a new product.
     */
    public function create(array $data): Product;

    /**
     * Update an existing product.
     */
    public function update(int $id, array $data): bool;

    /**
     * Delete a product.
     */
    public function delete(int $id): bool;

    /**
     * Toggle the active status of a product.
     */
    public function toggleActive(int $id): ?Product;

    /**
     * Update sort order for multiple products.
     *
     * @param array<int, int> $orderMap Array of [productId => sortOrder]
     */
    public function reorder(array $orderMap): bool;

    /**
     * Assign product to Flash Sale with discount and sort configuration.
     */
    public function assignFlashSale(int $id, array $flashSaleData): bool;

    /**
     * Remove product from Flash Sale without altering regular product fields.
     */
    public function removeFlashSale(int $id): bool;

    /**
     * Reorder Flash Sale products.
     *
     * @param array<int, int> $orderMap Array of [productId => flashSaleSortOrder]
     */
    public function reorderFlashSale(array $orderMap): bool;
}
