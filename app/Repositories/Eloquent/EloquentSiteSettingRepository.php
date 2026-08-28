<?php

namespace App\Repositories\Eloquent;

use App\Models\SiteSetting;
use App\Repositories\Contracts\SiteSettingRepositoryInterface;

class EloquentSiteSettingRepository implements SiteSettingRepositoryInterface
{
    /**
     * Get a single site setting payload by key.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return SiteSetting::get($key, $default);
    }

    /**
     * Get multiple site settings keyed by setting key.
     */
    public function getMany(array $keys): array
    {
        $settings = SiteSetting::whereIn('key', $keys)->get()->keyBy('key');
        $result = [];

        foreach ($keys as $k) {
            $result[$k] = isset($settings[$k]) ? $settings[$k]->value : null;
        }

        return $result;
    }

    /**
     * Set/update a site setting payload.
     */
    public function set(string $key, mixed $value): void
    {
        SiteSetting::set($key, $value);
    }

    /**
     * Get all site settings as associative array.
     */
    public function all(): array
    {
        $settings = SiteSetting::all();
        $result = [];

        foreach ($settings as $s) {
            $result[$s->key] = $s->value;
        }

        return $result;
    }
}
