<?php

namespace App\Repositories\Eloquent;

use App\Models\Review;
use App\Repositories\Contracts\ReviewRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class EloquentReviewRepository implements ReviewRepositoryInterface
{
    /**
     * Get active customer reviews ordered by sort_order and reviewed_at.
     */
    public function getActiveReviews(?int $limit = null): Collection
    {
        $query = Review::active()
            ->orderBy('sort_order', 'asc')
            ->orderBy('reviewed_at', 'desc');

        if ($limit !== null && $limit > 0) {
            $query->take($limit);
        }

        return $query->get();
    }

    /**
     * Get active reviews filtered by source ('manual' or 'google').
     */
    public function getBySource(string $source, bool $activeOnly = true): Collection
    {
        $query = Review::bySource($source)
            ->orderBy('sort_order', 'asc')
            ->orderBy('reviewed_at', 'desc');

        if ($activeOnly) {
            $query->active();
        }

        return $query->get();
    }

    /**
     * Get all reviews ordered by sort_order.
     */
    public function getAll(): Collection
    {
        return Review::orderBy('sort_order', 'asc')->get();
    }

    /**
     * Find review by primary ID.
     */
    public function findById(int $id): ?Review
    {
        return Review::find($id);
    }

    /**
     * Create a new review.
     */
    public function create(array $data): Review
    {
        return Review::create($data);
    }

    /**
     * Update an existing review.
     */
    public function update(int $id, array $data): bool
    {
        $review = Review::find($id);
        if (!$review) {
            return false;
        }

        return (bool) $review->update($data);
    }

    /**
     * Delete a review.
     */
    public function delete(int $id): bool
    {
        $review = Review::find($id);
        if (!$review) {
            return false;
        }

        return (bool) $review->delete();
    }

    /**
     * Toggle the active status of a review.
     */
    public function toggleActive(int $id): ?Review
    {
        $review = Review::find($id);
        if (!$review) {
            return null;
        }

        $review->is_active = !$review->is_active;
        $review->save();

        return $review;
    }

    /**
     * Update sort order for multiple reviews.
     *
     * @param array<int, int> $orderMap Array of [reviewId => sortOrder]
     */
    public function reorder(array $orderMap): bool
    {
        foreach ($orderMap as $id => $order) {
            Review::where('id', $id)->update(['sort_order' => (int) $order]);
        }

        return true;
    }
}
