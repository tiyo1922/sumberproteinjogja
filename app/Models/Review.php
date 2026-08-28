<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'reviews';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'reviewer_name',
        'reviewer_title',
        'reviewer_location',
        'review_text',
        'rating',
        'reviewed_at',
        'avatar',
        'source',
        'google_review_id',
        'is_active',
        'sort_order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'rating' => 'integer',
        'reviewed_at' => 'datetime',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get reviewer initials for avatar placeholder (e.g., "Ratna Dewi" -> "RD").
     */
    public function getInitialsAttribute(): string
    {
        $words = preg_split('/\s+/', trim($this->reviewer_name ?? ''));
        if (empty($words) || empty($words[0])) {
            return 'U';
        }
        if (count($words) === 1) {
            return strtoupper(substr($words[0], 0, 2));
        }
        return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
    }

    /**
     * Scope a query to only include active reviews.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to filter reviews by source (manual or google).
     */
    public function scopeBySource($query, string $source)
    {
        return $query->where('source', $source);
    }
}
