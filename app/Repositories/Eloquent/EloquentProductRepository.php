<?php

namespace App\Repositories\Eloquent;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class EloquentProductRepository implements ProductRepositoryInterface
{
    /**
     * Get all products with categories relation ordered by sort_order.
     */
    public function getAll(): Collection
    {
        return Product::with(['categories', 'category'])->orderBy('sort_order', 'asc')->get();
    }

    /**
     * Get active product catalog with categories relation ordered by sort_order.
     */
    public function getActiveCatalog(): Collection
    {
        return Product::active()
            ->with(['categories', 'category'])
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
            ->with(['categories', 'category'])
            ->get();
    }

    /**
     * Find product by primary ID with categories relation.
     */
    public function findById(int $id): ?Product
    {
        return Product::with(['categories', 'category'])->find($id);
    }

    /**
     * Find product by unique slug with categories relation.
     */
    public function findBySlug(string $slug): ?Product
    {
        return Product::with(['categories', 'category'])->where('slug', $slug)->first();
    }

    /**
     * Get active products by category ID via many-to-many pivot or legacy category_id.
     */
    public function getByCategory(int $categoryId): Collection
    {
        return Product::active()
            ->where(function ($query) use ($categoryId) {
                $query->whereHas('categories', function ($q) use ($categoryId) {
                    $q->where('categories.id', $categoryId);
                })->orWhere('category_id', $categoryId);
            })
            ->with(['categories', 'category'])
            ->orderBy('sort_order', 'asc')
            ->get();
    }

    /**
     * Create a new product and sync categories.
     */
    public function create(array $data): Product
    {
        $categoryIds = null;
        if (isset($data['category_ids'])) {
            $categoryIds = (array) $data['category_ids'];
            unset($data['category_ids']);
            if (!empty($categoryIds) && empty($data['category_id'])) {
                $data['category_id'] = $categoryIds[0];
            }
        }

        $product = Product::create($data);

        if (!empty($categoryIds)) {
            $product->categories()->sync($categoryIds);
        } elseif (!empty($product->category_id)) {
            $product->categories()->syncWithoutDetaching([$product->category_id]);
        }

        return $product->load(['categories', 'category']);
    }

    /**
     * Update an existing product and sync categories.
     */
    public function update(int $id, array $data): bool
    {
        $product = Product::find($id);
        if (!$product) {
            return false;
        }

        $categoryIds = null;
        if (isset($data['category_ids'])) {
            $categoryIds = (array) $data['category_ids'];
            unset($data['category_ids']);
            if (!empty($categoryIds)) {
                $data['category_id'] = $categoryIds[0];
                $product->categories()->sync($categoryIds);
            }
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

        // Pivot records are automatically deleted by ON DELETE CASCADE, but detach cleanly
        $product->categories()->detach();

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
            Product::where('id', $id)
                ->where('is_flash_sale', true)
                ->update(['flash_sale_sort_order' => (int) $order]);
        }

        return true;
    }
}
