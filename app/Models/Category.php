<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'categories';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'color',
        'image',
        'sort_order',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'integer',
    ];

    /**
     * Get the string status representation (inactive | active_landing | active_catalog).
     */
    public function getStatusAttribute(): string
    {
        $raw = (int) ($this->attributes['is_active'] ?? 0);
        if ($raw === 1) return 'active_landing';
        if ($raw === 2) return 'active_catalog';
        return 'inactive';
    }

    /**
     * Set the status attribute translating to integer is_active.
     */
    public function setStatusAttribute(mixed $value): void
    {
        if ($value === 'active_landing' || $value === 1 || $value === '1') {
            $this->attributes['is_active'] = 1;
        } elseif ($value === 'active_catalog' || $value === 2 || $value === '2') {
            $this->attributes['is_active'] = 2;
        } else {
            $this->attributes['is_active'] = 0;
        }
    }

    /**
     * Get the products for the category (Many-to-Many).
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'category_product')->withTimestamps();
    }

    /**
     * Get direct products using legacy foreign key (Backward compatibility).
     */
    public function directProducts(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    /**
     * Scope a query to only include active landing categories (is_active = 1).
     */
    public function scopeActiveLanding($query)
    {
        return $query->where('is_active', 1);
    }

    /**
     * Scope a query to only include active catalog categories (is_active = 1 or 2).
     */
    public function scopeActiveCatalog($query)
    {
        return $query->whereIn('is_active', [1, 2]);
    }

    /**
     * Scope a query to only include active categories (default for catalog and products).
     */
    public function scopeActive($query)
    {
        return $query->whereIn('is_active', [1, 2]);
    }
}
