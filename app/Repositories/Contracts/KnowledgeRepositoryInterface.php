<?php

namespace App\Repositories\Contracts;

use App\Models\KnowledgeArticle;
use App\Models\KnowledgeCategory;
use Illuminate\Database\Eloquent\Collection;

interface KnowledgeRepositoryInterface
{
    /**
     * Get all active knowledge categories ordered by sort_order.
     */
    public function getCategories(): Collection;

    /**
     * Get all knowledge categories (active and inactive).
     */
    public function getAllCategories(): Collection;

    /**
     * Find knowledge category by primary key ID.
     */
    public function findCategoryById(int $id): ?KnowledgeCategory;

    /**
     * Find knowledge category by unique slug.
     */
    public function findCategoryBySlug(string $slug): ?KnowledgeCategory;

    /**
     * Create a new knowledge category.
     */
    public function createCategory(array $data): KnowledgeCategory;

    /**
     * Update an existing knowledge category.
     */
    public function updateCategory(int $id, array $data): bool;

    /**
     * Delete a knowledge category.
     */
    public function deleteCategory(int $id): bool;

    /**
     * Toggle active state of a knowledge category.
     */
    public function toggleCategoryActive(int $id): ?KnowledgeCategory;

    /**
     * Reorder knowledge categories.
     */
    public function reorderCategories(array $orderMap): bool;

    /**
     * Count articles in a knowledge category.
     */
    public function countCategoryArticles(int $categoryId): int;

    /**
     * Get published knowledge articles with category relation.
     */
    public function getPublishedArticles(?int $limit = null): Collection;

    /**
     * Get all knowledge articles with category relation.
     */
    public function getAllArticles(): Collection;

    /**
     * Find knowledge article by primary key ID.
     */
    public function findArticleById(int $id): ?KnowledgeArticle;

    /**
     * Find knowledge article by unique slug.
     */
    public function findArticleBySlug(string $slug): ?KnowledgeArticle;

    /**
     * Create a new knowledge article.
     */
    public function createArticle(array $data): KnowledgeArticle;

    /**
     * Update an existing knowledge article.
     */
    public function updateArticle(int $id, array $data): bool;

    /**
     * Delete a knowledge article.
     */
    public function deleteArticle(int $id): bool;

    /**
     * Reorder knowledge articles.
     */
    public function reorderArticles(array $orderMap): bool;
}
