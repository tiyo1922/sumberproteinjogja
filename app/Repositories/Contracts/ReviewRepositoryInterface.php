<?php

namespace App\Repositories\Contracts;

use App\Models\Review;
use Illuminate\Database\Eloquent\Collection;

interface ReviewRepositoryInterface
{
    /**
     * Get active customer reviews ordered by sort_order and reviewed_at.
     */
    public function getActiveReviews(?int $limit = null): Collection;

    /**
     * Get active reviews filtered by source ('manual' or 'google').
     */
    public function getBySource(string $source, bool $activeOnly = true): Collection;

    /**
     * Get all reviews ordered by sort_order.
     */
    public function getAll(): Collection;

    /**
     * Find review by primary ID.
     */
    public function findById(int $id): ?Review;

    /**
     * Create a new review.
     */
    public function create(array $data): Review;

    /**
     * Update an existing review.
     */
    public function update(int $id, array $data): bool;

    /**
     * Delete a review.
     */
    public function delete(int $id): bool;

    /**
     * Toggle the active status of a review.
     */
    public function toggleActive(int $id): ?Review;

    /**
     * Update sort order for multiple reviews.
     *
     * @param array<int, int> $orderMap Array of [reviewId => sortOrder]
     */
    public function reorder(array $orderMap): bool;
}
