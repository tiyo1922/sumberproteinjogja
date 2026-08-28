<?php

namespace App\Repositories\Eloquent;

use App\Models\KnowledgeArticle;
use App\Models\KnowledgeCategory;
use App\Repositories\Contracts\KnowledgeRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class EloquentKnowledgeRepository implements KnowledgeRepositoryInterface
{
    /**
     * Get all active knowledge categories ordered by sort_order ASC, then id ASC.
     */
    public function getCategories(): Collection
    {
        return KnowledgeCategory::active()
            ->withCount('articles')
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->get();
    }

    /**
     * Get all knowledge categories (active and inactive) ordered by sort_order ASC, then id ASC.
     */
    public function getAllCategories(): Collection
    {
        return KnowledgeCategory::withCount('articles')
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->get();
    }

    /**
     * Find knowledge category by primary key ID.
     */
    public function findCategoryById(int $id): ?KnowledgeCategory
    {
        return KnowledgeCategory::withCount('articles')->find($id);
    }

    /**
     * Find knowledge category by unique slug.
     */
    public function findCategoryBySlug(string $slug): ?KnowledgeCategory
    {
        return KnowledgeCategory::withCount('articles')->where('slug', $slug)->first();
    }

    /**
     * Create a new knowledge category.
     */
    public function createCategory(array $data): KnowledgeCategory
    {
        return KnowledgeCategory::create($data);
    }

    /**
     * Update an existing knowledge category.
     */
    public function updateCategory(int $id, array $data): bool
    {
        $category = KnowledgeCategory::find($id);
        if (!$category) {
            return false;
        }

        return (bool) $category->update($data);
    }

    /**
     * Delete a knowledge category.
     */
    public function deleteCategory(int $id): bool
    {
        $category = KnowledgeCategory::find($id);
        if (!$category) {
            return false;
        }

        return (bool) $category->delete();
    }

    /**
     * Toggle active state of a knowledge category.
     */
    public function toggleCategoryActive(int $id): ?KnowledgeCategory
    {
        $category = KnowledgeCategory::find($id);
        if (!$category) {
            return null;
        }

        $category->is_active = !$category->is_active;
        $category->save();

        return $category;
    }

    /**
     * Reorder knowledge categories.
     */
    public function reorderCategories(array $orderMap): bool
    {
        foreach ($orderMap as $item) {
            if (isset($item['id'], $item['sort_order'])) {
                KnowledgeCategory::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
            }
        }

        return true;
    }

    /**
     * Count articles in a knowledge category.
     */
    public function countCategoryArticles(int $categoryId): int
    {
        return KnowledgeArticle::where('category_id', $categoryId)->count();
    }

    /**
     * Get published knowledge articles with category relation ordered by sort_order ASC, then id ASC.
     */
    public function getPublishedArticles(?int $limit = null): Collection
    {
        $query = KnowledgeArticle::published()
            ->with('category')
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc');

        if ($limit !== null && $limit > 0) {
            $query->take($limit);
        }

        return $query->get();
    }

    /**
     * Get all knowledge articles with category relation ordered by sort_order ASC, then id ASC.
     */
    public function getAllArticles(): Collection
    {
        return KnowledgeArticle::with('category')
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->get();
    }

    /**
     * Find knowledge article by primary key ID.
     */
    public function findArticleById(int $id): ?KnowledgeArticle
    {
        return KnowledgeArticle::with('category')->find($id);
    }

    /**
     * Find knowledge article by unique slug.
     */
    public function findArticleBySlug(string $slug): ?KnowledgeArticle
    {
        return KnowledgeArticle::with('category')->where('slug', $slug)->first();
    }

    /**
     * Create a new knowledge article.
     */
    public function createArticle(array $data): KnowledgeArticle
    {
        return KnowledgeArticle::create($data);
    }

    /**
     * Update an existing knowledge article.
     */
    public function updateArticle(int $id, array $data): bool
    {
        $article = KnowledgeArticle::find($id);
        if (!$article) {
            return false;
        }

        return (bool) $article->update($data);
    }

    /**
     * Delete a knowledge article.
     */
    public function deleteArticle(int $id): bool
    {
        $article = KnowledgeArticle::find($id);
        if (!$article) {
            return false;
        }

        return (bool) $article->delete();
    }

    /**
     * Reorder knowledge articles.
     */
    public function reorderArticles(array $orderMap): bool
    {
        foreach ($orderMap as $item) {
            if (isset($item['id'], $item['sort_order'])) {
                KnowledgeArticle::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
            }
        }

        return true;
    }
}
