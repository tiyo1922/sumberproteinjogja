<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
     * The accessors to append to the model's array and JSON representation.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'category_ids',
        'category_names',
        'category_display',
        'effective_price',
        'flash_sale_effective_price',
        'type_badges',
    ];

    /**
     * The categories that belong to the product (Many-to-Many).
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_product')->withTimestamps();
    }

    /**
     * Get the primary category that owns the product (Backward compatibility).
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * Get array of category IDs attached to the product (Many-to-Many source of truth).
     *
     * @return array<int>
     */
    public function getCategoryIdsAttribute(): array
    {
        if ($this->relationLoaded('categories')) {
            $ids = $this->categories->pluck('id')->map(fn($id) => (int) $id)->toArray();
            if (!empty($ids)) {
                return array_values($ids);
            }
        } elseif ($this->exists) {
            $ids = $this->categories()->pluck('categories.id')->map(fn($id) => (int) $id)->toArray();
            if (!empty($ids)) {
                return array_values($ids);
            }
        }
        return $this->category_id ? [(int) $this->category_id] : [];
    }

    /**
     * Get array of category names attached to the product.
     *
     * @return array<string>
     */
    public function getCategoryNamesAttribute(): array
    {
        if ($this->relationLoaded('categories')) {
            $names = $this->categories->pluck('name')->toArray();
            if (!empty($names)) {
                return array_values($names);
            }
        } elseif ($this->exists) {
            $names = $this->categories()->pluck('categories.name')->toArray();
            if (!empty($names)) {
                return array_values($names);
            }
        }
        return $this->category ? [$this->category->name] : [];
    }

    /**
     * Get combined category display name.
     */
    public function getCategoryDisplayAttribute(): string
    {
        $names = $this->category_names;
        return !empty($names) ? implode(', ', $names) : ($this->category->name ?? 'Daging Sapi');
    }

    /**
     * Get formatted type badges array with CSS classes.
     */
    public function getTypeBadgesAttribute(): array
    {
        $types = is_array($this->types) ? $this->types : [];
        $badges = [];
        $map = [
            'Frozen' => 'badge-frozen',
            'Fresh' => 'badge-fresh',
            'Ready to Cook' => 'badge-ready',
            'Berbumbu' => 'badge-accent',
            'Curah' => 'badge-bulk',
            'Plain' => 'badge-primary',
        ];

        foreach ($types as $t) {
            $badges[] = [
                'text' => $t,
                'class' => $map[$t] ?? 'badge-primary',
            ];
        }

        return $badges;
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
