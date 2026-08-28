<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'products';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'image',
        'types',
        'weight',
        'weight_value',
        'unit',
        'normal_price',
        'discount_type',
        'discount_value',
        'stock_status',
        'is_flash_sale',
        'flash_sale_discount_type',
        'flash_sale_discount_value',
        'flash_sale_sort_order',
        'is_active',
        'sort_order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'types' => 'array',
        'weight_value' => 'integer',
        'normal_price' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'flash_sale_discount_value' => 'decimal:2',
        'is_flash_sale' => 'boolean',
        'flash_sale_sort_order' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get the category that owns the product.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * Get regular effective price calculated from normal_price and regular discount.
     */
    public function getEffectivePriceAttribute(): float
    {
        $normal = (float) ($this->normal_price ?? 0);
        if ($normal <= 0) {
            return 0.0;
        }

        $type = $this->discount_type;
        $val = (float) ($this->discount_value ?? 0);

        if (!$type || $val <= 0) {
            return $normal;
        }

        if ($type === 'percentage') {
            $percentage = min(100.0, max(0.0, $val));
            $discountAmount = round($normal * ($percentage / 100.0), 2);
            return max(0.0, round($normal - $discountAmount, 2));
        }

        if ($type === 'fixed') {
            $discountAmount = max(0.0, $val);
            return max(0.0, round($normal - $discountAmount, 2));
        }

        return $normal;
    }

    /**
     * Get Flash Sale effective price calculated strictly from normal_price.
     * Flash Sale calculation never cascades or modifies regular effective price.
     */
    public function getFlashSaleEffectivePriceAttribute(): float
    {
        $normal = (float) ($this->normal_price ?? 0);
        if ($normal <= 0) {
            return 0.0;
        }

        if (!$this->is_flash_sale) {
            return $normal;
        }

        $type = $this->flash_sale_discount_type;
        $val = (float) ($this->flash_sale_discount_value ?? 0);

        if (!$type || $val <= 0) {
            return $normal;
        }

        if ($type === 'percentage') {
            $percentage = min(100.0, max(0.0, $val));
            $discountAmount = round($normal * ($percentage / 100.0), 2);
            return max(0.0, round($normal - $discountAmount, 2));
        }

        if ($type === 'fixed') {
            $discountAmount = max(0.0, $val);
            return max(0.0, round($normal - $discountAmount, 2));
        }

        return $normal;
    }

    /**
     * Check if product is Ready Stock.
     */
    public function isReadyStock(): bool
    {
        return $this->stock_status === 'READY_STOCK';
    }

    /**
     * Check if product is Out of Stock.
     */
    public function isOutOfStock(): bool
    {
        return $this->stock_status === 'OUT_OF_STOCK';
    }

    /**
     * Check if product is Pre-Order.
     */
    public function isPreOrder(): bool
    {
        return $this->stock_status === 'PRE_ORDER';
    }

    /**
     * Scope a query to only include active products.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to filter products by category.
     */
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope a query to retrieve eligible Flash Sale products ordered by flash_sale_sort_order.
     */
    public function scopeFlashSale($query)
    {
        return $query->where('is_active', true)
                     ->where('is_flash_sale', true)
                     ->orderBy('flash_sale_sort_order', 'asc')
                     ->orderBy('id', 'asc');
    }
}
