<?php

namespace App\Repositories\Contracts;

interface SiteSettingRepositoryInterface
{
    /**
     * Get a single site setting payload by key.
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Get multiple site settings keyed by setting key.
     */
    public function getMany(array $keys): array;

    /**
     * Set/update a site setting payload.
     */
    public function set(string $key, mixed $value): void;

    /**
     * Get all site settings as associative array.
     */
    public function all(): array;
}
