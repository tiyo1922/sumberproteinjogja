<?php

namespace App\Repositories\Eloquent;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class EloquentProductRepository implements ProductRepositoryInterface
{
    /**
     * Get all products with category relation ordered by sort_order.
     */
    public function getAll(): Collection
    {
        return Product::with('category')->orderBy('sort_order', 'asc')->get();
    }

    /**
     * Get active product catalog with category relation ordered by sort_order.
     */
    public function getActiveCatalog(): Collection
    {
        return Product::active()
            ->with('category')
            ->orderBy('sort_order', 'asc')
            ->get();
    }

    /**
     * Get active Flash Sale products ordered by flash_sale_sort_order and id.
     */
    public function getFlashSaleProducts(): Collection
    {
        return Product::active()
            ->flashSale()
            ->with('category')
            ->get();
    }

    /**
     * Find product by primary ID with category relation.
     */
    public function findById(int $id): ?Product
    {
        return Product::with('category')->find($id);
    }

    /**
     * Find product by unique slug with category relation.
     */
    public function findBySlug(string $slug): ?Product
    {
        return Product::with('category')->where('slug', $slug)->first();
    }

    /**
     * Get active products by category ID.
     */
    public function getByCategory(int $categoryId): Collection
    {
        return Product::active()
            ->byCategory($categoryId)
            ->with('category')
            ->orderBy('sort_order', 'asc')
            ->get();
    }

    /**
     * Create a new product.
     */
    public function create(array $data): Product
    {
        return Product::create($data);
    }

    /**
     * Update an existing product.
     */
    public function update(int $id, array $data): bool
    {
        $product = Product::find($id);
        if (!$product) {
            return false;
        }

        return (bool) $product->update($data);
    }

    /**
     * Delete a product.
     */
    public function delete(int $id): bool
    {
        $product = Product::find($id);
        if (!$product) {
            return false;
        }

        return (bool) $product->delete();
    }

    /**
     * Toggle the active status of a product.
     */
    public function toggleActive(int $id): ?Product
    {
        $product = Product::find($id);
        if (!$product) {
            return null;
        }

        $product->is_active = !$product->is_active;
        $product->save();

        return $product;
    }

    /**
     * Update sort order for multiple products.
     *
     * @param array<int, int> $orderMap Array of [productId => sortOrder]
     */
    public function reorder(array $orderMap): bool
    {
        foreach ($orderMap as $id => $order) {
            Product::where('id', $id)->update(['sort_order' => (int) $order]);
        }

        return true;
    }

    /**
     * Assign product to Flash Sale with discount and sort configuration.
     */
    public function assignFlashSale(int $id, array $flashSaleData): bool
    {
        $product = Product::find($id);
        if (!$product) {
            return false;
        }

        $updatePayload = [
            'is_flash_sale' => true,
            'flash_sale_discount_type' => $flashSaleData['discount_type'] ?? ($flashSaleData['flash_sale_discount_type'] ?? null),
            'flash_sale_discount_value' => isset($flashSaleData['discount_value']) ? (float) $flashSaleData['discount_value'] : (isset($flashSaleData['flash_sale_discount_value']) ? (float) $flashSaleData['flash_sale_discount_value'] : null),
            'flash_sale_sort_order' => isset($flashSaleData['sort_order']) ? (int) $flashSaleData['sort_order'] : (isset($flashSaleData['flash_sale_sort_order']) ? (int) $flashSaleData['flash_sale_sort_order'] : (Product::where('is_flash_sale', true)->count() + 1)),
        ];

        return (bool) $product->update($updatePayload);
    }

    /**
     * Remove product from Flash Sale without altering regular product fields.
     */
    public function removeFlashSale(int $id): bool
    {
        $product = Product::find($id);
        if (!$product) {
            return false;
        }

        return (bool) $product->update([
            'is_flash_sale' => false,
        ]);
    }

    /**
     * Reorder Flash Sale products.
     *
     * @param array<int, int> $orderMap Array of [productId => flashSaleSortOrder]
     */
    public function reorderFlashSale(array $orderMap): bool
    {
        foreach ($orderMap as $id => $order) {
            Product::where('id', $id)->update(['flash_sale_sort_order' => (int) $order]);
        }

        return true;
    }
}
